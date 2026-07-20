<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

class TextSanitizer
{
    private const LINK_PATTERN = '/(?:https?:\/\/|www\.|(?:[a-z0-9-]+\.)+(?:com|net|org|info|biz|xyz|click|site|online|top|vip|bet|casino|id)\b)/iu';

    private const SPAM_PATTERN = '/(?:slot\s*gacor|judi\s*online|casino|togel|pragmatic|maxwin|scatter|rtp\s*slot|link\s*alternatif|situs\s*judi|bandar\s*slot)/iu';

    public static function plain(?string $value, bool $preserveNewlines = false): string
    {
        if (! is_string($value)) {
            return '';
        }

        $sanitized = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $sanitized = strip_tags($sanitized);
        $sanitized = preg_replace('/[^\P{C}\n\t]+/u', '', $sanitized) ?? '';
        $sanitized = str_replace(["\r\n", "\r"], "\n", $sanitized);

        if ($preserveNewlines) {
            $sanitized = collect(explode("\n", $sanitized))
                ->map(function (string $line): string {
                    return trim((string) preg_replace('/[ \t]+/u', ' ', $line));
                })
                ->implode("\n");

            $sanitized = preg_replace("/\n{3,}/", "\n\n", $sanitized) ?? '';
        } else {
            $sanitized = preg_replace('/\s+/u', ' ', $sanitized) ?? '';
        }

        return trim($sanitized);
    }

    /**
     * @return array<int, string>
     */
    public static function lines(?string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $value))
            ->map(fn (?string $line) => self::plain($line))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, string|array<int, string>|null>  $fields
     */
    public static function ensureNoSpam(array $fields, string $message = 'Konten mengandung link atau kata yang terindikasi spam. Hapus dulu sebelum menyimpan.'): void
    {
        $errors = [];

        foreach ($fields as $field => $value) {
            $texts = is_array($value) ? $value : [$value];

            foreach ($texts as $text) {
                if (! is_string($text) || $text === '') {
                    continue;
                }

                if (self::containsSpam($text)) {
                    $errors[$field] = $message;
                    break;
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    public static function containsSpam(string $value): bool
    {
        $value = self::plain($value, true);

        return preg_match(self::LINK_PATTERN, $value) === 1
            || preg_match(self::SPAM_PATTERN, $value) === 1;
    }
}
