<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make image_path nullable for flora and fauna tables
        // since we now store images in database (image_data field)

        DB::statement("ALTER TABLE flora MODIFY image_path VARCHAR(255) NULL");
        DB::statement("ALTER TABLE fauna MODIFY image_path VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: This might fail if there are NULL values
        DB::statement("ALTER TABLE flora MODIFY image_path VARCHAR(255) NOT NULL DEFAULT ''");
        DB::statement("ALTER TABLE fauna MODIFY image_path VARCHAR(255) NOT NULL DEFAULT ''");
    }
};
