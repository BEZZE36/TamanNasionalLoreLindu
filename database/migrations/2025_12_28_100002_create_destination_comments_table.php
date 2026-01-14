<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('destination_comments')) {
            return;
        }
        Schema::create('destination_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('destination_comments')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['destination_id', 'is_visible']);
            $table->index(['destination_id', 'is_pinned']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_comments');
    }
};
