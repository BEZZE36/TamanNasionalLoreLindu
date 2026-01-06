<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\GalleryComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GalleryInteractionController extends Controller
{
    /**
     * Toggle like on a gallery.
     */
    public function toggleLike(Gallery $gallery)
    {
        $user = Auth::user();

        if ($user->hasLikedGallery($gallery->id)) {
            $user->galleryLikes()->detach($gallery->id);
            $liked = false;
        } else {
            $user->galleryLikes()->attach($gallery->id);
            $liked = true;
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'likes_count' => $gallery->likes()->count(),
            'formatted_likes' => $gallery->formatted_likes_count,
        ]);
    }

    /**
     * Store a new comment on a gallery.
     */
    public function storeComment(Request $request, Gallery $gallery)
    {
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|exists:gallery_comments,id',
        ], [
            'content.required' => 'Komentar tidak boleh kosong.',
            'content.min' => 'Komentar minimal 3 karakter.',
            'content.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        $comment = $gallery->comments()->create([
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
            'parent_id' => $request->input('parent_id'),
            'is_approved' => true,
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'message' => $request->parent_id ? 'Balasan berhasil ditambahkan.' : 'Komentar berhasil ditambahkan.',
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'parent_id' => $comment->parent_id,
                'created_at' => $comment->created_at->diffForHumans(),
                'user' => [
                    'name' => $comment->user->name,
                    'avatar_url' => $comment->user->avatar_url,
                ],
            ],
        ]);
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(GalleryComment $comment)
    {
        // Users cannot delete comments as per new policy
        return response()->json([
            'success' => false,
            'message' => 'Fitur hapus komentar telah dinonaktifkan.',
        ], 403);
    }

    /**
     * Get comments for a gallery (paginated).
     */
    public function getComments(Gallery $gallery, Request $request)
    {
        $comments = $gallery->comments()
            ->approved()
            ->topLevel()
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'success' => true,
            'comments' => $comments->map(function ($comment) {
                return $this->formatComment($comment);
            }),
            'has_more' => $comments->hasMorePages(),
        ]);
    }

    /**
     * Format comment for JSON response
     */
    private function formatComment($comment)
    {
        $data = [
            'id' => $comment->id,
            'content' => $comment->content,
            'parent_id' => $comment->parent_id,
            'created_at' => $comment->created_at->diffForHumans(),
            'is_owner' => Auth::check() && Auth::id() === $comment->user_id,
            'user' => [
                'name' => $comment->user->name,
                'avatar_url' => $comment->user->avatar_url,
            ],
            'replies' => [],
        ];

        if ($comment->relationLoaded('replies') && $comment->replies->count() > 0) {
            $data['replies'] = $comment->replies->map(function ($reply) {
                return $this->formatComment($reply);
            });
        }

        return $data;
    }
}
