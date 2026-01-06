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

        $flora = $query->paginate(12)->withQueryString();
        $categories = Flora::CATEGORIES;
        $featuredFlora = Flora::active()->featured()->take(3)->get();

        // Collect current filters
        $filters = [
            'search' => $request->search,
            'category' => $request->category,
        ];

        return \Inertia\Inertia::render('Public/Flora/Index', compact('flora', 'categories', 'featuredFlora', 'filters'));
    }

    /**
     * Display individual Flora detail
     */
    public function show(Flora $flora)
    {
        if (!$flora->is_active) {
            abort(404);
        }

        $flora->load([
            'images',
            'comments' => fn($q) => $q->where('is_approved', true)->with('user')->orderBy('created_at', 'desc')
        ]);

        $flora->increment('view_count');

        // Get related flora
        $relatedFlora = Flora::active()
            ->where('category', $flora->category)
            ->where('id', '!=', $flora->id)
            ->take(3)
            ->get();

        return \Inertia\Inertia::render('Public/Flora/Show', compact('flora', 'relatedFlora'));
    }
}

