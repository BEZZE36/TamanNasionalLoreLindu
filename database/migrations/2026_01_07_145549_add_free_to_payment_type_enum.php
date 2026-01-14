<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'free' to payment_type enum
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_type ENUM(
            'cash',
            'bank_transfer',
            'qris',
            'gopay',
            'shopeepay',
            'cstore',
            'credit_card',
            'other',
            'free'
        ) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY COLUMN payment_type ENUM(
            'cash',
            'bank_transfer',
            'qris',
            'gopay',
            'shopeepay',
            'cstore',
            'credit_card',
            'other'
        ) NULL");
    }
};
