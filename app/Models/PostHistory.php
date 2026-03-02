<?php

namespace App\Models;

use App\Traits\UserRelationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostHistory extends Model
{
    use UserRelationTrait;

    protected $fillable = [
        'post_id',
        'user_id',
        'action',
        'description',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class)->withTrashed();
    }
}
