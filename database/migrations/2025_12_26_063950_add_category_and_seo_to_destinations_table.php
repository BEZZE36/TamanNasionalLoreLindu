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
        Schema::table('destinations', function (Blueprint $table) {
            if (!Schema::hasColumn('destinations', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('name')->constrained('destination_categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('destinations', 'short_description')) {
                $table->text('short_description')->nullable()->after('description');
            }
            if (!Schema::hasColumn('destinations', 'meta_title')) {
                $table->string('meta_title')->nullable()->after('rules');
            }
            if (!Schema::hasColumn('destinations', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('destinations', 'keywords')) {
                $table->string('keywords')->nullable()->after('meta_description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('destinations', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id', 'short_description', 'meta_title', 'meta_description', 'keywords']);
        });
    }
};
