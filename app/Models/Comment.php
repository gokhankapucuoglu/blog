<?php

namespace App\Models;

use App\Models\CommentLike;
use App\Models\CommentReport;
use App\Models\Post;
use App\Models\User;
use App\Traits\UserRelationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes, UserRelationTrait;

    const STATUS_PENDING  = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_SPAM     = 3;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'ancestry',
        'body',
        'status',
        'rejection_note',
        'moderated_by',
        'moderated_at',
        'like_count',
        'dislike_count',
        'report_count',
        'reply_count',
        'ip_address',
        'user_agent',
        'is_edited',
        'edited_at',
    ];

    protected $casts = [
        'status'        => 'integer',
        'like_count'    => 'integer',
        'dislike_count' => 'integer',
        'report_count'  => 'integer',
        'reply_count'   => 'integer',
        'is_edited'     => 'boolean',
        'moderated_at'  => 'datetime',
        'edited_at'     => 'datetime',
    ];

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING  => 'Onay Bekliyor',
            self::STATUS_APPROVED => 'Onaylandı',
            self::STATUS_REJECTED => 'Reddedildi',
            self::STATUS_SPAM     => 'Spam',
            default               => 'Bilinmiyor',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'success',
            self::STATUS_PENDING  => 'warning',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_SPAM     => 'gray',
            default               => 'gray',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'heroicon-m-check-circle',
            self::STATUS_PENDING  => 'heroicon-m-question-mark-circle',
            self::STATUS_REJECTED => 'heroicon-m-x-circle',
            self::STATUS_SPAM     => 'heroicon-m-no-symbol',
            default               => 'heroicon-m-minus-circle',
        };
    }
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class)->withTrashed();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function getDepthAttribute(): int
    {
        if (! $this->ancestry) return 0;
        return substr_count($this->ancestry, '/') + 1;
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->latest();
    }

    public function approvedReplies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
            ->where('status', self::STATUS_APPROVED)
            ->with('approvedReplies.user')
            ->oldest();
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommentReport::class);
    }
}