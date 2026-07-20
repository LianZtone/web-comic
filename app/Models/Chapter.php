<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'comic_id',
        'number',
        'title',
        'release_label',
        'summary',
        'pages',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'pages' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ChapterComment::class)->latest();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ChapterReaction::class);
    }
}
