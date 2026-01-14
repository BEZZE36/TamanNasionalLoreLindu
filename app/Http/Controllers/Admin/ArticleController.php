<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    protected NotificationService $notificationService;
    protected PushNotificationService $pushNotificationService;

    public function __construct(
        NotificationService $notificationService,
        PushNotificationService $pushNotificationService
    ) {
        $this->notificationService = $notificationService;
        $this->pushNotificationService = $pushNotificationService;
    }
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
            'slug' => $a->slug,
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
        $categories = collect(Article::CATEGORIES)->except('berita')->toArray();
        return Inertia::render('Admin/Articles/Create', [
            'categories' => $categories,
            'locales' => Article::LOCALES,
        ]);
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
            'is_pinned' => 'boolean',
            'scheduled_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'locale' => 'nullable|in:id,en',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['user_id'] = auth()->id();
        $validated['locale'] = $validated['locale'] ?? 'id';

        if ($validated['is_published']) {
            $validated['published_at'] = now();
        }

        // Handle scheduling
        if (!empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = \Carbon\Carbon::parse($validated['scheduled_at']);
        }

        // Handle image upload to Database
        if ($request->hasFile('featured_image')) {
            $imageData = \App\Services\ImageService::storeToDatabase($request->file('featured_image'));
            $validated['image_data'] = $imageData['data'];
            $validated['image_mime'] = $imageData['mime'];
            $validated['featured_image'] = null;
        }

        $article = Article::create($validated);

        // Send notifications to all users if published
        if ($article->is_published) {
            $url = route('articles.show', $article->slug);
            $this->notificationService->notifyNewArticle(
                $article->title,
                $article->excerpt ?? '',
                $url
            );
            $this->pushNotificationService->notifyNewArticle(
                $article->title,
                $article->excerpt ?? '',
                $url
            );
        }

        return redirect()->route('admin.articles.index')
            ->with('success', 'Artikel berhasil ditambahkan.');
    }

    public function edit(Article $article)
    {
        $categories = collect(Article::CATEGORIES)->except('berita')->toArray();
        return Inertia::render('Admin/Articles/Edit', [
            'article' => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'category' => $article->category,
                'author_name' => $article->author_name,
                'is_featured' => $article->is_featured,
                'is_published' => $article->is_published,
                'is_pinned' => $article->is_pinned,
                'image_url' => $article->image_url,
                'scheduled_at' => $article->scheduled_at?->format('Y-m-d\TH:i'),
                'meta_title' => $article->meta_title,
                'meta_description' => $article->meta_description,
                'meta_keywords' => $article->meta_keywords,
                'locale' => $article->locale,
                'revision_count' => $article->revision_count,
                'last_auto_save' => $article->last_auto_save?->format('H:i:s'),
                'created_at' => $article->created_at,
                'published_at' => $article->published_at,
                'tags' => $article->tags,
            ],
            'categories' => $categories,
            'locales' => Article::LOCALES,
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
            'is_pinned' => 'boolean',
            'scheduled_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'locale' => 'nullable|in:id,en',
        ]);

        // Save revision before update if content changed
        if ($article->content !== $validated['content'] || $article->title !== $validated['title']) {
            $article->saveRevision(auth('admin')->id(), 'Content updated');
        }

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_pinned'] = $request->boolean('is_pinned');

        // Handle publish state changes
        $wasPublished = $article->is_published;
        $willPublish = $validated['is_published'];

        if ($willPublish && !$wasPublished) {
            // Publishing now - set new published_at
            $validated['published_at'] = now();
        } elseif (!$willPublish && $wasPublished) {
            // Unpublishing - clear published_at
            $validated['published_at'] = null;
        }

        // Handle scheduling
        if (!empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = \Carbon\Carbon::parse($validated['scheduled_at']);
        } else {
            $validated['scheduled_at'] = null;
        }

        // Handle image upload to Database
        if ($request->hasFile('featured_image')) {
            $imageData = \App\Services\ImageService::storeToDatabase($request->file('featured_image'));
            $validated['image_data'] = $imageData['data'];
            $validated['image_mime'] = $imageData['mime'];
            if ($article->featured_image) {
                Storage::disk('public')->delete($article->featured_image);
                $validated['featured_image'] = null;
            }
        }

        $article->update($validated);

        // Send push notification if newly published
        if ($willPublish && !$wasPublished) {
            try {
                $url = route('articles.show', $article->slug);
                $this->notificationService->notifyNewArticle(
                    $article->title,
                    $article->excerpt ?? '',
                    $url
                );
                $this->pushNotificationService->notifyNewArticle(
                    $article->title,
                    $article->excerpt ?? '',
                    $url
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Push notification failed: ' . $e->getMessage());
            }
        }

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

    public function togglePublished(Article $article)
    {
        $article->update([
            'is_published' => !$article->is_published,
            'published_at' => !$article->is_published ? now() : $article->published_at
        ]);

        return response()->json([
            'success' => true,
            'is_published' => $article->is_published,
            'message' => $article->is_published ? 'Artikel dipublikasikan' : 'Artikel menjadi draft'
        ]);
    }

    public function toggleFeatured(Article $article)
    {
        $article->update(['is_featured' => !$article->is_featured]);

        return response()->json([
            'success' => true,
            'is_featured' => $article->is_featured,
            'message' => $article->is_featured ? 'Artikel dijadikan unggulan' : 'Artikel bukan unggulan'
        ]);
    }

    public function duplicate(Article $article)
    {
        $newArticle = $article->replicate();
        $newArticle->title = $article->title . ' (Copy)';
        $newArticle->slug = Str::slug($newArticle->title) . '-' . time();
        $newArticle->is_published = false;
        $newArticle->published_at = null;
        $newArticle->last_auto_save = null;
        $newArticle->revision_count = 0;
        $newArticle->save();

        return response()->json([
            'success' => true,
            'message' => 'Artikel berhasil diduplikasi',
            'redirect' => route('admin.articles.edit', $newArticle)
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:articles,id'
        ]);

        $count = count($request->ids);
        Article::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $count . ' artikel berhasil dihapus'
        ]);
    }

    public function togglePinned(Article $article)
    {
        $article->update(['is_pinned' => !$article->is_pinned]);

        return response()->json([
            'success' => true,
            'is_pinned' => $article->is_pinned,
            'message' => $article->is_pinned ? 'Artikel di-pin' : 'Artikel di-unpin'
        ]);
    }

    public function archive(Article $article)
    {
        $article->archive();

        return response()->json([
            'success' => true,
            'message' => 'Artikel diarsipkan'
        ]);
    }

    public function unarchive(Article $article)
    {
        $article->update(['archived_at' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Artikel dipulihkan'
        ]);
    }

    public function bulkPublish(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:articles,id'
        ]);

        Article::whereIn('id', $request->ids)->update([
            'is_published' => true,
            'published_at' => now(),
            'archived_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' artikel dipublikasikan'
        ]);
    }

    public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:articles,id'
        ]);

        Article::whereIn('id', $request->ids)->update([
            'archived_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' artikel diarsipkan'
        ]);
    }

    public function autoSave(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
            'category' => 'nullable|string',
            'author_name' => 'nullable|string|max:255',
            'is_featured' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
            'scheduled_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'locale' => 'nullable|string',
        ]);

        $updateData = [
            'title' => $request->title ?? $article->title,
            'content' => $request->content ?? $article->content,
            'excerpt' => $request->excerpt ?? $article->excerpt,
            'last_auto_save' => now(),
        ];

        // Save revision if content or title changed significantly
        $contentChanged = $request->has('content') && $article->content !== $request->content;
        $titleChanged = $request->has('title') && $article->title !== $request->title;
        if ($contentChanged || $titleChanged) {
            $article->saveRevision(auth('admin')->id(), 'Auto-save');
        }

        // Add optional fields if provided
        if ($request->has('category'))
            $updateData['category'] = $request->category;
        if ($request->has('author_name'))
            $updateData['author_name'] = $request->author_name;
        if ($request->has('is_featured'))
            $updateData['is_featured'] = $request->boolean('is_featured');
        if ($request->has('is_published'))
            $updateData['is_published'] = $request->boolean('is_published');
        if ($request->has('is_pinned'))
            $updateData['is_pinned'] = $request->boolean('is_pinned');
        if ($request->has('scheduled_at'))
            $updateData['scheduled_at'] = $request->scheduled_at ? \Carbon\Carbon::parse($request->scheduled_at) : null;
        if ($request->has('meta_title'))
            $updateData['meta_title'] = $request->meta_title;
        if ($request->has('meta_description'))
            $updateData['meta_description'] = $request->meta_description;
        if ($request->has('meta_keywords'))
            $updateData['meta_keywords'] = $request->meta_keywords;
        if ($request->has('locale'))
            $updateData['locale'] = $request->locale;

        $article->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Draft disimpan',
            'last_auto_save' => $article->last_auto_save->format('H:i:s')
        ]);
    }

    public function revisions(Article $article)
    {
        $revisions = $article->revisions()
            ->with('admin:id,name')
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'revisions' => $revisions
        ]);
    }

    public function restoreRevision(Article $article, \App\Models\ArticleRevision $revision)
    {
        // Save current state as revision first
        $article->saveRevision(auth('admin')->id(), 'Before restoring revision #' . $revision->id);

        // Restore the revision
        $revision->restoreToArticle();

        return response()->json([
            'success' => true,
            'message' => 'Revisi dipulihkan'
        ]);
    }
}

