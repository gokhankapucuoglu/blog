<?php

namespace App\Models;

use App\Models\Comment;
use App\Traits\UserRelationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentLike extends Model
{
    use UserRelationTrait;

    const LIKE    =  1;
    const DISLIKE = -1;

    protected $fillable = [
        'comment_id',
        'user_id',
        'type',
    ];

    protected $casts = [
        'type' => 'integer',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}