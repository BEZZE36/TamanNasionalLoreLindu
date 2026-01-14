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
        if (!Schema::hasTable('tickets')) {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('ticket_code')->unique();
            $table->string('qr_code_path')->nullable();
            $table->text('qr_data')->nullable(); // Encrypted QR data
            
            // Ticket Info
            $table->date('valid_date');
            $table->unsignedInteger('total_persons')->default(1);
            
            // Status
            $table->enum('status', [
                'valid',
                'used',
                'expired',
                'cancelled',
            ])->default('valid');
            
            // Validation Info
            $table->timestamp('used_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('validation_notes')->nullable();
            
            // Download tracking
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['valid_date', 'status']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
