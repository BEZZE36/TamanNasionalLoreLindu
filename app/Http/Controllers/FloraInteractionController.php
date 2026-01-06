<?php

namespace App\Http\Controllers;

use App\Models\Flora;
use App\Models\FloraComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FloraInteractionController extends Controller
{
    /**
     * Store a new comment on a flora item.
     */
    public function storeComment(Request $request, Flora $flora)
    {
        $request->validate([
            'content' => 'required|string|min:3|max:1000',
            'parent_id' => 'nullable|exists:flora_comments,id',
        ], [
            'content.required' => 'Komentar tidak boleh kosong.',
            'content.min' => 'Komentar minimal 3 karakter.',
            'content.max' => 'Komentar maksimal 1000 karakter.',
        ]);

        $comment = $flora->comments()->create([
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
    public function deleteComment(FloraComment $comment)
    {
        // Users cannot delete comments as per new policy
        return response()->json([
            'success' => false,
            'message' => 'Fitur hapus komentar telah dinonaktifkan.',
        ], 403);
    }
}
