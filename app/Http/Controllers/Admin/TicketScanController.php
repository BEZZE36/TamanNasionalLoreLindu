<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Booking;
use App\Services\TicketScanService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TicketScanController extends Controller
{
    use LogsActivity;

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

            $this->logScan(null, "Scan gagal - kode tidak ditemukan: {$code}");

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
        // Handle both pending and legacy awaiting_cash status for payment
        if (in_array($booking->status, [Booking::STATUS_PENDING, 'awaiting_cash'])) {
            $this->logScan($booking, "Scan booking menunggu pembayaran: {$booking->order_number}");

            return response()->json([
                'valid' => false,
                'status' => 'payment_required',
                'title' => 'Menunggu Pembayaran',
                'message' => 'Booking ditemukan. Silakan konfirmasi pembayaran tunai.',
                'booking' => $this->scanService->formatBookingDetail($booking),
            ]);
        }

        // If booking is confirmed but has no tickets, auto-generate them
        if ($booking->tickets->isEmpty() && in_array($booking->status, [Booking::STATUS_CONFIRMED, 'paid', 'confirmed'])) {
            try {
                app(\App\Services\TicketService::class)->generateTicketsForBooking($booking);
                $booking->load('tickets');
                $this->logScan($booking, "Auto-generated tickets for confirmed booking: {$booking->order_number}");
            } catch (\Exception $e) {
                $this->logScan($booking, "Failed to auto-generate tickets: {$e->getMessage()}");
            }
        }

        if ($booking->tickets->isNotEmpty()) {
            $ticket = $booking->tickets->first();
            $ticket->load(['booking', 'booking.destination', 'booking.payment']);
            return $this->handleTicketFound($ticket);
        }

        $this->logScan($booking, "Scan booking valid tanpa tiket: {$booking->order_number}");

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

        $this->logScan($ticket, "Scan tiket: {$ticket->ticket_code} - Status: {$result['status']}");

        return response()->json($result);
    }

    public function processPayment(Request $request)
    {
        $request->validate(['booking_id' => 'required|exists:bookings,id']);

        $booking = Booking::with('tickets')->find($request->booking_id);
        $result = $this->scanService->processCashPayment($booking);

        if ($result['success']) {
            $this->logActivity('checkin', "Konfirmasi pembayaran tunai booking: {$booking->order_number} - Rp " . number_format($booking->total_amount, 0, ',', '.'), $booking);

            if (isset($result['booking'])) {
                $result['booking'] = $this->scanService->formatBookingDetail($result['booking']);
            }
        }

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function validateEntry(Request $request)
    {
        $request->validate(['ticket_id' => 'required|exists:tickets,id']);

        $ticket = Ticket::with('booking')->find($request->ticket_id);
        $result = $this->scanService->validateTicketEntry($ticket);

        if ($result['success']) {
            $this->logCheckin($ticket, "Check-in tiket: {$ticket->ticket_code}");

            if (isset($result['ticket'])) {
                $result['ticket'] = $this->scanService->formatTicketDetail($result['ticket']);
            }
        }

        return response()->json($result);
    }
}
