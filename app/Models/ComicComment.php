<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComicComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'comic_id',
        'user_id',
        'parent_id',
        'display_name',
        'score',
        'body',
        'image_path',
        'is_spoiler',
        'likes_count',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'is_spoiler' => 'boolean',
            'likes_count' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ComicCommentVote::class);
    }
}
