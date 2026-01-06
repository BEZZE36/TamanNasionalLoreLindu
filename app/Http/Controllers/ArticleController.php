<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display the blog index page
     */
    public function index(Request $request)
    {
        // Exclude 'berita' (news) category from blog - news has its own page
        $query = Article::published()
            ->where('category', '!=', 'berita')
            ->latest('published_at');

        // Filter by category
        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $articles = $query->paginate(9)->withQueryString();

        // Featured articles (exclude news)
        $featuredArticles = Article::published()
            ->where('category', '!=', 'berita')
            ->featured()
            ->latest('published_at')
            ->take(3)
            ->get();

        // Categories for filter (exclude 'berita' - it has its own page)
        $categories = collect(Article::CATEGORIES)->except('berita')->toArray();

        // Recent articles for sidebar (exclude news)
        $recentArticles = Article::published()
            ->where('category', '!=', 'berita')
            ->latest('published_at')
            ->take(5)
            ->get(['id', 'title', 'slug', 'featured_image', 'published_at']);

        $filters = [
            'search' => $request->search,
            'category' => $request->category,
        ];

        return \Inertia\Inertia::render('Public/Blog/Index', compact('articles', 'featuredArticles', 'categories', 'recentArticles', 'filters'));
    }

    /**
     * Display individual article
     */
    public function show($slug)
    {
        $article = Article::published()->where('slug', $slug)->firstOrFail();
        $article->incrementViews();

        // Related articles (same category)
        $relatedArticles = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category', $article->category)
            ->latest('published_at')
            ->take(3)
            ->get();

        // If not enough related, fill with recent
        if ($relatedArticles->count() < 3) {
            $moreArticles = Article::published()
                ->where('id', '!=', $article->id)
                ->whereNotIn('id', $relatedArticles->pluck('id'))
                ->latest('published_at')
                ->take(3 - $relatedArticles->count())
                ->get();
            $relatedArticles = $relatedArticles->merge($moreArticles);
        }

        return \Inertia\Inertia::render('Public/Blog/Show', compact('article', 'relatedArticles'));
    }
}

