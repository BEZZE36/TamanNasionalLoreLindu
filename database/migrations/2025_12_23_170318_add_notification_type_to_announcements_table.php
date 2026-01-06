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
        if (Schema::hasColumn('announcements', 'notification_type')) {
            return;
        }
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('notification_type')->default('info')->after('type'); // info, success, warning, danger
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('notification_type');
        });
    }
};
