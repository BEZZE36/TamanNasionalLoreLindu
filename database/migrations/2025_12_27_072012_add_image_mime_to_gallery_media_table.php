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
        if (Schema::hasColumn('gallery_media', 'image_mime')) {
            return;
        }
        Schema::table('gallery_media', function (Blueprint $table) {
            $table->string('image_mime', 50)->nullable()->after('image_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery_media', function (Blueprint $table) {
            $table->dropColumn('image_mime');
        });
    }
};
