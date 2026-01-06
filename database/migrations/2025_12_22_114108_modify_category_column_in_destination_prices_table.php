<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Change category from ENUM to VARCHAR to allow flexible category strings
        DB::statement("ALTER TABLE destination_prices MODIFY category VARCHAR(100) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reverting to ENUM might fail if data contains values not in the enum list
        // This is intentionally left as string to prevent data loss
    }
};
