<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Models\Booking;
use Illuminate\Console\Command;

class ExpireOldTickets extends Command
{
    protected $signature = 'tickets:expire';
    protected $description = 'Expire tickets and bookings that have passed their visit date';

    public function handle()
    {
        $this->info('Finding expired tickets...');

        // Find valid tickets where visit date has passed (before today)
        $expiredTickets = Ticket::where('status', 'valid')
            ->whereDate('valid_date', '<', today())
            ->get();

        $this->info("Found {$expiredTickets->count()} expired tickets.");

        foreach ($expiredTickets as $ticket) {
            $ticket->update(['status' => Ticket::STATUS_CANCELLED]);
            $this->line("Expired: {$ticket->ticket_code} (visit date: {$ticket->valid_date->format('Y-m-d')})");
        }

        // Also cancel confirmed bookings where visit date has passed and no tickets were used
        $this->info('Finding expired bookings...');

        $expiredBookings = Booking::whereIn('status', ['confirmed', 'paid'])
            ->whereDate('visit_date', '<', today())
            ->whereDoesntHave('tickets', function ($q) {
                $q->where('status', 'used');
            })
            ->get();

        $this->info("Found {$expiredBookings->count()} expired bookings.");

        foreach ($expiredBookings as $booking) {
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancel_reason' => 'Expired: Tanggal kunjungan telah lewat',
                'cancelled_at' => now(),
            ]);

            // Also cancel all tickets for this booking
            $booking->tickets()->where('status', 'valid')->update([
                'status' => Ticket::STATUS_CANCELLED,
            ]);

            $this->line("Expired booking: {$booking->order_number}");
        }

        $this->info('Done!');
        return 0;
    }
}
