<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'tblcomments';
    protected $primaryKey = 'cmt_id';
    
    protected $fillable = [
        'cmt_fnd_id',
        'cmt_content',
        'cmt_attachment',
        'cmt_added_by',
        'cmt_isReply_to',
        'cmt_isOpened',
        'cmt_isArchived'
    ];

    protected $with = ['user', 'replies']; // Eager load relationships by default

    // Relationship with User model
    public function user()
    {
        return $this->belongsTo(User::class, 'cmt_added_by');
    }

    // Get replies for this comment
    public function replies()
    {
        return $this->hasMany(Comment::class, 'cmt_isReply_to', 'cmt_id')
                    ->orderBy('created_at', 'asc');
    }

    // Get parent comment if this is a reply
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'cmt_isReply_to', 'cmt_id');
    }

    // Helper method to check if comment is a reply
    public function isReply()
    {
        return !is_null($this->cmt_isReply_to);
    }
} 