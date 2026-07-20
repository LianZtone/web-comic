<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminTwoFactor
{
    private const SESSION_KEY = 'admin_two_factor';
    public const EXPIRES_AFTER_MINUTES = 10;

    public static function begin(Request $request, User $user, bool $remember = false): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put(self::SESSION_KEY, [
            'user_id' => $user->id,
            'remember' => $remember,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::EXPIRES_AFTER_MINUTES)->timestamp,
        ]);

        return $code;
    }

    public static function pendingUser(Request $request): ?User
    {
        $challenge = self::challenge($request);

        if (! is_array($challenge)) {
            return null;
        }

        $user = User::query()->find($challenge['user_id'] ?? null);

        if (! $user?->is_admin) {
            self::clear($request);

            return null;
        }

        return $user;
    }

    public static function remember(Request $request): bool
    {
        return (bool) (self::challenge($request)['remember'] ?? false);
    }

    public static function verify(Request $request, ?string $code): User
    {
        $challenge = self::challenge($request);

        if (! is_array($challenge)) {
            throw ValidationException::withMessages([
                'code' => 'Sesi verifikasi admin tidak ditemukan. Ulangi login admin.',
            ]);
        }

        if ((int) ($challenge['expires_at'] ?? 0) < now()->timestamp) {
            self::clear($request);

            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi admin sudah kedaluwarsa. Minta kode baru.',
            ]);
        }

        $user = self::pendingUser($request);

        if (! $user) {
            throw ValidationException::withMessages([
                'code' => 'Akun admin untuk verifikasi tidak valid. Ulangi login admin.',
            ]);
        }

        if (! Hash::check(trim((string) $code), (string) ($challenge['code_hash'] ?? ''))) {
            throw ValidationException::withMessages([
                'code' => 'Kode verifikasi admin tidak cocok.',
            ]);
        }

        return $user;
    }

    public static function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    private static function challenge(Request $request): mixed
    {
        return $request->session()->get(self::SESSION_KEY);
    }
}
