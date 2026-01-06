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
        Schema::table('announcements', function (Blueprint $table) {
            // Image
            $table->string('image_path')->nullable()->after('animation_type');

            // Countdown Timer
            $table->boolean('show_countdown')->default(false)->after('image_path');
            $table->string('countdown_label', 100)->nullable()->after('show_countdown');

            // Multiple buttons (JSON array)
            $table->json('extra_buttons')->nullable()->after('countdown_label');

            // History tracking
            $table->text('history_log')->nullable()->after('extra_buttons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn([
                'image_path',
                'show_countdown',
                'countdown_label',
                'extra_buttons',
                'history_log',
            ]);
        });
    }
};
