<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChapterCommentVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_comment_id',
        'voter_key',
        'vote',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(ChapterComment::class, 'chapter_comment_id');
    }
}
