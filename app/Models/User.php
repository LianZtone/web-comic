<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'warning_count',
        'last_warning_reason',
        'last_warned_at',
        'hide_all_comments',
        'comments_hidden_at',
        'comments_hidden_reason',
        'suspended_until',
        'suspension_reason',
        'banned_at',
        'banned_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'warning_count' => 'integer',
            'last_warned_at' => 'datetime',
            'hide_all_comments' => 'boolean',
            'comments_hidden_at' => 'datetime',
            'suspended_until' => 'datetime',
            'banned_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasActiveSuspension(): bool
    {
        return $this->suspended_until !== null && $this->suspended_until->isFuture();
    }

    public function isBanned(): bool
    {
        return $this->banned_at !== null;
    }

    public function hasCommentRestriction(): bool
    {
        return (bool) $this->hide_all_comments;
    }

    public function hasCommunityRestriction(): bool
    {
        return $this->isBanned() || $this->hasActiveSuspension();
    }

    public function comicBookmarks(): HasMany
    {
        return $this->hasMany(ComicBookmark::class);
    }

    public function comicRatings(): HasMany
    {
        return $this->hasMany(ComicRating::class);
    }

    public function chapterComments(): HasMany
    {
        return $this->hasMany(ChapterComment::class);
    }

    public function comicComments(): HasMany
    {
        return $this->hasMany(ComicComment::class);
    }
}
