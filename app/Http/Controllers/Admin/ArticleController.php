<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::where('category', '!=', 'berita');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('excerpt', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_published', $request->status === 'published');
        }

        $articles = $query->latest()->paginate(12)->withQueryString();
        $articles->getCollection()->transform(fn($a) => [
            'id' => $a->id,
            'title' => $a->title,
            'excerpt' => $a->excerpt,
            'category' => $a->category,
            'is_featured' => $a->is_featured,
            'is_published' => $a->is_published,
            'published_at' => $a->published_at,
            'image_url' => $a->image_url,
        ]);

        $categories = collect(Article::CATEGORIES)->except('berita')->toArray();

        return Inertia::render('Admin/Articles/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function create()
    {
        $categories = Article::CATEGORIES;
        return Inertia::render('Admin/Articles/Create', ['categories' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'category' => 'required|in:' . implode(',', array_keys(Article::CATEGORIES)),
            'author_name' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);

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
            // Clear file path if any (though featured_image column is kept for fallback, we don't need to fill it for new ones)
            $validated['featured_image'] = null;
        }

        Article::create($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article)
    {
        $categories = Article::CATEGORIES;
        return Inertia::render('Admin/Articles/Edit', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'category' => $article->category,
                'author_name' => $article->author_name,
                'is_featured' => $article->is_featured,
                'is_published' => $article->is_published,
                'image_url' => $article->image_url,
            ],
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'category' => 'required|in:' . implode(',', array_keys(Article::CATEGORIES)),
            'author_name' => 'nullable|string|max:255',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published');

        // Set published_at if publishing for first time
        if ($validated['is_published'] && !$article->published_at) {
            $validated['published_at'] = now();
        }

        // Handle image upload to Database
        if ($request->hasFile('featured_image')) {
            $imageData = \App\Services\ImageService::storeToDatabase($request->file('featured_image'));
            $validated['image_data'] = $imageData['data'];
            $validated['image_mime'] = $imageData['mime'];
            // Clear old file path if it existed
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
                $validated['featured_image'] = null;
            }
        }

        $article->update($validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article)
    {
        // No need to delete file manually if we are fully on DB, 
        // but if there's a legacy file, we clean it.
        if ($article->featured_image) {
            Storage::disk('public')->delete($article->featured_image);
        }

        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil dihapus.');
    }
}
