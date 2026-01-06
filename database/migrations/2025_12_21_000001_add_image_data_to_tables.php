<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Add image_data (LONGBLOB) and image_mime columns to store images in database
     */
    public function up(): void
    {
        // DestinationImages table
        Schema::table('destination_images', function (Blueprint $table) {
            if (!Schema::hasColumn('destination_images', 'image_data')) {
                $table->longText('image_data')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('destination_images', 'image_mime')) {
                $table->string('image_mime', 50)->nullable()->after('image_data');
            }
        });

        // Flora table
        Schema::table('flora', function (Blueprint $table) {
            if (!Schema::hasColumn('flora', 'image_data')) {
                $table->longText('image_data')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('flora', 'image_mime')) {
                $table->string('image_mime', 50)->nullable()->after('image_data');
            }
        });

        // Fauna table
        Schema::table('fauna', function (Blueprint $table) {
            if (!Schema::hasColumn('fauna', 'image_data')) {
                $table->longText('image_data')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('fauna', 'image_mime')) {
                $table->string('image_mime', 50)->nullable()->after('image_data');
            }
        });

        // Galleries table
        Schema::table('galleries', function (Blueprint $table) {
            if (!Schema::hasColumn('galleries', 'image_data')) {
                $table->longText('image_data')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('galleries', 'image_mime')) {
                $table->string('image_mime', 50)->nullable()->after('image_data');
            }
        });

        // Banners table
        Schema::table('banners', function (Blueprint $table) {
            if (!Schema::hasColumn('banners', 'image_data')) {
                $table->longText('image_data')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('banners', 'image_mime')) {
                $table->string('image_mime', 50)->nullable()->after('image_data');
            }
            if (!Schema::hasColumn('banners', 'mobile_image_data')) {
                $table->longText('mobile_image_data')->nullable()->after('mobile_image_path');
            }
            if (!Schema::hasColumn('banners', 'mobile_image_mime')) {
                $table->string('mobile_image_mime', 50)->nullable()->after('mobile_image_data');
            }
        });

        // Create uploads table for TinyMCE images
        if (!Schema::hasTable('uploads')) {
            Schema::create('uploads', function (Blueprint $table) {
                $table->id();
                $table->string('hash', 64)->unique();
                $table->string('filename');
                $table->longText('image_data');
                $table->string('image_mime', 50);
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();

                $table->index('hash');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uploads');

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime', 'mobile_image_data', 'mobile_image_mime']);
        });

        Schema::table('galleries', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime']);
        });

        Schema::table('fauna', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime']);
        });

        Schema::table('flora', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime']);
        });

        Schema::table('destination_images', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime']);
        });
    }
};
