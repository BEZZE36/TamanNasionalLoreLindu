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
        if (!Schema::hasTable('destination_prices')) {
        Schema::create('destination_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->enum('category', [
                'adult',    // Dewasa (12+ years)
                'child',    // Anak (3-11 years)
                'senior',   // Lansia/Pelajar (60+ or student groups)
                'motor',    // Parkir Motor
                'car',      // Parkir Mobil
                'bus',      // Parkir Bus
            ]);
            $table->string('label'); // Display name, e.g., "Tiket Dewasa"
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('weekend_price', 12, 2)->nullable(); // Weekend/holiday price
            $table->unsignedInteger('min_age')->nullable();
            $table->unsignedInteger('max_age')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['destination_id', 'category']);
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('destination_prices');
    }
};
