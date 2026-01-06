<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            
            // Midtrans Transaction Info
            $table->string('transaction_id')->unique()->nullable();
            $table->string('order_id')->unique(); // Same as booking order_number
            $table->string('snap_token')->nullable();
            
            // Payment Details
            $table->enum('payment_type', [
                'cash',         // Cash payment at counter
                'bank_transfer',
                'qris',
                'gopay',
                'shopeepay',
                'cstore',       // Convenience store (Alfamart/Indomaret)
                'credit_card',
                'other',
            ])->nullable();
            $table->string('payment_channel')->nullable(); // bca, bni, bri, alfamart, counter, etc
            
            // VA/Payment Numbers
            $table->string('va_number')->nullable();
            $table->string('payment_code')->nullable(); // For cstore
            $table->string('qr_code_url')->nullable();
            
            // Amount
            $table->decimal('gross_amount', 12, 2);
            
            // Status
            $table->enum('status', [
                'pending',
                'success',
                'failed',
                'expired',
                'refunded',
                'challenge',    // For credit card fraud detection
                'deny',
            ])->default('pending');
            
            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            
            // Midtrans Response Storage
            $table->json('snap_response')->nullable();
            $table->json('notification_response')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('payment_type');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
