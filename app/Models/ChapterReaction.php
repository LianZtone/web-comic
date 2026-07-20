<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterReaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'type',
        'reactor_key',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
