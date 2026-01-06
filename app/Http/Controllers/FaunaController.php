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

        if ($request->filled('status')) {
            $query->where('conservation_status', $request->status);
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

        $fauna = $query->paginate(12)->withQueryString();
        $categories = Fauna::CATEGORIES;
        $conservationStatuses = Fauna::CONSERVATION_STATUSES;
        $featuredFauna = Fauna::active()->featured()->take(3)->get();

        $filters = [
            'search' => $request->search,
            'category' => $request->category,
            'status' => $request->status,
        ];

        return \Inertia\Inertia::render('Public/Fauna/Index', compact('fauna', 'categories', 'conservationStatuses', 'featuredFauna', 'filters'));
    }

    /**
     * Display individual Fauna detail
     */
    public function show(Fauna $fauna)
    {
        if (!$fauna->is_active) {
            abort(404);
        }

        $fauna->load([
            'images',
            'comments' => fn($q) => $q->where('is_approved', true)->with('user')->orderBy('created_at', 'desc')
        ]);

        $fauna->increment('view_count');

        $relatedFauna = Fauna::active()
            ->where('category', $fauna->category)
            ->where('id', '!=', $fauna->id)
            ->take(3)
            ->get();

        return \Inertia\Inertia::render('Public/Fauna/Show', compact('fauna', 'relatedFauna'));
    }
}

