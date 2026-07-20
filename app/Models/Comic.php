<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'tagline',
        'summary',
        'author',
        'artist',
        'status',
        'comic_type',
        'source_type',
        'schedule',
        'year',
        'rating',
        'readers',
        'genres',
        'features',
        'cover_url',
        'banner_url',
        'is_featured',
        'sort_order',
        'is_recommended',
        'recommended_order',
        'is_admin_pick',
        'admin_pick_order',
    ];

    protected function casts(): array
    {
        return [
            'genres' => 'array',
            'features' => 'array',
            'rating' => 'decimal:1',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'is_recommended' => 'boolean',
            'recommended_order' => 'integer',
            'is_admin_pick' => 'boolean',
            'admin_pick_order' => 'integer',
        ];
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('number');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(ComicBookmark::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(ComicRating::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ComicComment::class)->latest();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ComicReaction::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ComicView::class);
    }
}
