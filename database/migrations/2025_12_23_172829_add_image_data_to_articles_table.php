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
        if (Schema::hasColumn('articles', 'image_data')) {
            return;
        }
        Schema::table('articles', function (Blueprint $table) {
            // $table->longBinary('image_data')->nullable()->after('content');
            $table->string('image_mime')->nullable()->after('content');
            // We'll keep featured_image as fallback for now or make it nullable if not already
            $table->string('featured_image')->nullable()->change();
        });

        // Add LONGBLOB column using raw SQL for MySQL
        DB::statement("ALTER TABLE articles ADD image_data LONGBLOB NULL AFTER content");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime']);
        });
    }
};
