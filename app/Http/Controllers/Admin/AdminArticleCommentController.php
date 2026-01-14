<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArticleComment;
use App\Models\Article;
use App\Models\BlockedCommenter;
use App\Models\BadWord;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminArticleCommentController extends Controller
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
                $q->where('category', '!=', 'berita');
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

        // Status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'pending':
                    $query->pending();
                    break;
                case 'approved':
                    $query->approved();
                    break;
                case 'rejected':
                    $query->rejected();
                    break;
                case 'reported':
                    $query->reported();
                    break;
            }
        }

        $comments = $query->orderBy('is_pinned', 'desc')->latest()->paginate(15)->withQueryString();

        $comments->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'content' => $c->content,
            'is_visible' => $c->is_visible,
            'is_pinned' => $c->is_pinned,
            'status' => $c->status,
            'status_label' => $c->status_label,
            'is_reported' => $c->is_reported,
            'report_reason' => $c->report_reason,
            'likes_count' => $c->likes_count,
            'article_id' => $c->article_id,
            'user_id' => $c->user_id,
            'created_at' => $c->created_at,
            'user' => $c->user ? ['id' => $c->user->id, 'name' => $c->user->name, 'email' => $c->user->email] : null,
            'admin' => $c->admin ? ['name' => $c->admin->name] : null,
            'article' => $c->article ? ['title' => $c->article->title] : null,
            'replies' => $c->replies->map(fn($r) => [
                'id' => $r->id,
                'content' => $r->content,
                'is_visible' => $r->is_visible,
                'created_at' => $r->created_at,
                'status' => $r->status,
                'user' => $r->user ? ['name' => $r->user->name] : null,
                'admin' => $r->admin ? ['name' => $r->admin->name] : null,
            ]),
        ]);

        $articleList = Article::where('category', '!=', 'berita')
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        // Stats
        $stats = [
            'total' => ArticleComment::whereHas('article', fn($q) => $q->where('category', '!=', 'berita'))->count(),
            'pending' => ArticleComment::whereHas('article', fn($q) => $q->where('category', '!=', 'berita'))->pending()->count(),
            'approved' => ArticleComment::whereHas('article', fn($q) => $q->where('category', '!=', 'berita'))->approved()->count(),
            'rejected' => ArticleComment::whereHas('article', fn($q) => $q->where('category', '!=', 'berita'))->rejected()->count(),
            'reported' => ArticleComment::whereHas('article', fn($q) => $q->where('category', '!=', 'berita'))->reported()->count(),
        ];

        $blockedUsers = BlockedCommenter::with('user:id,name,email')
            ->latest()
            ->take(10)
            ->get();

        $filters = $request->only(['search', 'article_id', 'status']);

        return Inertia::render('Admin/Articles/Comments', compact('comments', 'articleList', 'filters', 'stats', 'blockedUsers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'article_id' => 'required|exists:articles,id',
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:article_comments,id',
        ]);

        $content = BadWord::filterText($request->input('content'));

        ArticleComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'article_id' => $request->input('article_id'),
            'content' => $content,
            'parent_id' => $request->input('parent_id'),
            'is_visible' => true,
            'status' => ArticleComment::STATUS_APPROVED,
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

        $content = BadWord::filterText($request->input('content'));

        ArticleComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'article_id' => $comment->article_id,
            'content' => $content,
            'parent_id' => $comment->id,
            'is_visible' => true,
            'status' => ArticleComment::STATUS_APPROVED,
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

            $url = route('articles.show', $article->slug);
            $this->notificationService->notifyCommentReply($comment->user_id, $participantUserIds, 'Artikel', $article->title, $url);

            foreach (array_unique(array_filter(array_merge([$comment->user_id], $participantUserIds))) as $userId) {
                $this->pushNotificationService->notifyCommentReply($userId, 'Artikel', $article->title, $url);
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
        $comment->update(['is_visible' => !$comment->is_visible]);

        return response()->json([
            'success' => true,
            'is_visible' => $comment->is_visible,
            'message' => $comment->is_visible ? 'Komentar ditampilkan' : 'Komentar disembunyikan'
        ]);
    }

    public function togglePin(ArticleComment $comment)
    {
        $comment->update(['is_pinned' => !$comment->is_pinned]);

        return response()->json([
            'success' => true,
            'is_pinned' => $comment->is_pinned,
            'message' => $comment->is_pinned ? 'Komentar disematkan' : 'Komentar tidak lagi disematkan'
        ]);
    }

    public function approve(ArticleComment $comment)
    {
        $comment->approve();

        return response()->json([
            'success' => true,
            'status' => $comment->status,
            'message' => 'Komentar disetujui'
        ]);
    }

    public function reject(ArticleComment $comment)
    {
        $comment->reject();

        return response()->json([
            'success' => true,
            'status' => $comment->status,
            'message' => 'Komentar ditolak'
        ]);
    }

    public function clearReport(ArticleComment $comment)
    {
        $comment->clearReport();

        return response()->json([
            'success' => true,
            'message' => 'Laporan dibersihkan'
        ]);
    }

    public function blockUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
            'blocked_until' => 'nullable|date',
        ]);

        BlockedCommenter::updateOrCreate(
            ['user_id' => $request->user_id],
            [
                'admin_id' => Auth::guard('admin')->id(),
                'reason' => $request->reason,
                'blocked_until' => $request->blocked_until,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengguna diblokir dari komentar'
        ]);
    }

    public function unblockUser($userId)
    {
        BlockedCommenter::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil di-unblok'
        ]);
    }

    public function batchApprove(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:article_comments,id'
        ]);

        ArticleComment::whereIn('id', $request->ids)->update([
            'status' => ArticleComment::STATUS_APPROVED,
            'is_visible' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' komentar disetujui'
        ]);
    }

    public function batchReject(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:article_comments,id'
        ]);

        ArticleComment::whereIn('id', $request->ids)->update([
            'status' => ArticleComment::STATUS_REJECTED,
            'is_visible' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' komentar ditolak'
        ]);
    }
}
