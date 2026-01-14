<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DestinationComment;
use App\Models\Destination;
use App\Services\NotificationService;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminDestinationCommentController extends Controller
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
        $query = DestinationComment::with(['user', 'admin', 'destination', 'replies.user', 'replies.admin'])
            ->root(); // Only get top-level comments

        // Search
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

        // Filter by Destination
        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        $comments = $query->orderBy('is_pinned', 'desc')->latest()->paginate(15)->withQueryString();

        // Transform for Vue
        $comments->getCollection()->transform(fn($c) => [
            'id' => $c->id,
            'content' => $c->content,
            'rating' => $c->rating,
            'is_visible' => $c->is_visible,
            'is_pinned' => $c->is_pinned,
            'destination_id' => $c->destination_id,
            'created_at' => $c->created_at,
            'user' => $c->user ? ['name' => $c->user->name] : null,
            'admin' => $c->admin ? ['name' => $c->admin->name] : null,
            'destination' => $c->destination ? ['name' => $c->destination->name] : null,
            'replies' => $c->replies->map(fn($r) => [
                'id' => $r->id,
                'content' => $r->content,
                'user' => $r->user ? ['name' => $r->user->name] : null,
                'admin' => $r->admin ? ['name' => $r->admin->name] : null,
            ]),
        ]);

        $destinationList = Destination::select('id', 'name')->orderBy('name')->get();
        $filters = $request->only(['search', 'destination_id']);

        return Inertia::render('Admin/Destinations/Comments', compact('comments', 'destinationList', 'filters'));
    }

    /**
     * Store a new comment (admin can add comments)
     */
    public function store(Request $request)
    {
        $request->validate([
            'destination_id' => 'required|exists:destinations,id',
            'content' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:destination_comments,id',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $data = [
            'admin_id' => Auth::guard('admin')->id(),
            'destination_id' => $request->input('destination_id'),
            'content' => $request->input('content'),
            'parent_id' => $request->input('parent_id'),
            'is_visible' => true,
        ];

        // Only include rating for main comments
        if (!$request->input('parent_id') && $request->input('rating')) {
            $data['rating'] = $request->input('rating');
        }

        DestinationComment::create($data);

        return response()->json([
            'success' => true,
            'message' => $request->parent_id ? 'Balasan berhasil ditambahkan' : 'Komentar berhasil ditambahkan'
        ]);
    }

    /**
     * Reply to a comment
     */
    public function reply(Request $request, DestinationComment $comment)
    {
        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        DestinationComment::create([
            'admin_id' => Auth::guard('admin')->id(),
            'destination_id' => $comment->destination_id,
            'content' => $request->input('content'),
            'parent_id' => $comment->id,
            'is_visible' => true,
        ]);

        // Send notification to original commenter and thread participants
        $destination = $comment->destination;
        if ($destination && $comment->user_id) {
            $participantUserIds = DestinationComment::where('destination_id', $comment->destination_id)
                ->where(function ($q) use ($comment) {
                    $q->where('id', $comment->id)->orWhere('parent_id', $comment->id);
                })
                ->whereNotNull('user_id')
                ->pluck('user_id')->unique()->toArray();

            $url = route('destinations.show', $destination->slug);
            $this->notificationService->notifyCommentReply($comment->user_id, $participantUserIds, 'Destinasi', $destination->name, $url);

            foreach (array_unique(array_filter(array_merge([$comment->user_id], $participantUserIds))) as $userId) {
                $this->pushNotificationService->notifyCommentReply($userId, 'Destinasi', $destination->name, $url);
            }
        }

        return response()->json(['success' => true, 'message' => 'Balasan berhasil ditambahkan']);
    }

    public function destroy(DestinationComment $comment)
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
            'ids.*' => 'exists:destination_comments,id'
        ]);

        DestinationComment::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' komentar berhasil dihapus'
        ]);
    }

    public function toggleStatus(DestinationComment $comment)
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

    public function togglePin(DestinationComment $comment)
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
}
