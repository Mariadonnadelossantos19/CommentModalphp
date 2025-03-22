<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Funding extends Model
{
    protected $table = 'your_funding_table';

    public function comments()
    {
        return $this->hasMany(Comment::class, 'cmt_fnd_id');
    }
} 