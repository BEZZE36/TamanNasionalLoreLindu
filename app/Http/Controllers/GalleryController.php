<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\GalleryCategory;
use App\Models\Destination;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Display the Gallery page
     */
    public function index(Request $request)
    {
        $query = Gallery::active()->with(['destination', 'category', 'media']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $request->type === 'image' ? $query->images() : ($request->type === 'video' ? $query->videos() : null);
        }

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }

        if ($request->filled('destination')) {
            $query->where('destination_id', $request->destination);
        }

        if ($request->filled('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'popular' => $query->popular(),
            'alphabetical' => $query->alphabetical(),
            default => $query->ordered(),
        };

        $galleries = $query->paginate(16)->withQueryString();
        $categories = GalleryCategory::where('is_active', true)->orderBy('name')->get();
        $destinations = Destination::active()->orderBy('name')->get();

        $filters = [
            'search' => $request->search,
            'type' => $request->type,
            'category' => $request->category,
            'destination' => $request->destination,
            'sort' => $request->sort ?? 'newest',
        ];

        return \Inertia\Inertia::render('Public/Gallery/Index', compact('galleries', 'categories', 'destinations', 'filters'));
    }

    /**
     * Display individual Gallery detail page
     */
    public function show(string $slug)
    {
        $gallery = Gallery::where('slug', $slug)
            ->active()
            ->with(['destination', 'category', 'media'])
            ->with(['comments' => fn($q) => $q->where('is_approved', true)->with('user')->orderBy('created_at', 'desc')])
            ->withCount('likes')
            ->firstOrFail();

        $gallery->incrementViewCount();

        $related = Gallery::active()
            ->where('id', '!=', $gallery->id)
            ->where(
                fn($q) => $q
                    ->when($gallery->gallery_category_id, fn($q) => $q->where('gallery_category_id', $gallery->gallery_category_id))
                    ->when($gallery->destination_id, fn($q) => $q->orWhere('destination_id', $gallery->destination_id))
            )
            ->with(['destination', 'category'])
            ->withCount('likes')
            ->take(6)
            ->get();

        $isLiked = auth()->check() ? $gallery->isLikedBy(auth()->user()) : false;

        return \Inertia\Inertia::render('Public/Gallery/Show', compact('gallery', 'related', 'isLiked'));
    }

    /**
     * Track gallery view count (AJAX)
     */
    public function trackView(int $id)
    {
        $gallery = Gallery::find($id);

        if (!$gallery) {
            return response()->json(['success' => false, 'message' => 'Gallery not found'], 404);
        }

        $gallery->incrementViewCount();

        return response()->json(['success' => true, 'views' => $gallery->view_count]);
    }
}
