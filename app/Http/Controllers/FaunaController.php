<?php

namespace App\Http\Controllers;

use App\Models\Fauna;
use Illuminate\Http\Request;

class FaunaController extends Controller
{
    /**
     * Display the Fauna index page
     */
    public function index(Request $request)
    {
        $query = Fauna::active()->ordered();

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('conservation_status')) {
            $query->where('conservation_status', $request->conservation_status);
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

        $fauna = $query->paginate(12)->withQueryString();
        $categories = Fauna::CATEGORIES;
        $conservationStatuses = Fauna::CONSERVATION_STATUSES;
        $featuredFauna = Fauna::active()->featured()->take(10)->get();

        $filters = [
            'search' => $request->search,
            'category' => $request->category,
            'conservation_status' => $request->conservation_status,
            'sort' => $request->sort ?? 'newest',
        ];

        // Calculate stats for hero
        $stats = [
            'totalFauna' => Fauna::active()->count(),
            'totalEndemic' => Fauna::active()->where('category', 'endemik')->count(),
            'totalEndangered' => Fauna::active()->whereNotNull('conservation_status')->count(),
            'totalViews' => Fauna::active()->sum('view_count'),
        ];

        return \Inertia\Inertia::render('Public/Fauna/Index', compact('fauna', 'categories', 'conservationStatuses', 'featuredFauna', 'filters', 'stats'));
    }

    /**
     * Display individual Fauna detail
     */
    public function show(Fauna $fauna)
    {
        if (!$fauna->is_active) {
            abort(404);
        }

        $fauna->load(['images']);
        $fauna->increment('view_count');

        // Load comments with replies and admin support
        $comments = $fauna->comments()
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

        $relatedFauna = Fauna::active()
            ->where('category', $fauna->category)
            ->where('id', '!=', $fauna->id)
            ->take(3)
            ->get();

        return \Inertia\Inertia::render('Public/Fauna/Show', compact('fauna', 'relatedFauna', 'comments'));
    }
}

