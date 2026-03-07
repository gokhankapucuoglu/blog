<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Comment;
use App\Traits\UserRelationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model
{
    use SoftDeletes, HasSlug, UserRelationTrait;

    const STATUS_DRAFT      = 0;
    const STATUS_PENDING    = 1;
    const STATUS_PUBLISHED  = 2;
    const STATUS_REJECTED   = 3;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'description',
        'content',
        'image',
        'meta_title',
        'meta_description',
        'tags',
        'is_featured',
        'view_count',
        'like_count',
        'published_at',
        'status',
    ];

    protected $casts = [
        'tags'          => 'array',
        'is_featured'   => 'boolean',
        'published_at'  => 'datetime',
        'view_count'    => 'integer',
        'like_count'    => 'integer',
        'status'        => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->status === self::STATUS_PUBLISHED && $this->published_at?->isFuture()) {
            return 'Planlandı';
        }

        return match ($this->status) {
            self::STATUS_DRAFT     => 'Taslak',
            self::STATUS_PENDING   => 'Onay Bekliyor',
            self::STATUS_PUBLISHED => 'Yayında',
            self::STATUS_REJECTED  => 'Reddedildi',
            default                => 'Bilinmiyor',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_label) {
            'Yayında', 'Planlandı' => 'success',
            'Onay Bekliyor'        => 'info',
            'Reddedildi'           => 'danger',
            default                => 'gray',
        };
    }

    public function getStatusIconAttribute(): string
    {
        return match ($this->status_label) {
            'Yayında'       => 'heroicon-m-check-circle',
            'Planlandı'     => 'heroicon-m-calendar-days',
            'Onay Bekliyor' => 'heroicon-m-question-mark-circle',
            'Taslak'        => 'heroicon-m-document-text',
            'Reddedildi'    => 'heroicon-m-x-circle',
            default         => 'heroicon-m-minus-circle',
        };
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PostHistory::class)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function approvedMainComments(): HasMany
    {
        return $this->hasMany(Comment::class)
            ->where('status', Comment::STATUS_APPROVED)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc');
    }
}
