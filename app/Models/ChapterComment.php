<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChapterComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'user_id',
        'parent_id',
        'display_name',
        'body',
        'image_path',
        'is_spoiler',
        'likes_count',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'is_spoiler' => 'boolean',
            'likes_count' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
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
        return $this->hasMany(ChapterCommentVote::class);
    }
}
