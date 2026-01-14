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
            ->orderByDesc('is_pinned') // Pinned articles first
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
            ->take(10)
            ->get();

        // Categories for filter (exclude 'berita' - it has its own page)
        $categories = collect(Article::CATEGORIES)->except('berita')->toArray();

        // Recent articles for sidebar (exclude news)
        $recentArticles = Article::published()
            ->where('category', '!=', 'berita')
            ->latest('published_at')
            ->take(5)
            ->get(['id', 'title', 'slug', 'featured_image', 'image_data', 'image_mime', 'published_at', 'updated_at']);

        $filters = [
            'search' => $request->search,
            'category' => $request->category,
        ];

        // Calculate stats for the hero section
        $stats = [
            'totalViews' => Article::published()->where('category', '!=', 'berita')->sum('views_count'),
            'totalComments' => \App\Models\ArticleComment::whereHas('article', function ($q) {
                $q->published()->where('category', '!=', 'berita');
            })->count(),
        ];

        return \Inertia\Inertia::render('Public/Blog/Index', compact('articles', 'featuredArticles', 'categories', 'recentArticles', 'filters', 'stats'));
    }

    /**
     * Display individual article
     */
    public function show($slug)
    {
        $article = Article::published()
            ->with(['tags'])
            ->where('slug', $slug)
            ->firstOrFail();
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

        // Comments (visible, root level, pinned first)
        $comments = \App\Models\ArticleComment::with(['user', 'admin', 'replies.user', 'replies.admin'])
            ->where('article_id', $article->id)
            ->root()
            ->visible()
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'content' => $c->content,
                'created_at' => $c->created_at,
                'is_pinned' => $c->is_pinned,
                'user' => $c->user ? ['id' => $c->user->id, 'name' => $c->user->name] : null,
                'admin' => $c->admin ? ['name' => $c->admin->name] : null,
                'replies' => $c->replies->filter(fn($r) => $r->is_visible)->map(fn($r) => [
                    'id' => $r->id,
                    'content' => $r->content,
                    'created_at' => $r->created_at,
                    'user' => $r->user ? ['id' => $r->user->id, 'name' => $r->user->name] : null,
                    'admin' => $r->admin ? ['name' => $r->admin->name] : null,
                ])->values(),
            ]);

        return \Inertia\Inertia::render('Public/Blog/Show', compact('article', 'relatedArticles', 'comments'));
    }
}
