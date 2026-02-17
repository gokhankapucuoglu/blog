<?php

namespace App\Models;

use App\Casts\SentenceCaseCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = [
        'parent_id',
        'icon',
        'name',
        'slug',
        'description',
        'order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'description' => SentenceCaseCast::class,
            'order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function (Category $category) {
            if ($category->children()->exists()) {
                throw new \Exception('Veri Bütünlüğü Hatası: Alt kategorisi olan bir kategori silinemez.');
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->extraScope(function ($builder) {
                $builder->where('parent_id', $this->parent_id);
            });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getAllChildrenIds(): array
    {
        $ids = [];

        foreach ($this->children as $child) {
            $ids[] = $child->id;

            $ids = array_merge($ids, $child->getAllChildrenIds());
        }

        return $ids;
    }

    public function getHierarchyText(): string
    {
        $path = [];
        $current = $this;

        while ($current) {
            array_unshift($path, $current->name);

            $current = $current->parent;
        }

        return implode(' > ', $path);
    }
}
