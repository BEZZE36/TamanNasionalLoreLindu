<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    /**
     * Display the news index page
     */
    public function index(Request $request)
    {
        $query = Article::published()
            ->where('category', 'berita')
            ->latest('published_at');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $news = $query->paginate(9)->withQueryString();

        $featuredNews = Article::published()
            ->where('category', 'berita')
            ->featured()
            ->latest('published_at')
            ->take(3)
            ->get();

        // Recent news for sidebar
        $recentNews = Article::published()
            ->where('category', 'berita')
            ->latest('published_at')
            ->take(5)
            ->get(['id', 'title', 'slug', 'featured_image', 'image_data', 'image_mime', 'published_at']);

        // Archives - get months with news
        $archives = Article::published()
            ->where('category', 'berita')
            ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->take(6)
            ->get();

        $filters = [
            'search' => $request->search,
        ];

        return \Inertia\Inertia::render('Public/News/Index', compact('news', 'featuredNews', 'recentNews', 'archives', 'filters'));
    }

    /**
     * Display individual news article
     */
    public function show($slug)
    {
        $article = Article::published()
            ->where('category', 'berita')
            ->where('slug', $slug)
            ->firstOrFail();

        $article->incrementViews();

        // Related news (same category)
        $relatedNews = Article::published()
            ->where('id', '!=', $article->id)
            ->where('category', 'berita')
            ->latest('published_at')
            ->take(3)
            ->get();

        return \Inertia\Inertia::render('Public/News/Show', compact('article', 'relatedNews'));
    }
}

