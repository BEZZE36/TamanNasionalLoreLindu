<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\DestinationImage;
use App\Models\DestinationCategory;
use App\Services\Admin\DestinationService;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    use LogsActivity;

    protected DestinationService $destinationService;
    protected NotificationService $notificationService;
    protected PushNotificationService $pushNotificationService;

    public function __construct(
        DestinationService $destinationService,
        NotificationService $notificationService,
        PushNotificationService $pushNotificationService
    ) {
        $this->destinationService = $destinationService;
        $this->notificationService = $notificationService;
        $this->pushNotificationService = $pushNotificationService;
    }

    public function dashboard(Request $request)
    {
        $period = $request->get('period', 30);
        $startDate = now()->subDays($period);

        $stats = [
            'total' => Destination::count(),
            'published' => Destination::where('is_active', true)->count(),
            'draft' => Destination::where('is_active', false)->count(),
            'featured' => Destination::where('is_featured', true)->count(),
            'total_views' => Destination::sum('views_count'),
        ];

        $bookingStats = [
            'total_bookings' => \App\Models\Booking::where('created_at', '>=', $startDate)->count(),
            'total_visitors' => \App\Models\Booking::whereIn('status', ['paid', 'confirmed', 'used'])
                ->where('created_at', '>=', $startDate)->sum('total_visitors'),
            'revenue' => \App\Models\Booking::whereIn('status', ['paid', 'confirmed', 'used'])
                ->where('created_at', '>=', $startDate)->sum('total_amount'),
        ];

        // Generate views chart data - use visitor_count per booking per day
        $viewsChart = ['labels' => [], 'data' => []];
        for ($i = $period - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $viewsChart['labels'][] = $date->format('d M');
            $viewsChart['data'][] = \App\Models\Booking::whereIn('status', ['paid', 'confirmed', 'used'])
                ->whereDate('created_at', $date->toDateString())
                ->sum('total_visitors');
        }

        $topDestinations = Destination::orderBy('views_count', 'desc')
            ->take(10)
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'city' => $d->city,
                'views_count' => $d->views_count,
            ]);

        $recentDestinations = Destination::latest()
            ->take(8)
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'city' => $d->city,
                'cover_url' => $d->cover_url,
                'is_featured' => $d->is_featured,
            ]);

        return \Inertia\Inertia::render('Admin/Destinations/Dashboard', [
            'stats' => $stats,
            'bookingStats' => $bookingStats,
            'viewsChart' => $viewsChart,
            'topDestinations' => $topDestinations,
            'recentDestinations' => $recentDestinations,
            'period' => $period,
        ]);
    }

    public function index(Request $request)
    {
        $query = Destination::with(['images', 'prices', 'category']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $destinations = $query->latest()->paginate(10)->withQueryString();

        $filters = [
            'search' => $request->search,
            'status' => $request->status,
        ];

        return \Inertia\Inertia::render('Admin/Destinations/Index', compact('destinations', 'filters'));
    }

    public function show(Destination $destination)
    {
        $destination->load(['images', 'prices', 'category', 'bookings']);
        return \Inertia\Inertia::render('Admin/Destinations/Show', compact('destination'));
    }

    public function create()
    {
        $categories = DestinationCategory::all();
        return \Inertia\Inertia::render('Admin/Destinations/Create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        $destination = $this->destinationService->createDestination($validated, $request);

        $this->logCreate($destination, 'Destinasi', $destination->name);

        // Send notifications to all users
        if ($destination->is_active) {
            $url = route('destinations.show', $destination->slug);
            $this->notificationService->notifyNewDestination(
                $destination->name,
                $destination->short_description ?? $destination->description ?? '',
                $url
            );
            $this->pushNotificationService->notifyNewDestination(
                $destination->name,
                $destination->short_description ?? $destination->description ?? '',
                $url
            );
        }

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi berhasil ditambahkan.');
    }

    public function edit(Destination $destination)
    {
        $destination->load(['images', 'prices']);
        $categories = DestinationCategory::all();
        return \Inertia\Inertia::render('Admin/Destinations/Edit', compact('destination', 'categories'));
    }

    public function update(Request $request, Destination $destination)
    {
        $validated = $request->validate($this->validationRules());

        $oldValues = $destination->only(['name', 'description', 'address', 'is_active', 'is_featured']);

        $this->destinationService->updateDestination($destination, $validated, $request);

        $destination->refresh();
        $newValues = $destination->only(['name', 'description', 'address', 'is_active', 'is_featured']);

        $this->logUpdate($destination, 'Destinasi', $oldValues, $newValues, $destination->name);

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi berhasil diperbarui.');
    }

    public function destroy(Destination $destination)
    {
        $name = $destination->name;
        $this->logDelete($destination, 'Destinasi', $name);

        $destination->delete();

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi berhasil dihapus.');
    }

    public function deleteImage(DestinationImage $image)
    {
        $image->delete();
        return back()->with('success', 'Gambar berhasil dihapus.');
    }

    public function setPrimaryImage(DestinationImage $image)
    {
        $image->destination->images()->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return back()->with('success', 'Gambar utama berhasil diubah.');
    }

    public function toggleActive(Destination $destination)
    {
        $oldStatus = $destination->is_active;
        $destination->update(['is_active' => !$destination->is_active]);

        $this->logToggle($destination, 'Destinasi', 'is_active', $destination->is_active, $destination->name);

        return response()->json([
            'success' => true,
            'is_active' => $destination->is_active,
            'message' => $destination->is_active ? 'Destinasi diaktifkan' : 'Destinasi dinonaktifkan'
        ]);
    }

    public function toggleFeatured(Destination $destination)
    {
        $destination->update(['is_featured' => !$destination->is_featured]);

        $this->logToggle($destination, 'Destinasi (Unggulan)', 'is_featured', $destination->is_featured, $destination->name);

        return response()->json([
            'success' => true,
            'is_featured' => $destination->is_featured,
            'message' => $destination->is_featured ? 'Destinasi dijadikan unggulan' : 'Destinasi bukan unggulan'
        ]);
    }

    public function duplicate(Destination $destination)
    {
        $newDestination = $this->destinationService->duplicateDestination($destination);

        $this->logCreate($newDestination, 'Destinasi (Duplikat)', $newDestination->name);

        return response()->json([
            'success' => true,
            'message' => 'Destinasi berhasil diduplikasi',
            'redirect' => route('admin.destinations.edit', $newDestination)
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:destinations,id'
        ]);

        $count = count($request->ids);
        Destination::whereIn('id', $request->ids)->delete();

        $this->logBulk('delete', 'Destinasi', $count);

        return response()->json([
            'success' => true,
            'message' => $count . ' destinasi berhasil dihapus'
        ]);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:destinations,id'
        ]);

        foreach ($request->order as $index => $id) {
            Destination::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Urutan berhasil diperbarui'
        ]);
    }

    /**
     * Common validation rules
     */
    protected function validationRules(): array
    {
        return [
            'category_id' => 'nullable|exists:destination_categories,id',
            'name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'operating_hours' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'google_maps_embed' => 'nullable|string',
            'facilities' => 'nullable|array',
            'rules' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'rating' => 'nullable|numeric|min:0|max:5',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'prices' => 'nullable|array',
            'parking_fees' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'keywords' => 'nullable|string',
        ];
    }
}
