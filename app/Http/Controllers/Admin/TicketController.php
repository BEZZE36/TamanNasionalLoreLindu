<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TicketController extends Controller
{
    protected $ticketService;
    protected $notificationService;

    public function __construct(
        TicketService $ticketService,
        \App\Services\NotificationService $notificationService
    ) {
        $this->ticketService = $ticketService;
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = Ticket::with(['booking.destination', 'booking.user', 'validator']);

        if ($request->filled('search')) {
            $query->where('ticket_code', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('valid_date', $request->date);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();
        $tickets->getCollection()->transform(fn($t) => [
            'id' => $t->id,
            'ticket_code' => $t->ticket_code,
            'valid_date' => $t->valid_date,
            'status' => $t->status,
            'used_at' => $t->used_at,
            'order_number' => $t->booking?->order_number,
            'destination_name' => $t->booking?->destination?->name,
            'validator_name' => $t->validator?->name,
        ]);

        $todayStats = [
            'total' => Ticket::whereDate('valid_date', today())->count(),
            'validated' => Ticket::whereDate('used_at', today())->where('status', 'used')->count(),
            // Count all valid tickets that can be validated (any date)
            'pending' => Ticket::where('status', 'valid')->count(),
        ];

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,
            'todayStats' => $todayStats,
            'filters' => $request->only(['search', 'status', 'date']),
        ]);
    }

    public function validatePage()
    {
        return Inertia::render('Admin/Tickets/Validate');
    }

    public function checkTicket(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
        ]);

        try {
            // Check status ONLY (do not mark as used yet)
            $result = $this->ticketService->validateTicketByCode($request->ticket_code);

            // Return result regardless of validity
            return response()->json([
                'success' => $result['valid'],
                'message' => $result['message'],
                'reason' => $result['reason'] ?? 'unknown',
                'ticket' => isset($result['ticket']) ? $result['ticket']->load(['booking.destination', 'booking.user']) : null,
                'booking' => isset($result['booking']) ? $result['booking']->load(['destination', 'user']) : null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function redeem(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
        ]);

        try {
            // Re-validate to be safe
            $result = $this->ticketService->validateTicketByCode($request->ticket_code);

            if ($result['valid']) {
                $ticket = $result['ticket'];

                // Mark as used
                $ticket->update([
                    'status' => 'used',
                    'used_at' => now(),
                    'validated_by' => auth('admin')->id(),
                ]);

                // Update booking status if all tickets are now used
                $this->updateBookingStatusIfAllUsed($ticket);

                // Notify
                $this->notificationService->notifyTicketValidated($ticket);

                return response()->json([
                    'success' => true,
                    'message' => 'Tiket Berhasil Digunakan!',
                    'ticket' => $ticket->load(['booking.destination', 'booking.user']),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Gagal menggunakan tiket: ' . $result['message'],
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['booking.destination', 'booking.user', 'booking.items', 'validator']);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'ticket_code' => $ticket->ticket_code,
                'valid_date' => $ticket->valid_date,
                'status' => $ticket->status,
                'used_at' => $ticket->used_at,
                'visitor_name' => $ticket->visitor_name,
                'validator_name' => $ticket->validator?->name,
                'booking' => $ticket->booking ? [
                    'order_number' => $ticket->booking->order_number,
                    'leader_name' => $ticket->booking->leader_name,
                    'destination' => $ticket->booking->destination ? ['name' => $ticket->booking->destination->name] : null,
                ] : null,
            ],
        ]);
    }

    public function manualValidate(Ticket $ticket)
    {
        if ($ticket->status !== 'valid') {
            return back()->with('error', 'Tiket tidak dapat divalidasi. Status: ' . $ticket->status);
        }

        $ticket->update([
            'status' => 'used',
            'used_at' => now(),
            'validated_by' => auth('admin')->id(),
        ]);

        // Update booking status if all tickets are now used
        $this->updateBookingStatusIfAllUsed($ticket);

        // Send notification
        $this->notificationService->notifyTicketValidated($ticket);

        return back()->with('success', 'Tiket berhasil divalidasi secara manual.');
    }

    /**
     * Update booking status to 'used' if all tickets are now used
     */
    protected function updateBookingStatusIfAllUsed(Ticket $ticket): void
    {
        $booking = \App\Models\Booking::find($ticket->booking_id);
        if (!$booking)
            return;

        $remainingValid = Ticket::where('booking_id', $booking->id)
            ->where('status', '!=', 'used')
            ->count();

        if ($remainingValid === 0 && in_array($booking->status, [\App\Models\Booking::STATUS_CONFIRMED, 'paid', 'confirmed'])) {
            $booking->status = \App\Models\Booking::STATUS_USED;
            $booking->used_at = now();
            $booking->save();
        }
    }

    public function checkStatus($code)
    {
        $ticket = Ticket::where('ticket_code', $code)
            ->with(['booking.destination', 'booking.user'])
            ->first();

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'ticket' => $ticket,
        ]);
    }
}
