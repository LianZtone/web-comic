<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComicCommentVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'comic_comment_id',
        'voter_key',
        'vote',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(ComicComment::class, 'comic_comment_id');
    }
}
