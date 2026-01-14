<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Destination;
use App\Services\BookingService;
use App\Services\MidtransService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    protected MidtransService $midtransService;
    protected BookingService $bookingService;
    protected NotificationService $notificationService;

    public function __construct(
        MidtransService $midtransService,
        BookingService $bookingService,
        NotificationService $notificationService
    ) {
        $this->midtransService = $midtransService;
        $this->bookingService = $bookingService;
        $this->notificationService = $notificationService;
    }

    /**
     * Confirm cash payment - booking will be paid at counter
     */
    public function confirmCash($orderNumber)
    {
        $booking = $this->getBookingByOrder($orderNumber);

        if ($booking->status !== Booking::STATUS_PENDING) {
            return redirect()->route('booking.success', $booking->order_number);
        }

        $this->bookingService->confirmCashPayment($booking);

        return redirect()->route('booking.success', $booking->order_number);
    }

    /**
     * Show booking details with auto-healing for payment status.
     */
    public function show($orderNumber)
    {
        $booking = $this->getBookingByOrder($orderNumber);
        $booking = $this->bookingService->autoHealBooking($booking, $this->midtransService);

        // Make sure relationships are loaded for Inertia
        $booking->load(['destination.images', 'items', 'payment', 'tickets']);

        return \Inertia\Inertia::render('User/Bookings/Show', compact('booking'));
    }

    /**
     * Show booking form for a destination.
     */
    public function create($slug)
    {
        $destination = Destination::where('slug', $slug)
            ->active()
            ->with([
                'images',
                'prices' => fn($q) => $q->where('is_active', true)
            ])
            ->firstOrFail();

        $coupons = \App\Models\Coupon::valid()
            ->get()
            ->filter(fn($coupon) => $this->isCouponApplicable($coupon, $destination))
            ->values();

        return \Inertia\Inertia::render('Booking/Form', compact('destination', 'coupons'));
    }

    /**
     * Check if coupon is applicable to destination and user
     */
    protected function isCouponApplicable($coupon, Destination $destination): bool
    {
        // Check destination
        if ($coupon->applicable_destinations && count($coupon->applicable_destinations) > 0) {
            if (!in_array($destination->id, $coupon->applicable_destinations)) {
                return false;
            }
        }
        // Check per user limit if logged in
        if (auth()->check() && $coupon->per_user_limit) {
            $userUsage = $coupon->usages()->where('user_id', auth()->id())->count();
            if ($userUsage >= $coupon->per_user_limit) {
                return false;
            }
        }
        return true;
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request, $slug)
    {
        $destination = Destination::where('slug', $slug)->active()->with('prices')->firstOrFail();

        $validated = $request->validate([
            'visit_date' => 'required|date|after_or_equal:today',
            'leader_name' => 'required|string|max:255',
            'leader_phone' => 'required|string|max:20',
            'leader_email' => 'required|email',
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:0',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        // Calculate booking items
        $calculation = $this->bookingService->calculateBookingItems(
            $validated['quantities'],
            $destination->prices
        );

        // Validate at least 1 visitor
        if ($calculation['total_visitors'] < 1) {
            return back()->withErrors(['quantities' => 'Minimal 1 pengunjung diperlukan.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $serviceFee = 5000;
            $grossTotal = $calculation['subtotal'] + $serviceFee;

            // Apply coupon
            $couponResult = $this->bookingService->applyCoupon(
                $validated['coupon_code'] ?? null,
                $grossTotal,
                $destination->id
            );

            $totalAmount = max(0, $grossTotal - $couponResult['discount']);

            // Create booking
            $booking = Booking::create(array_merge([
                'order_number' => Booking::generateOrderNumber(),
                'user_id' => auth()->id(),
                'destination_id' => $destination->id,
                'visit_date' => $validated['visit_date'],
                'leader_name' => $validated['leader_name'],
                'leader_phone' => $validated['leader_phone'],
                'leader_email' => $validated['leader_email'],
                'total_visitors' => $calculation['total_visitors'],
                'subtotal' => $calculation['subtotal'],
                'service_fee' => $serviceFee,
                'discount' => $couponResult['discount'],
                'discount_code' => $couponResult['discount_code'],
                'total_amount' => $totalAmount,
                'status' => Booking::STATUS_PENDING,
            ], $calculation['summary']));

            // Create booking items
            foreach ($calculation['items'] as $item) {
                $booking->items()->create($item);
            }

            // If total is 0 (free due to coupon), auto-confirm payment
            if ($totalAmount <= 0) {
                $booking->update([
                    'status' => Booking::STATUS_CONFIRMED,
                    'confirmed_at' => now(),
                ]);

                // Create free payment record
                $booking->payments()->create([
                    'order_id' => $booking->order_number,
                    'payment_type' => 'free',
                    'payment_channel' => 'coupon',
                    'gross_amount' => 0,
                    'status' => 'success',
                    'paid_at' => now(),
                ]);

                // Mark coupon as used
                if ($couponResult['discount_code']) {
                    $coupon = \App\Models\Coupon::findByCode($couponResult['discount_code']);
                    if ($coupon && auth()->id()) {
                        $coupon->markAsUsed(auth()->id(), $booking->id);
                    }
                }

                // Generate tickets
                app(\App\Services\TicketService::class)->generateTicketsForBooking($booking);

                DB::commit();

                $this->notificationService->notifyBookingCreated($booking);
                $this->notificationService->notifyPaymentSuccess($booking);

                return redirect()->route('booking.success', $booking->order_number);
            }

            DB::commit();

            $this->notificationService->notifyBookingCreated($booking);

            return redirect()->route('booking.payment', $booking->order_number);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show payment page.
     */
    public function payment($orderNumber)
    {
        $booking = $this->getBookingByOrder($orderNumber);

        // Auto-heal: check Midtrans status first before creating new snap token
        // This prevents "order_id sudah digunakan" error when payment was already completed
        $booking = $this->bookingService->autoHealBooking($booking, $this->midtransService);

        if ($booking->status !== Booking::STATUS_PENDING) {
            return redirect()->route('booking.success', $booking->order_number);
        }

        $snapToken = null;
        $errorMessage = null;

        try {
            $result = $this->midtransService->createSnapToken($booking);
            $snapToken = $result['success'] ? $result['snap_token'] : null;
            $errorMessage = $result['success'] ? null : ($result['message'] ?? 'Gagal membuat pembayaran');
        } catch (\Exception $e) {
            $errorMessage = 'Layanan pembayaran tidak tersedia: ' . $e->getMessage();
        }

        return \Inertia\Inertia::render('Booking/Payment', compact('booking', 'snapToken', 'errorMessage'));
    }

    /**
     * Show booking success page.
     */
    public function success($orderNumber)
    {
        $booking = $this->getBookingByOrder($orderNumber);

        if ($booking->status === Booking::STATUS_PENDING) {
            return redirect()->route('booking.payment', $booking->order_number);
        }

        return \Inertia\Inertia::render('Booking/Success', compact('booking'));
    }

    /**
     * Get booking by order number with authorization check.
     */
    protected function getBookingByOrder($orderNumber)
    {
        $query = Booking::where('order_number', $orderNumber)
            ->with(['destination', 'items', 'payment', 'tickets']);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        }

        return $query->firstOrFail();
    }

    /**
     * Download E-Ticket PDF.
     */
    public function downloadTicket($orderNumber)
    {
        try {
            $booking = $this->getBookingByOrder($orderNumber);

            if (!in_array($booking->status, [Booking::STATUS_CONFIRMED, Booking::STATUS_CONFIRMED, 'used'])) {
                return back()->with('error', 'E-Ticket hanya tersedia untuk pesanan yang sudah lunas.');
            }

            $booking->load(['destination', 'tickets', 'items']);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket', compact('booking'));

            return $pdf->download('E-Ticket-TNLL-' . $booking->order_number . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Download Error: ' . $e->getMessage() . ' | ' . $e->getTraceAsString());
            return back()->with('error', 'Gagal membuat PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download Invoice PDF (80mm POS format).
     */
    public function downloadInvoice($orderNumber)
    {
        try {
            $booking = $this->getBookingByOrder($orderNumber);

            $booking->load(['destination', 'items', 'payment']);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', compact('booking'))
                ->setPaper([0, 0, 226.77, 600], 'portrait'); // 80mm width, auto height

            return $pdf->download('Invoice-TNLL-' . $booking->order_number . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Invoice PDF Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat invoice: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form for pending booking.
     */
    public function edit($orderNumber)
    {
        $booking = $this->getBookingByOrder($orderNumber);

        // Only allow editing pending bookings
        if ($booking->status !== Booking::STATUS_PENDING) {
            return redirect()->route('booking.show', $orderNumber)
                ->with('error', 'Booking yang sudah dibayar tidak dapat diubah.');
        }

        $booking->load(['destination.prices' => fn($q) => $q->where('is_active', true), 'items']);

        // Get available coupons
        $coupons = \App\Models\Coupon::valid()
            ->get()
            ->filter(fn($coupon) => $this->isCouponApplicable($coupon, $booking->destination))
            ->values();

        return \Inertia\Inertia::render('Booking/Edit', [
            'booking' => $booking,
            'destination' => $booking->destination,
            'coupons' => $coupons,
        ]);
    }

    /**
     * Update pending booking.
     */
    public function update(Request $request, $orderNumber)
    {
        $booking = $this->getBookingByOrder($orderNumber);

        // Only allow updating pending bookings
        if ($booking->status !== Booking::STATUS_PENDING) {
            return back()->with('error', 'Booking yang sudah dibayar tidak dapat diubah.');
        }

        $destination = $booking->destination->load('prices');

        $validated = $request->validate([
            'visit_date' => 'required|date|after_or_equal:today',
            'leader_name' => 'required|string|max:255',
            'leader_phone' => 'required|string|max:20',
            'leader_email' => 'required|email',
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:0',
            'coupon_code' => 'nullable|string|max:50',
        ]);

        // Calculate booking items
        $calculation = $this->bookingService->calculateBookingItems(
            $validated['quantities'],
            $destination->prices
        );

        // Validate at least 1 visitor
        if ($calculation['total_visitors'] < 1) {
            return back()->withErrors(['quantities' => 'Minimal 1 pengunjung diperlukan.'])->withInput();
        }

        DB::beginTransaction();

        try {
            $serviceFee = 5000;
            $grossTotal = $calculation['subtotal'] + $serviceFee;

            // Apply coupon
            $couponResult = $this->bookingService->applyCoupon(
                $validated['coupon_code'] ?? null,
                $grossTotal,
                $destination->id
            );

            $totalAmount = max(0, $grossTotal - $couponResult['discount']);

            // Update booking
            $booking->update(array_merge([
                'visit_date' => $validated['visit_date'],
                'leader_name' => $validated['leader_name'],
                'leader_phone' => $validated['leader_phone'],
                'leader_email' => $validated['leader_email'],
                'total_visitors' => $calculation['total_visitors'],
                'subtotal' => $calculation['subtotal'],
                'service_fee' => $serviceFee,
                'discount' => $couponResult['discount'],
                'discount_code' => $couponResult['discount_code'],
                'total_amount' => $totalAmount,
            ], $calculation['summary']));

            // Delete old items and create new ones
            $booking->items()->delete();
            foreach ($calculation['items'] as $item) {
                $booking->items()->create($item);
            }

            // If total is 0 (free due to coupon), auto-confirm payment
            if ($totalAmount <= 0) {
                $booking->update([
                    'status' => Booking::STATUS_CONFIRMED,
                    'confirmed_at' => now(),
                ]);

                // Create free payment record
                $booking->payments()->delete();
                $booking->payments()->create([
                    'order_id' => $booking->order_number,
                    'payment_type' => 'free',
                    'payment_channel' => 'coupon',
                    'gross_amount' => 0,
                    'status' => 'success',
                    'paid_at' => now(),
                ]);

                // Mark coupon as used
                if ($couponResult['discount_code']) {
                    $coupon = \App\Models\Coupon::findByCode($couponResult['discount_code']);
                    if ($coupon && auth()->id()) {
                        $coupon->markAsUsed(auth()->id(), $booking->id);
                    }
                }

                // Generate tickets
                app(\App\Services\TicketService::class)->generateTicketsForBooking($booking);

                DB::commit();

                $this->notificationService->notifyPaymentSuccess($booking);

                return redirect()->route('booking.success', $booking->order_number)
                    ->with('success', 'Booking berhasil diperbarui dan dikonfirmasi!');
            }

            DB::commit();

            return redirect()->route('booking.payment', $booking->order_number)
                ->with('success', 'Booking berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking update error: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()])->withInput();
        }
    }
}
