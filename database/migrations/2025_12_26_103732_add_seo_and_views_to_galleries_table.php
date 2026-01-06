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
        Schema::table('galleries', function (Blueprint $table) {
            // SEO fields
            if (!Schema::hasColumn('galleries', 'meta_title')) {
                $table->string('meta_title', 70)->nullable()->after('description');
            }
            if (!Schema::hasColumn('galleries', 'meta_description')) {
                $table->string('meta_description', 170)->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('galleries', 'meta_keywords')) {
                $table->string('meta_keywords', 255)->nullable()->after('meta_description');
            }

            // View analytics
            if (!Schema::hasColumn('galleries', 'view_count')) {
                $table->unsignedInteger('view_count')->default(0)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('galleries', 'meta_title'))
                $columns[] = 'meta_title';
            if (Schema::hasColumn('galleries', 'meta_description'))
                $columns[] = 'meta_description';
            if (Schema::hasColumn('galleries', 'meta_keywords'))
                $columns[] = 'meta_keywords';
            if (Schema::hasColumn('galleries', 'view_count'))
                $columns[] = 'view_count';

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
