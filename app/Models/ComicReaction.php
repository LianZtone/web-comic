<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComicReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'comic_id',
        'type',
        'reactor_key',
    ];

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }
}
