<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommentsController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'cmt_content' => 'required|string',
                'cmt_fnd_id' => 'required|integer',
                'cmt_attachment' => 'nullable|file|max:10240' // 10MB max
            ]);

            $comment = new Comment();
            $comment->cmt_content = $request->cmt_content;
            $comment->cmt_fnd_id = $request->cmt_fnd_id;
            $comment->cmt_added_by = auth()->id();

            if ($request->hasFile('cmt_attachment')) {
                $path = $request->file('cmt_attachment')->store('comments', 'public');
                $comment->cmt_attachment = $path;
            }

            $comment->save();

            return redirect()->back()->with('success', 'Comment added successfully');
        } catch (\Exception $e) {
            \Log::error('Error adding comment: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error adding comment: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function reply(Request $request, $commentId)
    {
        $request->validate([
            'cmt_content' => 'required|string',
            'cmt_attachment' => 'nullable|file|max:10240'
        ]);

        $reply = new Comment();
        $reply->cmt_content = $request->cmt_content;
        $reply->cmt_fnd_id = Comment::findOrFail($commentId)->cmt_fnd_id;
        $reply->cmt_added_by = auth()->id();
        $reply->cmt_isReply_to = $commentId;

        if ($request->hasFile('cmt_attachment')) {
            $path = $request->file('cmt_attachment')->store('comments', 'public');
            $reply->cmt_attachment = $path;
        }

        $reply->save();

        return redirect()->back()->with('success', 'Reply added successfully');
    }

    public function update(Request $request, $id)
    {
        $comment = Comment::findOrFail($id);

        // Check if user is authorized to update
        if ($comment->cmt_added_by !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action');
        }

        $request->validate([
            'cmt_content' => 'required|string',
            'cmt_attachment' => 'nullable|file|max:10240'
        ]);

        $comment->cmt_content = $request->cmt_content;

        if ($request->hasFile('cmt_attachment')) {
            // Delete old attachment if exists
            if ($comment->cmt_attachment) {
                Storage::disk('public')->delete($comment->cmt_attachment);
            }
            
            $path = $request->file('cmt_attachment')->store('comments', 'public');
            $comment->cmt_attachment = $path;
        }

        $comment->save();

        return redirect()->back()->with('success', 'Comment updated successfully');
    }

    public function destroy($id)
    {
        $comment = Comment::findOrFail($id);

        // Check if user is authorized to delete
        if ($comment->cmt_added_by !== auth()->id()) {
            return redirect()->back()->with('error', 'Unauthorized action');
        }

        // Delete attachment if exists
        if ($comment->cmt_attachment) {
            Storage::disk('public')->delete($comment->cmt_attachment);
        }

        // Delete all replies if this is a parent comment
        if (!$comment->cmt_isReply_to) {
            $comment->replies()->delete();
        }

        $comment->delete();

        return redirect()->back()->with('success', 'Comment deleted successfully');
    }
} 