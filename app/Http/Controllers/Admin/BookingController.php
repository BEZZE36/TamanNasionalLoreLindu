<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\User;
use App\Services\Admin\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
    }

    /**
     * Get pending bookings count for sidebar badge
     */
    public function pendingCount()
    {
        $count = Booking::where('status', 'pending')->count();

        return response()->json(['count' => $count]);
    }

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'destination', 'payment', 'tickets']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                    ->orWhere('leader_name', 'like', '%' . $request->search . '%')
                    ->orWhere('leader_email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('visit_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('visit_date', '<=', $request->date_to);
        }

        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();
        $bookings->getCollection()->transform(fn($b) => [
            'id' => $b->id,
            'order_number' => $b->order_number,
            'leader_name' => $b->leader_name,
            'leader_phone' => $b->leader_phone,
            'leader_email' => $b->leader_email,
            'visit_date' => $b->visit_date,
            'total_visitors' => $b->total_visitors,
            'total_amount' => $b->total_amount,
            'formatted_total_amount' => $b->formatted_total_amount,
            'status' => $b->status,
            'payment_type' => $b->payment?->payment_type,
            'destination' => $b->destination ? ['id' => $b->destination->id, 'name' => $b->destination->name] : null,
            'created_at' => $b->created_at,
        ]);
        $destinations = Destination::select('id', 'name')->get();

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'destinations' => $destinations,
            'filters' => $request->only(['search', 'destination_id', 'status']),
        ]);
    }

    public function create()
    {
        $destinations = Destination::active()->with('prices')->get()->map(fn($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'prices' => $d->prices->map(fn($p) => ['category' => $p->category, 'price' => $p->price])->toArray(),
        ]);
        $users = User::latest()->limit(50)->get(['id', 'name', 'email', 'phone_number']);

        return Inertia::render('Admin/Bookings/Create', [
            'destinations' => $destinations,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBookingRequest($request);

        $destination = Destination::with('prices')->findOrFail($validated['destination_id']);

        // Validate at least 1 visitor
        $totalVisitors = ($validated['total_adults'] ?? 0) + ($validated['total_children'] ?? 0) + ($validated['total_seniors'] ?? 0);
        if ($totalVisitors < 1) {
            return back()->withErrors(['total_adults' => 'Minimal 1 pengunjung diperlukan.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $booking = $this->bookingService->createBooking($validated, $destination);

            // Handle Payment Status if Paid/Confirmed
            if (in_array($booking->status, ['paid', 'confirmed'])) {
                $this->bookingService->processPaymentAndTickets($booking);
            }

            DB::commit();
            return redirect()->route('admin.bookings.show', $booking)->with('success', 'Booking berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat booking: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'destination', 'payment', 'tickets', 'items.destinationPrice']);

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => [
                'id' => $booking->id,
                'order_number' => $booking->order_number,
                'leader_name' => $booking->leader_name,
                'leader_email' => $booking->leader_email,
                'leader_phone' => $booking->leader_phone,
                'visit_date' => $booking->visit_date,
                'total_visitors' => $booking->total_visitors,
                'total_adults' => $booking->total_adults,
                'total_children' => $booking->total_children,
                'total_seniors' => $booking->total_seniors,
                'total_motorcycles' => $booking->total_motorcycles,
                'total_cars' => $booking->total_cars,
                'total_buses' => $booking->total_buses,
                'total_amount' => $booking->total_amount,
                'status' => $booking->status,
                'payment_type' => $booking->payment?->payment_type,
                'created_at' => $booking->created_at,
                'destination' => $booking->destination ? ['id' => $booking->destination->id, 'name' => $booking->destination->name] : null,
                'tickets' => $booking->tickets->map(fn($t) => [
                    'id' => $t->id,
                    'ticket_code' => $t->ticket_code,
                    'visitor_name' => $t->visitor_name,
                    'is_used' => $t->is_used,
                ])->toArray(),
                'items' => $booking->items->map(fn($i) => [
                    'id' => $i->id,
                    'quantity' => $i->quantity,
                    'label' => $i->label,
                    'total_price' => $i->total_price,
                ])->toArray(),
            ],
        ]);
    }

    public function edit(Booking $booking)
    {
        $booking->load(['destination', 'items']);
        $destinations = Destination::active()->get(['id', 'name']);

        return Inertia::render('Admin/Bookings/Edit', [
            'booking' => [
                'id' => $booking->id,
                'order_number' => $booking->order_number,
                'leader_name' => $booking->leader_name,
                'leader_email' => $booking->leader_email,
                'leader_phone' => $booking->leader_phone,
                'visit_date' => $booking->visit_date,
                'total_visitors' => $booking->total_visitors,
                'total_amount' => $booking->total_amount,
                'status' => $booking->status,
                'destination' => $booking->destination ? ['id' => $booking->destination->id, 'name' => $booking->destination->name] : null,
                'items' => $booking->items->map(fn($i) => ['id' => $i->id, 'quantity' => $i->quantity, 'label' => $i->label, 'total_price' => $i->total_price])->toArray(),
            ],
            'destinations' => $destinations,
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'visit_date' => 'required|date',
            'leader_name' => 'required|string|max:255',
            'leader_phone' => 'required|string|max:20',
            'leader_email' => 'required|email',
            'status' => 'required|in:pending,paid,confirmed,cancelled,used',
        ]);

        DB::beginTransaction();
        try {
            $this->bookingService->updateBooking($booking, $validated);
            DB::commit();
            return redirect()->route('admin.bookings.show', $booking)->with('success', 'Booking berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,confirmed,used,cancelled,expired',
        ]);

        $booking->update(['status' => $validated['status']]);

        // Auto generate tickets if marked as paid/confirmed
        if (in_array($validated['status'], ['paid', 'confirmed']) && $booking->tickets->isEmpty()) {
            $this->bookingService->processPaymentAndTickets($booking);
        }

        return back()->with('success', 'Status booking berhasil diperbarui.');
    }

    public function cancel(Booking $booking)
    {
        if ($booking->status === Booking::STATUS_CANCELLED) {
            return back()->with('error', 'Booking sudah dibatalkan.');
        }

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        if ($booking->payment && $booking->payment->status === Payment::STATUS_PENDING) {
            $booking->payment->update(['status' => Payment::STATUS_FAILED]);
        }

        $booking->tickets()->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }

    public function sendTicket(Booking $booking)
    {
        if (!$booking->isPaid() && $booking->status !== 'confirmed') {
            return back()->with('error', 'Tiket hanya dapat dikirim untuk pesanan yang sudah lunas/dikonfirmasi.');
        }

        if ($booking->tickets->isEmpty()) {
            $this->bookingService->processPaymentAndTickets($booking, 'manual_resend');
            $booking->refresh();
        }

        $success = $this->bookingService->sendConfirmationEmail($booking);

        return $success
            ? back()->with('success', 'E-Ticket berhasil dikirim ke email user.')
            : back()->with('error', 'Gagal mengirim email.');
    }

    public function export(Request $request)
    {
        $bookings = Booking::with(['user', 'destination', 'payment'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('visit_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('visit_date', '<=', $request->date_to))
            ->get();

        $filename = 'bookings-' . now()->format('YmdHis') . '.csv';

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Order Number', 'Tanggal Booking', 'Tanggal Kunjungan', 'Destinasi', 'Nama Ketua', 'Email', 'Telepon', 'Total Pengunjung', 'Total Amount', 'Status', 'Status Pembayaran']);
            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->order_number,
                    $booking->created_at->format('d/m/Y H:i'),
                    $booking->visit_date instanceof \DateTimeInterface ? $booking->visit_date->format('d/m/Y') : $booking->visit_date,
                    $booking->destination->name ?? '-',
                    $booking->leader_name,
                    $booking->leader_email,
                    $booking->leader_phone,
                    $booking->total_visitors,
                    $booking->total_amount,
                    $booking->status,
                    $booking->payment?->status ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    public function destroy(Booking $booking)
    {
        DB::beginTransaction();
        try {
            $booking->tickets()->delete();
            $booking->items()->delete();
            $booking->payments()->delete();
            \App\Models\CouponUsage::where('booking_id', $booking->id)->delete();
            $booking->delete();

            DB::commit();
            return redirect()->route('admin.bookings.index')->with('success', 'Booking berhasil dihapus secara permanen.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus booking: ' . $e->getMessage());
        }
    }

    /**
     * Validation rules for booking creation
     */
    protected function validateBookingRequest(Request $request): array
    {
        return $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'destination_id' => 'required|exists:destinations,id',
            'visit_date' => 'required|date',
            'leader_name' => 'required|string|max:255',
            'leader_phone' => 'required|string|max:20',
            'leader_email' => 'required|email',
            'total_adults' => 'required|integer|min:0',
            'total_children' => 'nullable|integer|min:0',
            'total_seniors' => 'nullable|integer|min:0',
            'total_motorcycles' => 'nullable|integer|min:0',
            'total_cars' => 'nullable|integer|min:0',
            'total_buses' => 'nullable|integer|min:0',
            'status' => 'required|in:pending,paid,confirmed',
        ]);
    }
}
