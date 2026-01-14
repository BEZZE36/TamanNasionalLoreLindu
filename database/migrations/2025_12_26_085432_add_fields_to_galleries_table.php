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
        Schema::table('galleries', function (Blueprint $table) {
            $table->json('tags')->nullable()->after('description');
            $table->date('capture_date')->nullable()->after('tags');
            $table->foreignId('gallery_category_id')->nullable()->after('category')->constrained('gallery_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropForeign(['gallery_category_id']);
            $table->dropColumn(['gallery_category_id', 'tags', 'capture_date']);
        });
    }
};
