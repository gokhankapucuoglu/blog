<?php

namespace App\Models;

use App\Models\Comment;
use App\Traits\UserRelationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReport extends Model
{
    use UserRelationTrait;

    const STATUS_PENDING   = 0;
    const STATUS_REVIEWED  = 1;
    const STATUS_DISMISSED = 2;

    const REASONS = [
        'spam'           => 'Spam',
        'hate_speech'    => 'Nefret Söylemi',
        'harassment'     => 'Taciz / Hakaret',
        'misinformation' => 'Yanlış Bilgi',
        'off_topic'      => 'Konu Dışı',
        'other'          => 'Diğer',
    ];

    protected $fillable = [
        'comment_id',
        'user_id',
        'reason',
        'note',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function getReasonLabelAttribute(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Bekliyor',
            self::STATUS_REVIEWED  => 'İşleme Alındı',
            self::STATUS_DISMISSED => 'Reddedildi',
            default                => 'Bilinmiyor',
        };
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
