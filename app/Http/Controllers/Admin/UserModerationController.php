<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChapterComment;
use App\Models\ComicComment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserModerationController extends Controller
{
    public function warn(Request $request, User $user): RedirectResponse
    {
        $this->ensureUserCanBeModerated($user);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $this->normalizeReason($data['reason'] ?? null);

        $user->update([
            'warning_count' => max(0, (int) $user->warning_count) + 1,
            'last_warning_reason' => $reason,
            'last_warned_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', "Peringatan dicatat untuk {$user->name}.");
    }

    public function hideComments(Request $request, User $user): RedirectResponse
    {
        $this->ensureUserCanBeModerated($user);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $this->normalizeReason($data['reason'] ?? null);
        $affected = $this->hideUserComments($user);

        $user->update([
            'hide_all_comments' => true,
            'comments_hidden_at' => now(),
            'comments_hidden_reason' => $reason,
        ]);

        return redirect()
            ->back()
            ->with('success', "Komentar {$user->name} dibatasi. {$affected} komentar disembunyikan.");
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $this->ensureUserCanBeModerated($user);

        $data = $request->validate([
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $duration = (int) ($data['duration_days'] ?? 7);
        $reason = $this->normalizeReason($data['reason'] ?? null);

        $user->update([
            'suspended_until' => now()->addDays($duration),
            'suspension_reason' => $reason,
            'banned_at' => null,
            'banned_reason' => null,
        ]);

        return redirect()
            ->back()
            ->with('success', "{$user->name} disuspend selama {$duration} hari.");
    }

    public function ban(Request $request, User $user): RedirectResponse
    {
        $this->ensureUserCanBeModerated($user);

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $reason = $this->normalizeReason($data['reason'] ?? null);
        $affected = $this->hideUserComments($user);

        $user->update([
            'hide_all_comments' => true,
            'comments_hidden_at' => now(),
            'comments_hidden_reason' => $reason,
            'suspended_until' => null,
            'suspension_reason' => null,
            'banned_at' => now(),
            'banned_reason' => $reason,
        ]);

        return redirect()
            ->back()
            ->with('success', "{$user->name} diblokir. {$affected} komentar ikut disembunyikan.");
    }

    public function clearRestrictions(User $user): RedirectResponse
    {
        $this->ensureUserCanBeModerated($user);

        $user->update([
            'hide_all_comments' => false,
            'comments_hidden_at' => null,
            'comments_hidden_reason' => null,
            'suspended_until' => null,
            'suspension_reason' => null,
            'banned_at' => null,
            'banned_reason' => null,
        ]);

        return redirect()
            ->back()
            ->with('success', "Pembatasan akun {$user->name} dibuka kembali.");
    }

    private function ensureUserCanBeModerated(User $user): void
    {
        abort_if($user->is_admin, 403, 'Akun admin tidak bisa dimoderasi dari panel ini.');
    }

    private function normalizeReason(?string $reason): string
    {
        $reason = trim((string) $reason);

        return $reason !== '' ? $reason : 'Spoiler berlebihan atau komentar yang mengganggu pengalaman baca.';
    }

    private function hideUserComments(User $user): int
    {
        $chapterCount = ChapterComment::query()
            ->where('user_id', $user->id)
            ->where('is_visible', true)
            ->update(['is_visible' => false]);

        $comicCount = ComicComment::query()
            ->where('user_id', $user->id)
            ->where('is_visible', true)
            ->update(['is_visible' => false]);

        return (int) $chapterCount + (int) $comicCount;
    }
}
