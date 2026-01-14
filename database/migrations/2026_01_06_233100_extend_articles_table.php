<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Stats - views_count already exists
            if (!Schema::hasColumn('articles', 'likes_count')) {
                $table->unsignedBigInteger('likes_count')->default(0)->after('views_count');
            }
            if (!Schema::hasColumn('articles', 'reading_time')) {
                $table->unsignedInteger('reading_time')->default(0)->after('likes_count');
            }

            // Scheduling & Status
            if (!Schema::hasColumn('articles', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('published_at');
            }
            if (!Schema::hasColumn('articles', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('scheduled_at');
            }
            if (!Schema::hasColumn('articles', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('is_featured');
            }

            // SEO - meta_title & meta_description already exist
            if (!Schema::hasColumn('articles', 'meta_keywords')) {
                $table->string('meta_keywords')->nullable()->after('meta_description');
            }

            // Revisions
            if (!Schema::hasColumn('articles', 'revision_count')) {
                $table->unsignedInteger('revision_count')->default(0)->after('content');
            }
            if (!Schema::hasColumn('articles', 'last_auto_save')) {
                $table->timestamp('last_auto_save')->nullable()->after('revision_count');
            }

            // Multi-language
            if (!Schema::hasColumn('articles', 'locale')) {
                $table->string('locale')->default('id')->after('category');
            }
            if (!Schema::hasColumn('articles', 'translation_of')) {
                $table->foreignId('translation_of')->nullable()->after('locale');
            }
        });

        // Add foreign key for translation_of if column was just added
        if (Schema::hasColumn('articles', 'translation_of')) {
            try {
                Schema::table('articles', function (Blueprint $table) {
                    $table->foreign('translation_of')->references('id')->on('articles')->nullOnDelete();
                });
            } catch (\Exception $e) {
                // Foreign key might already exist
            }
        }
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Drop foreign key first
            try {
                $table->dropForeign(['translation_of']);
            } catch (\Exception $e) {
            }

            $columns = [
                'likes_count',
                'reading_time',
                'scheduled_at',
                'archived_at',
                'is_pinned',
                'meta_keywords',
                'revision_count',
                'last_auto_save',
                'locale',
                'translation_of'
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('articles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
