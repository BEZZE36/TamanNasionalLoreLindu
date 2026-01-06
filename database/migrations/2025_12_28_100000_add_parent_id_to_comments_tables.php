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
        // Add parent_id to flora_comments
        if (!Schema::hasColumn('flora_comments', 'parent_id')) {
            Schema::table('flora_comments', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->after('is_approved')
                    ->constrained('flora_comments')->onDelete('cascade');
                $table->index('parent_id');
            });
        }

        // Add parent_id to fauna_comments
        if (!Schema::hasColumn('fauna_comments', 'parent_id')) {
            Schema::table('fauna_comments', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->after('is_approved')
                    ->constrained('fauna_comments')->onDelete('cascade');
                $table->index('parent_id');
            });
        }

        // Add parent_id to gallery_comments
        if (!Schema::hasColumn('gallery_comments', 'parent_id')) {
            Schema::table('gallery_comments', function (Blueprint $table) {
                $table->foreignId('parent_id')->nullable()->after('is_approved')
                    ->constrained('gallery_comments')->onDelete('cascade');
                $table->index('parent_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flora_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });

        Schema::table('fauna_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });

        Schema::table('gallery_comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
