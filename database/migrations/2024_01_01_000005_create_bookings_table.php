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
        if (!Schema::hasTable('bookings')) {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            
            // Visit Information
            $table->date('visit_date');
            $table->time('visit_time')->nullable();
            
            // Leader Information (Ketua Rombongan)
            $table->string('leader_name');
            $table->string('leader_nik', 20)->nullable(); // 16 digit NIK
            $table->string('leader_phone');
            $table->string('leader_email');
            $table->string('leader_address')->nullable();
            
            // Visitor Counts
            $table->unsignedInteger('total_visitors')->default(0);
            $table->unsignedInteger('total_adults')->default(0);
            $table->unsignedInteger('total_children')->default(0);
            $table->unsignedInteger('total_seniors')->default(0);
            
            // Vehicle Counts
            $table->unsignedInteger('total_motorcycles')->default(0);
            $table->unsignedInteger('total_cars')->default(0);
            $table->unsignedInteger('total_buses')->default(0);
            
            // Additional Information
            $table->text('special_requests')->nullable();
            
            // Pricing
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->string('discount_code')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            
            // Status
            $table->enum('status', [
                'pending',        // Waiting for payment method selection
                'awaiting_cash',  // Cash payment at counter pending
                'paid',           // Payment received
                'confirmed',      // Confirmed by admin (optional)
                'used',           // Ticket has been used
                'cancelled',      // Cancelled by user/admin
                'expired',        // Payment expired
                'refunded',       // Refunded
            ])->default('pending');
            
            $table->text('cancel_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('expired_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['visit_date', 'status']);
            $table->index(['user_id', 'status']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

