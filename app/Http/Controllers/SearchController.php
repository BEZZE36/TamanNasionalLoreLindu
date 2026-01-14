<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Flora;
use App\Models\Fauna;
use App\Models\Gallery;
use App\Models\SearchHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    /**
     * Global search API endpoint - Optimized for performance
     */
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        // Cache key based on query
        $cacheKey = 'search_' . md5(strtolower($query));

        // Try to get from cache first (cache for 2 minutes)
        $results = Cache::remember($cacheKey, 120, function () use ($query) {
            return $this->performSearch($query);
        });

        return response()->json([
            'results' => $results,
            'query' => $query
        ]);
    }

    /**
     * Perform the actual search with optimized queries
     */
    private function performSearch(string $query): array
    {
        $results = [];
        $searchTerm = "%{$query}%";

        // Search Destinations - Only select needed columns
        try {
            $destinations = Destination::query()
                ->select(['id', 'name', 'slug', 'description', 'city'])
                ->where('is_active', true)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('city', 'like', $searchTerm);
                })
                ->limit(4)
                ->get();

            foreach ($destinations as $item) {
                $results[] = [
                    'id' => 'dest_' . $item->id,
                    'type' => 'destination',
                    'type_label' => 'Destinasi',
                    'name' => $item->name,
                    'description' => Str::limit(strip_tags($item->description), 50),
                    'url' => route('destinations.show', $item->slug),
                    'image' => url('/images/destination/' . $item->id),
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Search destinations error: ' . $e->getMessage());
        }

        // Search Flora - Only select needed columns
        try {
            $flora = Flora::query()
                ->select(['id', 'name', 'slug', 'scientific_name', 'description'])
                ->where('is_active', true)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('scientific_name', 'like', $searchTerm);
                })
                ->limit(4)
                ->get();

            foreach ($flora as $item) {
                $results[] = [
                    'id' => 'flora_' . $item->id,
                    'type' => 'flora',
                    'type_label' => 'Flora',
                    'name' => $item->name,
                    'description' => $item->scientific_name ?? Str::limit(strip_tags($item->description), 50),
                    'url' => route('flora.show', $item->slug),
                    'image' => url('/images/flora/' . $item->id),
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Search flora error: ' . $e->getMessage());
        }

        // Search Fauna - Only select needed columns
        try {
            $fauna = Fauna::query()
                ->select(['id', 'name', 'slug', 'scientific_name', 'description'])
                ->where('is_active', true)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                        ->orWhere('scientific_name', 'like', $searchTerm);
                })
                ->limit(4)
                ->get();

            foreach ($fauna as $item) {
                $results[] = [
                    'id' => 'fauna_' . $item->id,
                    'type' => 'fauna',
                    'type_label' => 'Fauna',
                    'name' => $item->name,
                    'description' => $item->scientific_name ?? Str::limit(strip_tags($item->description), 50),
                    'url' => route('fauna.show', $item->slug),
                    'image' => url('/images/fauna/' . $item->id),
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Search fauna error: ' . $e->getMessage());
        }

        // Search Gallery - Only select needed columns
        try {
            $galleries = Gallery::query()
                ->select(['id', 'title', 'slug', 'description', 'type'])
                ->where('is_active', true)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'like', $searchTerm);
                })
                ->limit(3)
                ->get();

            foreach ($galleries as $item) {
                $results[] = [
                    'id' => 'gallery_' . $item->id,
                    'type' => 'gallery',
                    'type_label' => 'Galeri',
                    'name' => $item->title,
                    'description' => Str::limit(strip_tags($item->description ?? ''), 50),
                    'url' => route('gallery.show', $item->slug ?? $item->id),
                    'image' => url('/images/gallery/' . $item->id),
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Search gallery error: ' . $e->getMessage());
        }

        return $results;
    }

    /**
     * Get search history for authenticated user
     */
    public function getHistory(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['history' => []]);
        }

        $history = SearchHistory::getRecentForUser(Auth::id(), 8);

        return response()->json([
            'history' => $history->map(function ($item) {
                return [
                    'id' => $item->id,
                    'query' => $item->query,
                    'type' => $item->result_type,
                    'title' => $item->result_title,
                    'url' => $item->result_url,
                    'created_at' => $item->created_at->diffForHumans(),
                ];
            })
        ]);
    }

    /**
     * Save search to history for authenticated user
     */
    public function saveToHistory(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false]);
        }

        $validated = $request->validate([
            'query' => 'required|string|max:255',
            'result_type' => 'nullable|string|max:50',
            'result_id' => 'nullable',
            'result_title' => 'nullable|string|max:255',
            'result_url' => 'required|string|max:500',
        ]);

        SearchHistory::addToHistory(Auth::id(), $validated);

        return response()->json(['success' => true]);
    }

    /**
     * Clear search history for authenticated user
     */
    public function clearHistory(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false]);
        }

        SearchHistory::clearForUser(Auth::id());

        return response()->json(['success' => true]);
    }

    /**
     * Delete a specific history item
     */
    public function deleteHistoryItem(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false]);
        }

        SearchHistory::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['success' => true]);
    }
}
