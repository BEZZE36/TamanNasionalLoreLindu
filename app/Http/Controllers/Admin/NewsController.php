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

class NewsController extends Controller
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
            'slug' => $a->slug,
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
        return Inertia::render('Admin/News/Create', [
            'locales' => Article::LOCALES,
        ]);
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
            'is_pinned' => 'boolean',
            'scheduled_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'locale' => 'nullable|in:id,en',
        ]);

        $validated['category'] = 'berita';
        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['user_id'] = auth()->id();
        $validated['locale'] = $validated['locale'] ?? 'id';

        if ($validated['is_published']) {
            $validated['published_at'] = now();
        }

        if (!empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = \Carbon\Carbon::parse($validated['scheduled_at']);
        }

        if ($request->hasFile('featured_image')) {
            $imageData = \App\Services\ImageService::storeToDatabase($request->file('featured_image'));
            $validated['image_data'] = $imageData['data'];
            $validated['image_mime'] = $imageData['mime'];
            $validated['featured_image'] = null;
        }

        $news = Article::create($validated);

        // Send notifications if published
        if ($news->is_published) {
            $url = route('news.show', $news->slug);
            $this->notificationService->notifyNewNews(
                $news->title,
                $news->excerpt ?? '',
                $url
            );
            $this->pushNotificationService->notifyNewNews(
                $news->title,
                $news->excerpt ?? '',
                $url
            );
        }

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
                'slug' => $news->slug,
                'excerpt' => $news->excerpt,
                'content' => $news->content,
                'author_name' => $news->author_name,
                'is_featured' => $news->is_featured,
                'is_published' => $news->is_published,
                'is_pinned' => $news->is_pinned,
                'image_url' => $news->image_url,
                'scheduled_at' => $news->scheduled_at?->format('Y-m-d\TH:i'),
                'meta_title' => $news->meta_title,
                'meta_description' => $news->meta_description,
                'meta_keywords' => $news->meta_keywords,
                'locale' => $news->locale,
                'revision_count' => $news->revision_count,
                'last_auto_save' => $news->last_auto_save?->format('H:i:s'),
                'created_at' => $news->created_at,
                'published_at' => $news->published_at,
            ],
            'locales' => Article::LOCALES,
        ]);
    }

    /**
     * Update news article
     */
    public function update(Request $request, Article $news)
    {
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
            'is_pinned' => 'boolean',
            'scheduled_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'locale' => 'nullable|in:id,en',
        ]);

        // Save revision before update if content changed
        if ($news->content !== $validated['content'] || $news->title !== $validated['title']) {
            $news->saveRevision(auth('admin')->id(), 'Content updated');
        }

        $validated['category'] = 'berita';
        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_published'] = $request->boolean('is_published');
        $validated['is_pinned'] = $request->boolean('is_pinned');

        // Handle publish state changes
        $wasPublished = $news->is_published;
        $willPublish = $validated['is_published'];

        if ($willPublish && !$wasPublished) {
            // Publishing now - set new published_at
            $validated['published_at'] = now();
        } elseif (!$willPublish && $wasPublished) {
            // Unpublishing - clear published_at
            $validated['published_at'] = null;
        }

        if (!empty($validated['scheduled_at'])) {
            $validated['scheduled_at'] = \Carbon\Carbon::parse($validated['scheduled_at']);
        } else {
            $validated['scheduled_at'] = null;
        }

        if ($request->hasFile('featured_image')) {
            $imageData = \App\Services\ImageService::storeToDatabase($request->file('featured_image'));
            $validated['image_data'] = $imageData['data'];
            $validated['image_mime'] = $imageData['mime'];
            if ($news->featured_image) {
                Storage::disk('public')->delete($news->featured_image);
                $validated['featured_image'] = null;
            }
        }

        $news->update($validated);

        // Send push notification if newly published
        if ($willPublish && !$wasPublished) {
            try {
                $url = route('news.show', $news->slug);
                $this->notificationService->notifyNewNews(
                    $news->title,
                    $news->excerpt ?? '',
                    $url
                );
                $this->pushNotificationService->notifyNewNews(
                    $news->title,
                    $news->excerpt ?? '',
                    $url
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Push notification failed: ' . $e->getMessage());
            }
        }

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

    public function togglePublished(Article $news)
    {
        $news->update([
            'is_published' => !$news->is_published,
            'published_at' => !$news->is_published ? now() : $news->published_at
        ]);

        return response()->json([
            'success' => true,
            'is_published' => $news->is_published,
            'message' => $news->is_published ? 'Berita dipublikasikan' : 'Berita menjadi draft'
        ]);
    }

    public function toggleFeatured(Article $news)
    {
        $news->update(['is_featured' => !$news->is_featured]);

        return response()->json([
            'success' => true,
            'is_featured' => $news->is_featured,
            'message' => $news->is_featured ? 'Berita dijadikan headline' : 'Berita bukan headline'
        ]);
    }

    public function duplicate(Article $news)
    {
        $newNews = $news->replicate();
        $newNews->title = $news->title . ' (Copy)';
        $newNews->slug = Str::slug($newNews->title) . '-' . time();
        $newNews->is_published = false;
        $newNews->published_at = null;
        $newNews->last_auto_save = null;
        $newNews->revision_count = 0;
        $newNews->save();

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diduplikasi',
            'redirect' => route('admin.news.edit', $newNews)
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:articles,id'
        ]);

        $count = count($request->ids);
        Article::whereIn('id', $request->ids)->where('category', 'berita')->delete();

        return response()->json([
            'success' => true,
            'message' => $count . ' berita berhasil dihapus'
        ]);
    }

    public function togglePinned(Article $news)
    {
        $news->update(['is_pinned' => !$news->is_pinned]);

        return response()->json([
            'success' => true,
            'is_pinned' => $news->is_pinned,
            'message' => $news->is_pinned ? 'Berita di-pin' : 'Berita di-unpin'
        ]);
    }

    public function archive(Article $news)
    {
        $news->update(['archived_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Berita diarsipkan'
        ]);
    }

    public function unarchive(Article $news)
    {
        $news->update(['archived_at' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Berita dipulihkan'
        ]);
    }

    public function bulkPublish(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:articles,id'
        ]);

        Article::whereIn('id', $request->ids)->where('category', 'berita')->update([
            'is_published' => true,
            'published_at' => now(),
            'archived_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' berita dipublikasikan'
        ]);
    }

    public function bulkArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:articles,id'
        ]);

        Article::whereIn('id', $request->ids)->where('category', 'berita')->update([
            'archived_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' berita diarsipkan'
        ]);
    }

    public function autoSave(Request $request, Article $news)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'excerpt' => 'nullable|string',
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
            'title' => $request->title ?? $news->title,
            'content' => $request->content ?? $news->content,
            'excerpt' => $request->excerpt ?? $news->excerpt,
            'last_auto_save' => now(),
        ];

        // Save revision if content or title changed significantly
        $contentChanged = $request->has('content') && $news->content !== $request->content;
        $titleChanged = $request->has('title') && $news->title !== $request->title;
        if ($contentChanged || $titleChanged) {
            $news->saveRevision(auth('admin')->id(), 'Auto-save');
        }

        // Add optional fields if provided
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

        $news->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Draft disimpan',
            'last_auto_save' => $news->last_auto_save->format('H:i:s')
        ]);
    }

    public function revisions(Article $news)
    {
        $revisions = $news->revisions()
            ->with('admin:id,name')
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'revisions' => $revisions
        ]);
    }

    public function restoreRevision(Article $news, \App\Models\ArticleRevision $revision)
    {
        $news->saveRevision(auth('admin')->id(), 'Before restoring revision #' . $revision->id);
        $revision->restoreToArticle();

        return response()->json([
            'success' => true,
            'message' => 'Revisi dipulihkan'
        ]);
    }
}

