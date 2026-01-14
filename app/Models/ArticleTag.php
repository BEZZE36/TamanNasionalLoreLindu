<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ArticleTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'articles_count',
    ];

    protected $casts = [
        'articles_count' => 'integer',
    ];

    // Relationships
    public function articles()
    {
        return $this->belongsToMany(Article::class, 'article_tag', 'tag_id', 'article_id');
    }

    // Boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Update count
    public function updateArticlesCount(): void
    {
        $this->update(['articles_count' => $this->articles()->count()]);
    }
}
