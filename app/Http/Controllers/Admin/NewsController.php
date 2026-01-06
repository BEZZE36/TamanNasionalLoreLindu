<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NewsController extends Controller
{
    /**
     * Display listing of news articles (category = berita)
     */
    public function index(Request $request)
    {
        $query = Article::where('category', 'berita');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        $news = $query->latest()->paginate(12)->withQueryString();
        $news->getCollection()->transform(fn($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'excerpt' => $a->excerpt,
            'is_featured' => $a->is_featured,
            'is_published' => $a->is_published,
            'published_at' => $a->published_at,
            'image_url' => $a->image_url,
        ]);

        return Inertia::render('Admin/News/Index', [
            'news' => $news,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/News/Create');
    }

    /**
     * Store new news article
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'author_name' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);

        $validated['category'] = 'berita'; // Force category to berita
        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published');
        $validated['user_id'] = auth()->id();

        if ($validated['is_published']) {
            $validated['published_at'] = now();
        }

        // Handle image upload to Database
        if ($request->hasFile('featured_image')) {
            $imageData = \App\Services\ImageService::storeToDatabase($request->file('featured_image'));
            $validated['image_data'] = $imageData['data'];
            $validated['image_mime'] = $imageData['mime'];
            $validated['featured_image'] = null;
        }

        Article::create($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'Berita berhasil ditambahkan.');
    }

    /**
     * Show form to edit news article
     */
    public function edit(Article $news)
    {
        if ($news->category !== 'berita') {
            abort(404);
        }

        return Inertia::render('Admin/News/Edit', [
            'news' => [
                'id' => $news->id,
                'title' => $news->title,
                'excerpt' => $news->excerpt,
                'content' => $news->content,
                'author_name' => $news->author_name,
                'is_featured' => $news->is_featured,
                'is_published' => $news->is_published,
                'image_url' => $news->image_url,
            ],
        ]);
    }

    /**
     * Update news article
     */
    public function update(Request $request, Article $news)
    {
        // Ensure we're only editing news articles
        if ($news->category !== 'berita') {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'author_name' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);

        $validated['category'] = 'berita'; // Keep category as berita
        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published');

        // Set published_at if publishing for first time
        if ($validated['is_published'] && !$news->published_at) {
            $validated['published_at'] = now();
        }

        // Handle image upload to Database
        if ($request->hasFile('featured_image')) {
            $imageData = \App\Services\ImageService::storeToDatabase($request->file('featured_image'));
            $validated['image_data'] = $imageData['data'];
            $validated['image_mime'] = $imageData['mime'];
            // Clear old file path if it existed
            if ($news->featured_image) {
                Storage::disk('public')->delete($news->featured_image);
                $validated['featured_image'] = null;
            }
        }

        $news->update($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'Berita berhasil diperbarui.');
    }

    /**
     * Delete news article
     */
    public function destroy(Article $news)
    {
        // Ensure we're only deleting news articles
        if ($news->category !== 'berita') {
            abort(404);
        }

        // Clean up legacy file if exists
        if ($news->featured_image) {
            Storage::disk('public')->delete($news->featured_image);
        }

        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'Berita berhasil dihapus.');
    }
}
