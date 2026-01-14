<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add is_pinned to flora_comments
        if (!Schema::hasColumn('flora_comments', 'is_pinned')) {
            Schema::table('flora_comments', function (Blueprint $table) {
                $table->boolean('is_pinned')->default(false)->after('is_approved');
            });
        }

        // Add is_pinned to fauna_comments
        if (!Schema::hasColumn('fauna_comments', 'is_pinned')) {
            Schema::table('fauna_comments', function (Blueprint $table) {
                $table->boolean('is_pinned')->default(false)->after('is_approved');
            });
        }

        // Add is_pinned to gallery_comments
        if (!Schema::hasColumn('gallery_comments', 'is_pinned')) {
            Schema::table('gallery_comments', function (Blueprint $table) {
                $table->boolean('is_pinned')->default(false)->after('is_approved');
            });
        }
    }

    public function down(): void
    {
        Schema::table('flora_comments', function (Blueprint $table) {
            $table->dropColumn('is_pinned');
        });

        Schema::table('fauna_comments', function (Blueprint $table) {
            $table->dropColumn('is_pinned');
        });

        Schema::table('gallery_comments', function (Blueprint $table) {
            $table->dropColumn('is_pinned');
        });
    }
};
