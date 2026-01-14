<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for managing news tags (reuses ArticleTag model).
 */
class NewsTagController extends Controller
{
    /**
     * Display tags for news.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $tags = ArticleTag::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->withCount(['articles' => fn($q) => $q->where('category', 'berita')])
            ->orderBy('name')
            ->paginate(50);

        return Inertia::render('Admin/News/Tags', [
            'tags' => $tags,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Store a new tag.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:article_tags,name',
        ]);

        $tag = ArticleTag::create([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil ditambahkan.',
            'tag' => $tag,
        ]);
    }

    /**
     * Update a tag.
     */
    public function update(Request $request, ArticleTag $tag): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:article_tags,name,' . $tag->id,
        ]);

        $tag->update([
            'name' => $validated['name'],
            'slug' => \Illuminate\Support\Str::slug($validated['name']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil diperbarui.',
            'tag' => $tag,
        ]);
    }

    /**
     * Delete a tag.
     */
    public function destroy(ArticleTag $tag): JsonResponse
    {
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag berhasil dihapus.',
        ]);
    }

    /**
     * Bulk delete tags.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:article_tags,id',
        ]);

        ArticleTag::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => count($validated['ids']) . ' tag berhasil dihapus.',
        ]);
    }

    /**
     * Search tags for autocomplete.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');

        $tags = ArticleTag::where('name', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($tags);
    }
}
