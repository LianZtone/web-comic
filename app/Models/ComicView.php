<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComicView extends Model
{
    use HasFactory;

    protected $fillable = [
        'comic_id',
        'chapter_id',
        'user_id',
        'viewer_key',
        'viewed_on',
    ];

    protected function casts(): array
    {
        return [
            'chapter_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
