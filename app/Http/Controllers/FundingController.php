<?php

namespace App\Http\Controllers;

use App\Models\Funding;
use App\Models\Comment;
use Illuminate\Http\Request;

class FundingController extends Controller
{
    public function show($funding_id)
    {
        // Get the funding details
        $funding = Funding::findOrFail($funding_id);
        
        // Get comments for this funding
        $comments = Comment::where('cmt_fnd_id', $funding_id)
            ->whereNull('cmt_isReply_to')  // Get only parent comments
            ->with(['user', 'replies.user'])  // Eager load relationships
            ->orderBy('created_at', 'desc')
            ->get();

        return view('comments', compact('comments', 'funding_id'));
    }
} 