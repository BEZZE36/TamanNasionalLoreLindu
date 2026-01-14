<?php

namespace App\Http\Controllers;

use App\Models\Flora;
use Illuminate\Http\Request;

class FloraController extends Controller
{
    /**
     * Display the Flora index page
     */
    public function index(Request $request)
    {
        $query = Flora::active()->ordered();

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('local_name', 'like', "%{$search}%")
                    ->orWhere('scientific_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Handle sort
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'alphabetical' => $query->orderBy('name', 'asc'),
            'popular' => $query->orderBy('view_count', 'desc'),
            default => null, // Already ordered by default
        };

        $flora = $query->paginate(12)->withQueryString();
        $categories = Flora::CATEGORIES;
        $featuredFlora = Flora::active()->featured()->take(10)->get();

        // Collect current filters
        $filters = [
            'search' => $request->search,
            'category' => $request->category,
            'sort' => $request->sort ?? 'newest',
        ];

        // Calculate stats for hero
        $stats = [
            'totalFlora' => Flora::active()->count(),
            'totalEndemik' => Flora::active()->where('category', 'endemik')->count(),
            'totalLangka' => Flora::active()->where('category', 'langka')->count(),
            'totalViews' => Flora::active()->sum('view_count'),
        ];

        return \Inertia\Inertia::render('Public/Flora/Index', compact('flora', 'categories', 'featuredFlora', 'filters', 'stats'));
    }

    /**
     * Display individual Flora detail
     */
    public function show(Flora $flora)
    {
        if (!$flora->is_active) {
            abort(404);
        }

        $flora->load(['images']);
        $flora->increment('view_count');

        // Load comments with replies and admin support
        $comments = $flora->comments()
            ->whereNull('parent_id')
            ->where('is_approved', true)
            ->with([
                'user',
                'admin',
                'replies' => function ($q) {
                    $q->where('is_approved', true)->with(['user', 'admin'])->orderBy('created_at', 'asc');
                }
            ])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        // Get related flora
        $relatedFlora = Flora::active()
            ->where('category', $flora->category)
            ->where('id', '!=', $flora->id)
            ->take(3)
            ->get();

        return \Inertia\Inertia::render('Public/Flora/Show', compact('flora', 'relatedFlora', 'comments'));
    }
}

