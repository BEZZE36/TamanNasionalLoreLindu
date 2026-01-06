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
        if (Schema::hasColumn('newsletter_subscribers', 'disabled_by_admin')) {
            return;
        }
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->boolean('disabled_by_admin')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn('disabled_by_admin');
        });
    }
};
