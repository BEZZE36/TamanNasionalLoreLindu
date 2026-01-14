<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\User;
use App\Services\Admin\BookingService;
use App\Traits\LogsActivity;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookingController extends Controller
{
    use LogsActivity;

    public function __construct(
        protected BookingService $bookingService
    ) {
    }

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
            'user_avatar' => $b->user?->avatar_url ?? null,
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

        $stats = [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'paid' => Booking::where('status', 'paid')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'used' => Booking::where('status', 'used')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'revenue' => Booking::whereIn('status', ['paid', 'confirmed', 'used'])->sum('total_amount'),
            'this_month' => Booking::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'destinations' => $destinations,
            'filters' => $request->only(['search', 'destination_id', 'status']),
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $destinations = Destination::active()->with('prices')->get()->map(fn($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'prices' => $d->prices->map(fn($p) => ['category' => $p->category, 'price' => $p->price])->toArray(),
        ]);
        $users = User::latest()->limit(50)->get(['id', 'name', 'email', 'phone']);

        return Inertia::render('Admin/Bookings/Create', [
            'destinations' => $destinations,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateBookingRequest($request);
        $destination = Destination::with('prices')->findOrFail($validated['destination_id']);

        $totalVisitors = ($validated['total_adults'] ?? 0) + ($validated['total_children'] ?? 0) + ($validated['total_seniors'] ?? 0);
        if ($totalVisitors < 1) {
            return back()->withErrors(['total_adults' => 'Minimal 1 pengunjung diperlukan.'])->withInput();
        }

        DB::beginTransaction();
        try {
            $booking = $this->bookingService->createBooking($validated, $destination);

            if (in_array($booking->status, ['paid', 'confirmed'])) {
                $this->bookingService->processPaymentAndTickets($booking);
            }

            $this->logCreate($booking, 'Booking', $booking->order_number);

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
                'visit_date' => $booking->visit_date?->format('Y-m-d'),
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

        $oldValues = $booking->only(['visit_date', 'leader_name', 'status']);

        DB::beginTransaction();
        try {
            $this->bookingService->updateBooking($booking, $validated);

            $newValues = $booking->fresh()->only(['visit_date', 'leader_name', 'status']);
            $this->logUpdate($booking, 'Booking', $oldValues, $newValues, $booking->order_number);

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

        $oldStatus = $booking->status;
        $booking->update(['status' => $validated['status']]);

        if (in_array($validated['status'], ['paid', 'confirmed']) && $booking->tickets->isEmpty()) {
            $this->bookingService->processPaymentAndTickets($booking);
        }

        $this->logActivity('update', "Mengubah status booking {$booking->order_number}: {$oldStatus} → {$validated['status']}", $booking, ['status' => $oldStatus], ['status' => $validated['status']]);

        return back()->with('success', 'Status booking berhasil diperbarui.');
    }

    public function cancel(Booking $booking)
    {
        if ($booking->status === Booking::STATUS_CANCELLED) {
            return back()->with('error', 'Booking sudah dibatalkan.');
        }

        $oldStatus = $booking->status;
        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        if ($booking->payment && $booking->payment->status === Payment::STATUS_PENDING) {
            $booking->payment->update(['status' => Payment::STATUS_FAILED]);
        }

        $booking->tickets()->update(['status' => 'cancelled']);

        $this->logActivity('update', "Membatalkan booking: {$booking->order_number}", $booking, ['status' => $oldStatus], ['status' => 'cancelled']);

        return back()->with('success', 'Booking berhasil dibatalkan.');
    }

    public function sendTicket(Booking $booking)
    {
        if (!$booking->isPaid() && $booking->status !== 'confirmed') {
            return back()->with('error', 'Tiket hanya dapat dikirim untuk pesanan yang sudah lunas/dikonfirmasi.');
        }

        if ($booking->tickets->isEmpty()) {
            $this->bookingService->processPaymentAndTickets($booking, 'cash');
            $booking->refresh();
        }

        $success = $this->bookingService->sendConfirmationEmail($booking);

        if ($success) {
            $this->logSend($booking, 'E-Ticket', $booking->order_number);
        }

        return $success
            ? back()->with('success', 'E-Ticket berhasil dikirim ke email user.')
            : back()->with('error', 'Gagal mengirim email.');
    }

    public function downloadInvoice(Booking $booking)
    {
        $booking->load(['destination', 'items', 'payment']);

        $pdf = Pdf::loadView('pdf.invoice', compact('booking'))
            ->setPaper([0, 0, 226.77, 600], 'portrait');

        return $pdf->download('Invoice-TNLL-' . $booking->order_number . '.pdf');
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');

        $bookings = Booking::with(['user', 'destination', 'payment'])
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($request->filled('search'), fn($q) => $q->where(function ($query) use ($request) {
                $query->where('order_number', 'like', '%' . $request->search . '%')
                    ->orWhere('leader_name', 'like', '%' . $request->search . '%');
            }))
            ->when($request->filled('destination_id'), fn($q) => $q->where('destination_id', $request->destination_id))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('visit_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($q) => $q->whereDate('visit_date', '<=', $request->date_to))
            ->latest()
            ->get();

        $data = $bookings->map(fn($b) => [
            'Order Number' => $b->order_number,
            'Tanggal Booking' => $b->created_at->format('d/m/Y H:i'),
            'Tanggal Kunjungan' => $b->visit_date instanceof \DateTimeInterface ? $b->visit_date->format('d/m/Y') : $b->visit_date,
            'Destinasi' => $b->destination->name ?? '-',
            'Nama Ketua' => $b->leader_name,
            'Email' => $b->leader_email,
            'Telepon' => $b->leader_phone,
            'Total Pengunjung' => $b->total_visitors,
            'Total Amount' => 'Rp ' . number_format($b->total_amount, 0, ',', '.'),
            'Status' => ucfirst($b->status),
            'Status Pembayaran' => ucfirst($b->payment?->status ?? 'N/A'),
        ])->toArray();

        $this->logExport('Booking', strtoupper($format), count($data));

        if ($format === 'pdf') {
            return $this->exportPdf($data, $bookings);
        }

        if ($format === 'excel') {
            return $this->exportExcel($data);
        }

        return $this->exportCsv($data);
    }

    protected function exportCsv(array $data)
    {
        $filename = 'bookings-' . now()->format('YmdHis') . '.csv';

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if (!empty($data)) {
                fputcsv($file, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=$filename",
        ]);
    }

    protected function exportExcel(array $data)
    {
        $filename = 'bookings-' . now()->format('YmdHis') . '.xlsx';

        $export = new \App\Exports\BookingsExport($data);
        return $export->download($filename);
    }

    protected function exportPdf(array $data, $bookings)
    {
        $filename = 'bookings-' . now()->format('YmdHis') . '.pdf';

        $stats = [
            'total' => count($data),
            'total_revenue' => $bookings->whereIn('status', ['paid', 'confirmed', 'used'])->sum('total_amount'),
        ];

        $pdf = Pdf::loadView('exports.bookings-pdf', [
            'bookings' => $data,
            'stats' => $stats,
            'generated_at' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    public function destroy(Booking $booking)
    {
        DB::beginTransaction();
        try {
            $orderNumber = $booking->order_number;

            $this->logDelete($booking, 'Booking', $orderNumber);

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
