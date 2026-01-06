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
            'visit_date' => 'required|date|after:today',
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

        if (!in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_AWAITING_CASH])) {
            return redirect()->route('booking.success', $booking->order_number);
        }

        if ($booking->status === Booking::STATUS_AWAITING_CASH) {
            $booking->update(['status' => Booking::STATUS_PENDING]);
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
        $booking = $this->getBookingByOrder($orderNumber);

        if ($booking->status !== Booking::STATUS_PAID) {
            return back()->with('error', 'E-Ticket hanya tersedia untuk pesanan yang sudah lunas.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.ticket', compact('booking'));

        return $pdf->download('E-Ticket-TNLL-' . $booking->order_number . '.pdf');
    }
}
