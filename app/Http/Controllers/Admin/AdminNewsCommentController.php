<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleComment;
use App\Models\Article;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminNewsCommentController extends Controller
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
        $query = ArticleComment::with(['user', 'admin', 'article', 'replies.user', 'replies.admin'])
            ->root()
            ->whereHas('article', function ($q) {
                $q->where('category', 'berita');
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('article_id')) {
            $query->where('article_id', $request->article_id);
        }

        $comments = $query->orderBy('is_pinned', 'desc')->latest()->paginate(15)->withQueryString();

        $comments->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'content' => $c->content,
            'is_visible' => $c->is_visible,
            'is_pinned' => $c->is_pinned,
            'article_id' => $c->article_id,
            'created_at' => $c->created_at,
            'user' => $c->user ? ['name' => $c->user->name] : null,
            'admin' => $c->admin ? ['name' => $c->admin->name] : null,
            'article' => $c->article ? ['title' => $c->article->title] : null,
            'replies' => $c->replies->map(fn($r) => [
                'id' => $r->id,
                'content' => $r->content,
                'is_visible' => $r->is_visible,
                'created_at' => $r->created_at,
                'user' => $r->user ? ['name' => $r->user->name] : null,
                'admin' => $r->admin ? ['name' => $r->admin->name] : null,
            ]),
        ]);

        $articleList = Article::where('category', 'berita')
            ->select('id', 'title')
            ->orderBy('title')
            ->get();
        $filters = $request->only(['search', 'article_id']);

        return Inertia::render('Admin/News/Comments', compact('comments', 'articleList', 'filters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:article_comments,id',
        ]);

        ArticleComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'article_id' => $request->input('article_id'),
            'content' => $request->input('content'),
            'parent_id' => $request->input('parent_id'),
            'is_visible' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->parent_id ? 'Balasan berhasil ditambahkan' : 'Komentar berhasil ditambahkan'
        ]);
    }

    public function reply(Request $request, ArticleComment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        ArticleComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'article_id' => $comment->article_id,
            'content' => $request->input('content'),
            'parent_id' => $comment->id,
            'is_visible' => true,
        ]);

        // Send notification to original commenter and thread participants
        $article = $comment->article;
        if ($article && $comment->user_id) {
            $participantUserIds = ArticleComment::where('article_id', $comment->article_id)
                ->where(function ($q) use ($comment) {
                    $q->where('id', $comment->id)->orWhere('parent_id', $comment->id);
                })
                ->whereNotNull('user_id')
                ->pluck('user_id')->unique()->toArray();

            $url = route('news.show', $article->slug);
            $this->notificationService->notifyCommentReply($comment->user_id, $participantUserIds, 'Berita', $article->title, $url);

            foreach (array_unique(array_filter(array_merge([$comment->user_id], $participantUserIds))) as $userId) {
                $this->pushNotificationService->notifyCommentReply($userId, 'Berita', $article->title, $url);
            }
        }

        return response()->json(['success' => true, 'message' => 'Balasan berhasil ditambahkan']);
    }

    public function destroy(ArticleComment $comment)
    {
        $comment->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus'
            ]);
        }

        return back()->with('success', 'Komentar berhasil dihapus');
    }

    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:article_comments,id'
        ]);

        ArticleComment::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' komentar berhasil dihapus'
        ]);
    }

    public function toggleStatus(ArticleComment $comment)
    {
        $comment->update([
            'is_visible' => !$comment->is_visible
        ]);

        return response()->json([
            'success' => true,
            'is_visible' => $comment->is_visible,
            'message' => $comment->is_visible ? 'Komentar ditampilkan' : 'Komentar disembunyikan'
        ]);
    }

    public function togglePin(ArticleComment $comment)
    {
        $comment->update([
            'is_pinned' => !$comment->is_pinned
        ]);

        return response()->json([
            'success' => true,
            'is_pinned' => $comment->is_pinned,
            'message' => $comment->is_pinned ? 'Komentar disematkan' : 'Komentar tidak lagi disematkan'
        ]);
    }

    /**
     * Approve a comment.
     */
    public function approve(ArticleComment $comment)
    {
        $comment->update(['status' => 'approved', 'is_visible' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Komentar disetujui.'
        ]);
    }

    /**
     * Reject a comment.
     */
    public function reject(ArticleComment $comment)
    {
        $comment->update(['status' => 'rejected', 'is_visible' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Komentar ditolak.'
        ]);
    }

    /**
     * Clear report on a comment.
     */
    public function clearReport(ArticleComment $comment)
    {
        $comment->update(['is_reported' => false, 'report_reason' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan dihapus.'
        ]);
    }

    /**
     * Block a user from commenting.
     */
    public function blockUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:255',
        ]);

        \App\Models\BlockedCommenter::updateOrCreate(
            ['user_id' => $validated['user_id']],
            [
                'blocked_by' => Auth::guard('admin')->id(),
                'reason' => $validated['reason'] ?? null,
                'blocked_at' => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengguna diblokir dari berkomentar.'
        ]);
    }

    /**
     * Unblock a user.
     */
    public function unblockUser($userId)
    {
        \App\Models\BlockedCommenter::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna tidak lagi diblokir.'
        ]);
    }

    /**
     * Batch approve comments.
     */
    public function batchApprove(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:article_comments,id',
        ]);

        ArticleComment::whereIn('id', $validated['ids'])
            ->update(['status' => 'approved', 'is_visible' => true]);

        return response()->json([
            'success' => true,
            'message' => count($validated['ids']) . ' komentar disetujui.'
        ]);
    }

    /**
     * Batch reject comments.
     */
    public function batchReject(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:article_comments,id',
        ]);

        ArticleComment::whereIn('id', $validated['ids'])
            ->update(['status' => 'rejected', 'is_visible' => false]);

        return response()->json([
            'success' => true,
            'message' => count($validated['ids']) . ' komentar ditolak.'
        ]);
    }
}

