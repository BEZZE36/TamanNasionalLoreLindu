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
        if (Schema::hasTable('gallery_media')) {
            return;
        }
        Schema::create('gallery_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('video_url')->nullable();
            $table->string('alt_text')->nullable()->comment('For SEO and Accessibility');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Add LONGBLOB column for image_data (not supported by Blueprint directly)
        DB::statement("ALTER TABLE `gallery_media` ADD `image_data` LONGBLOB NULL AFTER `type`");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gallery_media');
    }
};
