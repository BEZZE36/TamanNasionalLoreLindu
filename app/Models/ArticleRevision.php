<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticleRevision extends Model
{
    protected $fillable = [
        'article_id',
        'admin_id',
        'title',
        'excerpt',
        'content',
        'meta',
        'change_summary',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Restore this revision to the article
     */
    public function restoreToArticle(): void
    {
        $this->article->update([
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            // SEO fields
            'meta_title' => $this->meta['meta_title'] ?? null,
            'meta_description' => $this->meta['meta_description'] ?? null,
            'meta_keywords' => $this->meta['meta_keywords'] ?? null,
            // Additional fields (with safe fallbacks for older revisions)
            'author_name' => $this->meta['author_name'] ?? $this->article->author_name,
            'category' => $this->meta['category'] ?? $this->article->category,
            'is_featured' => $this->meta['is_featured'] ?? false,
            'is_published' => $this->meta['is_published'] ?? false,
            'is_pinned' => $this->meta['is_pinned'] ?? false,
            'scheduled_at' => isset($this->meta['scheduled_at']) ? \Carbon\Carbon::parse($this->meta['scheduled_at']) : null,
            'locale' => $this->meta['locale'] ?? 'id',
        ]);
    }
}
