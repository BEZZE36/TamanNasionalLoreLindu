<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleLike;
use App\Models\ArticleFavorite;
use Illuminate\Http\Request;

class ArticleInteractionController extends Controller
{
    /**
     * Toggle like on an article
     */
    public function toggleLike(Article $article)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Login diperlukan untuk menyukai artikel'
            ], 401);
        }

        $userId = auth()->id();
        $existing = ArticleLike::where('article_id', $article->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $article->decrement('likes_count');
            $isLiked = false;
        } else {
            ArticleLike::create([
                'article_id' => $article->id,
                'user_id' => $userId,
            ]);
            $article->increment('likes_count');
            $isLiked = true;
        }

        return response()->json([
            'success' => true,
            'is_liked' => $isLiked,
            'likes_count' => $article->fresh()->likes_count,
            'message' => $isLiked ? 'Artikel disukai' : 'Like dibatalkan'
        ]);
    }

    /**
     * Toggle favorite on an article
     */
    public function toggleFavorite(Article $article)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Login diperlukan untuk menyimpan artikel'
            ], 401);
        }

        $userId = auth()->id();
        $existing = ArticleFavorite::where('article_id', $article->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorited = false;
        } else {
            ArticleFavorite::create([
                'article_id' => $article->id,
                'user_id' => $userId,
            ]);
            $isFavorited = true;
        }

        return response()->json([
            'success' => true,
            'is_favorited' => $isFavorited,
            'message' => $isFavorited ? 'Artikel disimpan ke favorit' : 'Artikel dihapus dari favorit'
        ]);
    }

    /**
     * Get user's interaction status with an article
     */
    public function status(Article $article)
    {
        if (!auth()->check()) {
            return response()->json([
                'is_liked' => false,
                'is_favorited' => false,
                'likes_count' => $article->likes_count,
            ]);
        }

        $userId = auth()->id();

        return response()->json([
            'is_liked' => ArticleLike::where('article_id', $article->id)->where('user_id', $userId)->exists(),
            'is_favorited' => ArticleFavorite::where('article_id', $article->id)->where('user_id', $userId)->exists(),
            'likes_count' => $article->likes_count,
        ]);
    }

    /**
     * Get user's favorite articles
     */
    public function favorites()
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Login diperlukan'], 401);
        }

        $favorites = ArticleFavorite::with([
            'article' => function ($q) {
                $q->select('id', 'title', 'slug', 'excerpt', 'featured_image', 'image_data', 'image_mime', 'category', 'published_at', 'views_count', 'likes_count');
            }
        ])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return response()->json($favorites);
    }

    /**
     * Get user's liked articles
     */
    public function likes()
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'message' => 'Login diperlukan'], 401);
        }

        $likes = ArticleLike::with([
            'article' => function ($q) {
                $q->select('id', 'title', 'slug', 'excerpt', 'featured_image', 'image_data', 'image_mime', 'category', 'published_at', 'views_count', 'likes_count');
            }
        ])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(12);

        return response()->json($likes);
    }
}
