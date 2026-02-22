<?php

namespace App\Models;

use App\Models\Category;
use App\Traits\UserRelationTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
