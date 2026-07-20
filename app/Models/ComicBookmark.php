<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComicBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'comic_id',
        'user_id',
    ];

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
