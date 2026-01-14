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
        // Add admin_id to flora_comments
        if (!Schema::hasColumn('flora_comments', 'admin_id')) {
            Schema::table('flora_comments', function (Blueprint $table) {
                $table->foreignId('admin_id')->nullable()->after('user_id')
                    ->constrained('admins')->onDelete('cascade');
                // Make user_id nullable (for admin comments)
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }

        // Add admin_id to fauna_comments
        if (!Schema::hasColumn('fauna_comments', 'admin_id')) {
            Schema::table('fauna_comments', function (Blueprint $table) {
                $table->foreignId('admin_id')->nullable()->after('user_id')
                    ->constrained('admins')->onDelete('cascade');
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }

        // Add admin_id to gallery_comments
        if (!Schema::hasColumn('gallery_comments', 'admin_id')) {
            Schema::table('gallery_comments', function (Blueprint $table) {
                $table->foreignId('admin_id')->nullable()->after('user_id')
                    ->constrained('admins')->onDelete('cascade');
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flora_comments', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('fauna_comments', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        Schema::table('gallery_comments', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });
    }
};
