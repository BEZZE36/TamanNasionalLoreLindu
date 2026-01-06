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
            // Analytics
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->unsignedInteger('dismiss_count')->default(0);

            // Target Audience
            $table->enum('target_audience', ['all', 'guest', 'authenticated', 'first_visit'])->default('all');

            // Page Targeting
            $table->json('target_pages')->nullable(); // e.g., ['home', 'destinations', 'gallery']
            $table->json('exclude_pages')->nullable();

            // Display Position (for banners)
            $table->enum('position', ['top', 'bottom', 'floating'])->default('top');

            // Advanced Scheduling
            $table->json('show_days')->nullable(); // e.g., [1,2,3,4,5] for Mon-Fri
            $table->time('show_time_start')->nullable();
            $table->time('show_time_end')->nullable();

            // Animation
            $table->string('animation_type', 50)->default('fade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn([
                'view_count',
                'click_count',
                'dismiss_count',
                'target_audience',
                'target_pages',
                'exclude_pages',
                'position',
                'show_days',
                'show_time_start',
                'show_time_end',
                'animation_type',
            ]);
        });
    }
};
