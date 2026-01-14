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
        if (!Schema::hasTable('feedbacks')) {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('display_name')->nullable();
            $table->boolean('is_anonymous')->default(false);
            $table->string('subject');
            $table->text('message');
            $table->string('contact_whatsapp')->nullable();
            $table->string('contact_email')->nullable();
            $table->unsignedTinyInteger('rating')->nullable(); // 1-5 stars
            $table->enum('status', ['unread', 'read', 'replied', 'archived'])->default('unread');
            $table->boolean('is_published')->default(true); // Show on public page
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            // Index for public display
            $table->index(['is_published', 'status', 'created_at']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
