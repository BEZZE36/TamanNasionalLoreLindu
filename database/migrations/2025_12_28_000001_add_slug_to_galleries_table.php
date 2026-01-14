<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            if (!Schema::hasColumn('galleries', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
        });

        // Generate slugs for existing galleries
        $galleries = \App\Models\Gallery::whereNull('slug')->get();
        foreach ($galleries as $gallery) {
            $slug = Str::slug($gallery->title);
            $originalSlug = $slug;
            $counter = 1;

            while (\App\Models\Gallery::where('slug', $slug)->where('id', '!=', $gallery->id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $gallery->update(['slug' => $slug]);
        }

        // Make slug unique after populating
        Schema::table('galleries', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
