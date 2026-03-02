<?php

namespace App\Models;

use App\Models\Category;
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
        // Eğer durum 2 ise ve yayın tarihi gelecek bir tarihse Nullsafe operatörü ile kontrol et
        if ($this->status === 2 && $this->published_at?->isFuture()) {
            return 'Planlandı';
        }

        return match ($this->status) {
            0 => 'Taslak',
            1 => 'Onay Bekliyor',
            2 => 'Yayında',
            3 => 'Reddedildi',
            default => 'Bilinmiyor',
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
}
