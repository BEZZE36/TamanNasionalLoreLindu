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
        // Alter Flora Table (only if columns don't exist)
        if (!Schema::hasColumn('flora', 'slug')) {
            Schema::table('flora', function (Blueprint $table) {
                $table->string('slug')->unique()->nullable()->after('name');
                $table->string('conservation_status')->nullable()->after('category'); // Adding IUCN status to Flora
                $table->string('best_time_to_visit')->nullable()->after('description');
                $table->string('meta_title')->nullable()->after('sort_order');
                $table->text('meta_description')->nullable()->after('meta_title');
            });
        }

        // Alter Fauna Table (only if columns don't exist)
        if (!Schema::hasColumn('fauna', 'slug')) {
            Schema::table('fauna', function (Blueprint $table) {
                $table->string('slug')->unique()->nullable()->after('name');
                $table->string('best_time_to_visit')->nullable()->after('description');
                $table->string('meta_title')->nullable()->after('sort_order');
                $table->text('meta_description')->nullable()->after('meta_title');
            });
        }

        // Create Flora Images Table (Gallery)
        if (!Schema::hasTable('flora_images')) {
            Schema::create('flora_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('flora_id')->constrained('flora')->onDelete('cascade');
                $table->binary('image_data')->nullable(); // LONGBLOB handled by driver usually, or use DB specific
                $table->string('image_mime')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
            // Helper specifically for MariaDB/MySQL to ensure LONGBLOB if using binary isn't enough (Laravel 'binary' is BLOB, usually need LONGBLOB for images)
            DB::statement("ALTER TABLE flora_images MODIFY image_data LONGBLOB");
        }


        // Create Fauna Images Table (Gallery)
        if (!Schema::hasTable('fauna_images')) {
            Schema::create('fauna_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fauna_id')->constrained('fauna')->onDelete('cascade');
                $table->binary('image_data')->nullable();
                $table->string('image_mime')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
            DB::statement("ALTER TABLE fauna_images MODIFY image_data LONGBLOB");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fauna_images');
        Schema::dropIfExists('flora_images');

        Schema::table('fauna', function (Blueprint $table) {
            $table->dropColumn(['slug', 'best_time_to_visit', 'meta_title', 'meta_description']);
        });

        Schema::table('flora', function (Blueprint $table) {
            $table->dropColumn(['slug', 'conservation_status', 'best_time_to_visit', 'meta_title', 'meta_description']);
        });
    }
};
