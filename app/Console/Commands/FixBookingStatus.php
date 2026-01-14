<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Ticket;
use Illuminate\Console\Command;

class FixBookingStatus extends Command
{
    protected $signature = 'booking:fix-status';
    protected $description = 'Fix booking status for bookings where all tickets are used';

    public function handle()
    {
        $this->info('Finding bookings with all tickets used but status still confirmed/paid...');

        $bookings = Booking::whereIn('status', ['confirmed', 'paid'])
            ->get()
            ->filter(function ($booking) {
                $allTicketsUsed = $booking->tickets()->count() > 0
                    && $booking->tickets()->where('status', '!=', 'used')->count() === 0;
                return $allTicketsUsed;
            });

        $this->info("Found {$bookings->count()} bookings to update.");

        foreach ($bookings as $booking) {
            $booking->update([
                'status' => Booking::STATUS_USED,
                'used_at' => now(),
            ]);
            $this->line("Updated: {$booking->order_number}");
        }

        $this->info('Done!');
        return 0;
    }
}
