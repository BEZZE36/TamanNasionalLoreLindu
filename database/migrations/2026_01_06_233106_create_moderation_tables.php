<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Blocked commenters
        Schema::create('blocked_commenters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained()->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->timestamp('blocked_until')->nullable(); // null = permanent
            $table->timestamps();

            $table->unique('user_id');
        });

        // Bad words filter
        Schema::create('bad_words', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->string('replacement')->default('***');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('word');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bad_words');
        Schema::dropIfExists('blocked_commenters');
    }
};
