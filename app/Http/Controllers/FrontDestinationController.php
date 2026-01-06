<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FrontDestinationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Destination::active()->with(['images', 'prices', 'category']);

        // Category Filter
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search Filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Price Range Filter
        if ($request->filled('min_price')) {
            $query->whereHas('prices', function ($q) use ($request) {
                $q->where('price', '>=', (int) $request->min_price);
            });
        }
        if ($request->filled('max_price')) {
            $query->whereHas('prices', function ($q) use ($request) {
                $q->where('price', '<=', (int) $request->max_price);
            });
        }

        // Facilities Filter
        if ($request->filled('facilities') && is_array($request->facilities)) {
            foreach ($request->facilities as $facility) {
                $query->whereJsonContains('facilities', $facility);
            }
        }

        // Sorting
        $sort = $request->get('sort', 'popular');

        switch ($sort) {
            case 'newest':
                $query->latest();
                break;
            case 'rating':
                $query->orderByDesc('rating');
                break;
            case 'cheapest':
                // Subquery to get min price for sorting
                $query->withMin('prices', 'price')
                    ->orderBy('prices_min_price', 'asc');
                break;
            case 'expensive':
                $query->withMax('prices', 'price')
                    ->orderBy('prices_max_price', 'desc');
                break;
            case 'popular':
            default:
                $query->orderByDesc('is_featured')->orderByDesc('rating');
                break;
        }

        $destinations = $query->paginate(12)->withQueryString();

        // Transform destination data to handle null values
        $destinations->getCollection()->transform(function ($d) {
            return [
                'id' => $d->id,
                'name' => $d->name,
                'slug' => $d->slug,
                'city' => $d->city,
                'short_description' => $d->short_description,
                'rating' => $d->rating ?? 0,
                'review_count' => $d->review_count ?? 0,
                'is_featured' => $d->is_featured,
                'primary_image_url' => $d->primary_image_url,
                'adult_price' => $d->prices->first()?->price ?? 0,
                'category' => $d->category ? [
                    'id' => $d->category->id,
                    'name' => $d->category->name,
                    'slug' => $d->category->slug,
                ] : null,
            ];
        });

        $categories = \App\Models\DestinationCategory::withCount('destinations')->get();

        // Data for Filters
        $minPrice = (int) (\App\Models\DestinationPrice::min('price') ?? 0);
        $maxPrice = (int) (\App\Models\DestinationPrice::max('price') ?? 1000000);

        // Get all unique facilities
        $allFacilities = Destination::active()
            ->select('facilities')
            ->get()
            ->pluck('facilities')
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Collect current filters for the Vue component
        $filters = [
            'search' => $request->search,
            'category' => $request->category,
            'sort' => $request->sort ?? 'popular',
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'facilities' => $request->facilities,
        ];

        // Total successful (paid) bookings count
        $totalBookings = \App\Models\Booking::where('status', 'paid')->count();

        return \Inertia\Inertia::render('Public/Destinations/Index', compact(
            'destinations',
            'categories',
            'minPrice',
            'maxPrice',
            'allFacilities',
            'filters',
            'totalBookings'
        ));
    }

    /**
     * Display the specified destination.
     */
    public function show($slug)
    {
        $destination = Destination::where('slug', $slug)
            ->active()
            ->with([
                'images',
                'prices',
                'category',
                'reviews' => function ($q) {
                    $q->where('is_published', true)->latest();
                },
                'reviews.user',
                'reviews.publicReplies.admin'
            ])
            ->firstOrFail();

        // Increment view and visitor count
        $destination->increment('visitor_count');
        $destination->increment('views_count');

        // Fetch related destinations
        $relatedDestinations = Destination::where('category_id', $destination->category_id)
            ->where('id', '!=', $destination->id)
            ->active()
            ->with(['images', 'category', 'prices'])
            ->inRandomOrder()
            ->take(3)
            ->get();

        return \Inertia\Inertia::render('Public/Destinations/Show', compact('destination', 'relatedDestinations'));
    }

    /**
     * Store a newly created review in storage.
     */
    public function storeReview(Request $request, $slug)
    {
        $destination = Destination::where('slug', $slug)->active()->firstOrFail();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'required|string|max:1000',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Max 2MB per image
            'images' => 'nullable|array|max:5', // Max 5 images
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('reviews', 'public');
                $imagePaths[] = $path;
            }
        }

        Feedback::create([
            'user_id' => auth()->id(),
            'destination_id' => $destination->id,
            'display_name' => auth()->user()->name,
            'subject' => 'Ulasan ' . $destination->name,
            'message' => $validated['message'],
            'rating' => $validated['rating'],
            'is_published' => true, // Auto-publish for now
            'status' => 'read',
            'images' => !empty($imagePaths) ? $imagePaths : null,
        ]);

        return redirect()->route('destinations.show', $slug)->with('success', 'Ulasan berhasil dikirim!');
    }
}
