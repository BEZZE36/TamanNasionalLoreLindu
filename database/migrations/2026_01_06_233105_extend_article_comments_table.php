<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Update article_comments table
        Schema::table('article_comments', function (Blueprint $table) {
            $table->unsignedInteger('likes_count')->default(0)->after('is_pinned');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('likes_count');
            $table->boolean('is_reported')->default(false)->after('status');
            $table->string('report_reason')->nullable()->after('is_reported');
        });

        // Comment likes
        Schema::create('article_comment_likes', function (Blueprint $table) {
            $table->foreignId('comment_id')->references('id')->on('article_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_comment_likes');

        Schema::table('article_comments', function (Blueprint $table) {
            $table->dropColumn(['likes_count', 'status', 'is_reported', 'report_reason']);
        });
    }
};
