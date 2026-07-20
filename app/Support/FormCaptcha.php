<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FormCaptcha
{
    private const SESSION_KEY = 'form_captcha';
    private const TTL_SECONDS = 900;

    public static function question(Request $request, string $key): string
    {
        $challenge = $request->session()->get(self::sessionPath($key));

        if (! self::isValidChallenge($challenge)) {
            $challenge = self::regenerate($request, $key);
        }

        return (string) $challenge['question'];
    }

    public static function regenerate(Request $request, string $key): array
    {
        $left = random_int(1, 9);
        $right = random_int(1, 9);

        if (random_int(0, 1) === 1) {
            [$left, $right] = [max($left, $right), min($left, $right)];
            $operator = '-';
            $answer = $left - $right;
        } else {
            $operator = '+';
            $answer = $left + $right;
        }

        $challenge = [
            'question' => "{$left} {$operator} {$right} = ?",
            'answer' => (string) $answer,
            'generated_at' => now()->timestamp,
        ];

        $request->session()->put(self::sessionPath($key), $challenge);

        return $challenge;
    }

    public static function validate(Request $request, string $key, ?string $answer, string $field = 'captcha_answer'): void
    {
        $challenge = $request->session()->get(self::sessionPath($key));

        if (! self::isValidChallenge($challenge)) {
            self::regenerate($request, $key);

            throw ValidationException::withMessages([
                $field => 'CAPTCHA sudah kedaluwarsa. Coba jawab tantangan yang baru.',
            ]);
        }

        $normalized = trim((string) preg_replace('/\s+/', '', (string) $answer));
        $isCorrect = $normalized !== '' && hash_equals((string) $challenge['answer'], $normalized);

        self::regenerate($request, $key);

        if (! $isCorrect) {
            throw ValidationException::withMessages([
                $field => 'Jawaban CAPTCHA tidak cocok.',
            ]);
        }
    }

    private static function sessionPath(string $key): string
    {
        return self::SESSION_KEY.'.'.$key;
    }

    private static function isValidChallenge(mixed $challenge): bool
    {
        return is_array($challenge)
            && isset($challenge['question'], $challenge['answer'], $challenge['generated_at'])
            && is_string($challenge['question'])
            && is_string($challenge['answer'])
            && is_numeric($challenge['generated_at'])
            && ((int) $challenge['generated_at'] + self::TTL_SECONDS) >= now()->timestamp;
    }
}
