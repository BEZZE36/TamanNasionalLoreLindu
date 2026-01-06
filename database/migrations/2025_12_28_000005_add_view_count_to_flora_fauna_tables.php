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
        Schema::table('flora', function (Blueprint $table) {
            if (!Schema::hasColumn('flora', 'view_count')) {
                $table->unsignedBigInteger('view_count')->default(0)->after('is_active');
            }
        });

        Schema::table('fauna', function (Blueprint $table) {
            if (!Schema::hasColumn('fauna', 'view_count')) {
                $table->unsignedBigInteger('view_count')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flora', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });

        Schema::table('fauna', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }
};
