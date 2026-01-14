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
        // Make image_path nullable since we store images in database now
        DB::statement("ALTER TABLE destination_images MODIFY image_path VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert image_path to NOT NULL
        DB::statement("ALTER TABLE destination_images MODIFY image_path VARCHAR(255) NOT NULL");
    }
};
