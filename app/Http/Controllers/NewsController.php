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
            ->orderByDesc('is_pinned') // Pinned news first
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

        // Archive filter (format: YYYY-MM)
        if ($request->filled('archive')) {
            $parts = explode('-', $request->archive);
            if (count($parts) === 2) {
                $query->whereYear('published_at', $parts[0])
                    ->whereMonth('published_at', $parts[1]);
            }
        }

        $news = $query->paginate(9)->withQueryString();

        $featuredNews = Article::published()
            ->where('category', 'berita')
            ->featured()
            ->latest('published_at')
            ->take(10)
            ->get();

        // Recent news for sidebar
        $recentNews = Article::published()
            ->where('category', 'berita')
            ->latest('published_at')
            ->take(5)
            ->get(['id', 'title', 'slug', 'featured_image', 'image_data', 'image_mime', 'published_at', 'updated_at']);

        // Format archives with month names
        $archives = Article::published()
            ->where('category', 'berita')
            ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->take(6)
            ->get()
            ->filter(fn($item) => $item->month >= 1 && $item->month <= 12)
            ->map(function ($item) {
                $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
                return [
                    'value' => $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                    'month_name' => $monthNames[$item->month - 1] ?? 'Unknown',
                    'year' => $item->year,
                    'count' => $item->count,
                ];
            })->values();

        $filters = [
            'search' => $request->search,
            'archive' => $request->archive,
        ];

        // Calculate stats for the hero section
        $stats = [
            'totalViews' => Article::published()->where('category', 'berita')->sum('views_count'),
            'totalComments' => \App\Models\ArticleComment::whereHas('article', function ($q) {
                $q->published()->where('category', 'berita');
            })->count(),
        ];

        return \Inertia\Inertia::render('Public/News/Index', compact('news', 'featuredNews', 'recentNews', 'archives', 'filters', 'stats'));
    }

    /**
     * Display individual news article
     */
    public function show($slug)
    {
        $article = Article::published()
            ->with(['tags'])
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

        return \Inertia\Inertia::render('Public/News/Show', compact('article', 'relatedNews', 'comments'));
    }
}

