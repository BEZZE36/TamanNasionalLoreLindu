<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Booking;
use App\Services\TicketScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TicketScanController extends Controller
{
    public function __construct(
        protected TicketScanService $scanService
    ) {
    }

    public function scan()
    {
        $dbConnected = false;
        $ticketCount = 0;
        $todayStats = ['scans' => 0, 'payments' => 0, 'revenue' => 0];
        $recentTransactions = collect();

        try {
            DB::connection()->getPdo();
            $dbConnected = true;
            $ticketCount = Ticket::count();
            $todayStats = $this->scanService->getTodayStats();
            $recentTransactions = $this->scanService->getRecentTransactions();
        } catch (\Exception $e) {
            $dbConnected = false;
        }

        return Inertia::render('Admin/Tickets/Scan', compact('dbConnected', 'ticketCount', 'todayStats', 'recentTransactions'));
    }

    public function checkTicket(Request $request)
    {
        $request->validate(['ticket_code' => 'required|string']);

        $code = strtoupper(trim($request->ticket_code));
        $ticket = $this->scanService->findTicket($code);

        if (!$ticket) {
            $booking = $this->scanService->findBookingByCode($code);
            if ($booking) {
                return $this->handleBookingFound($booking);
            }

            return response()->json([
                'valid' => false,
                'status' => 'not_found',
                'title' => 'Data Tidak Ditemukan',
                'message' => "Kode \"{$code}\" tidak ditemukan dalam sistem.",
            ]);
        }

        return $this->handleTicketFound($ticket);
    }

    protected function handleBookingFound(Booking $booking)
    {
        if (in_array($booking->status, [Booking::STATUS_PENDING, Booking::STATUS_AWAITING_CASH])) {
            return response()->json([
                'valid' => false,
                'status' => 'payment_required',
                'title' => 'Menunggu Pembayaran',
                'message' => 'Booking ditemukan. Silakan konfirmasi pembayaran tunai.',
                'booking' => $this->scanService->formatBookingDetail($booking),
            ]);
        }

        if ($booking->tickets->isNotEmpty()) {
            $ticket = $booking->tickets->first();
            $ticket->load(['booking', 'booking.destination', 'booking.payment']);
            return $this->handleTicketFound($ticket);
        }

        return response()->json([
            'valid' => true,
            'status' => 'paid_no_tickets',
            'title' => 'Booking Valid',
            'message' => 'Booking sudah dibayar tapi tiket belum digenerate.',
            'booking' => $this->scanService->formatBookingDetail($booking),
        ]);
    }

    protected function handleTicketFound(Ticket $ticket)
    {
        $booking = $ticket->booking;
        $result = $this->scanService->checkTicketStatus($ticket);

        $result['ticket'] = $this->scanService->formatTicketDetail($ticket);
        if ($booking) {
            $result['booking'] = $this->scanService->formatBookingDetail($booking);
        }

        return response()->json($result);
    }

    public function processPayment(Request $request)
    {
        $request->validate(['booking_id' => 'required|exists:bookings,id']);

        $booking = Booking::with('tickets')->find($request->booking_id);
        $result = $this->scanService->processCashPayment($booking);

        if ($result['success'] && isset($result['booking'])) {
            $result['booking'] = $this->scanService->formatBookingDetail($result['booking']);
        }

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function validateEntry(Request $request)
    {
        $request->validate(['ticket_id' => 'required|exists:tickets,id']);

        $ticket = Ticket::with('booking')->find($request->ticket_id);
        $result = $this->scanService->validateTicketEntry($ticket);

        if ($result['success'] && isset($result['ticket'])) {
            $result['ticket'] = $this->scanService->formatTicketDetail($result['ticket']);
        }

        return response()->json($result);
    }
}
