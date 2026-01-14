<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Console\Command;

class SyncCouponUsages extends Command
{
    protected $signature = 'coupon:sync';
    protected $description = 'Sync missing CouponUsage records for paid bookings with coupons';

    public function handle()
    {
        $this->info('Syncing coupon usages...');

        $bookings = Booking::whereNotNull('discount_code')
            ->whereIn('status', ['paid', 'confirmed', 'used'])
            ->get();

        $created = 0;

        foreach ($bookings as $booking) {
            // Check if usage already exists
            if (CouponUsage::where('booking_id', $booking->id)->exists()) {
                continue;
            }

            // Find coupon
            $coupon = Coupon::where('code', $booking->discount_code)->first();
            if (!$coupon) {
                $this->warn("Coupon not found: {$booking->discount_code}");
                continue;
            }

            if (!$booking->user_id) {
                $this->warn("Booking {$booking->order_number} has no user_id");
                continue;
            }

            // Create usage record
            $coupon->markAsUsed($booking->user_id, $booking->id);
            $created++;

            $this->info("Created usage for booking {$booking->order_number} with coupon {$coupon->code}");
        }

        $this->info("Done! Created {$created} coupon usage records.");

        return 0;
    }
}
