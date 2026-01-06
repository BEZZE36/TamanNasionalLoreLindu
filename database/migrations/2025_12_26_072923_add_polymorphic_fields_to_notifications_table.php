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
        Schema::table('notifications', function (Blueprint $table) {
            // Drop foreign key if it exists to allow nullable change or removal
            // $table->dropForeign(['user_id']); // Commented out in case it doesn't exist or uses default name

            // Add polymorphic columns
            $table->string('notifiable_type')->nullable()->after('user_id');
            $table->unsignedBigInteger('notifiable_id')->nullable()->after('notifiable_type');

            // Make user_id nullable since we might use notifiable_id instead
            $table->foreignId('user_id')->nullable()->change();

            // Add index
            $table->index(['notifiable_type', 'notifiable_id']);
        });

        // Migrate existing data
        DB::statement("UPDATE notifications SET notifiable_type = 'App\\\\Models\\\\User', notifiable_id = user_id WHERE user_id IS NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_type', 'notifiable_id']);
            $table->dropColumn(['notifiable_type', 'notifiable_id']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
