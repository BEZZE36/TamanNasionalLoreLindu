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
        if (!Schema::hasTable('notifications')) {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Notification type for categorization
            $table->string('type', 50); // booking_created, payment_success, etc.
            
            // Content
            $table->string('title');
            $table->text('message');
            
            // Additional data (booking_id, order_number, etc.)
            $table->json('data')->nullable();
            
            // Visual styling
            $table->string('icon', 50)->default('bell'); // Icon name or emoji
            $table->string('color', 20)->default('primary'); // Color theme
            
            // Action link
            $table->string('link')->nullable();
            
            // Read status
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
