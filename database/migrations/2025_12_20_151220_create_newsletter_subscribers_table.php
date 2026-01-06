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
        if (!Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('name')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('subscribed_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index('is_active');
            });
        } else {
            // Table exists - ensure all required columns are present
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                if (!Schema::hasColumn('newsletter_subscribers', 'email')) {
                    $table->string('email')->unique()->after('id');
                }
                if (!Schema::hasColumn('newsletter_subscribers', 'name')) {
                    $table->string('name')->nullable()->after('email');
                }
                if (!Schema::hasColumn('newsletter_subscribers', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('name');
                }
                if (!Schema::hasColumn('newsletter_subscribers', 'subscribed_at')) {
                    $table->timestamp('subscribed_at')->nullable()->after('is_active');
                }
                if (!Schema::hasColumn('newsletter_subscribers', 'unsubscribed_at')) {
                    $table->timestamp('unsubscribed_at')->nullable()->after('subscribed_at');
                }
                if (!Schema::hasColumn('newsletter_subscribers', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable()->after('unsubscribed_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
