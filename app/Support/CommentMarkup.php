<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

class CommentMarkup
{
    private const TOKEN_PATTERN = '/\[(\/?)(spoiler|b|i|emoji|sticker)\]/i';

    public static function toHtml(?string $value): HtmlString
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", (string) $value);
        [$html] = self::renderSegments($normalized, 0, null, 0);

        return new HtmlString($html);
    }

    /**
     * @return array{0: string, 1: int, 2: bool}
     */
    private static function renderSegments(string $text, int $offset, ?string $untilTag, int $depth): array
    {
        if ($depth > 8) {
            return [self::escapeSegment(substr($text, $offset)), strlen($text), false];
        }

        $result = '';

        while (preg_match(self::TOKEN_PATTERN, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            $fullMatch = $matches[0][0];
            $position = $matches[0][1];
            $isClosingTag = $matches[1][0] === '/';
            $tag = strtolower($matches[2][0]);

            $result .= self::escapeSegment(substr($text, $offset, $position - $offset));
            $offset = $position + strlen($fullMatch);

            if ($isClosingTag) {
                if ($untilTag === $tag) {
                    return [$result, $offset, true];
                }

                $result .= self::escapeSegment($fullMatch);

                continue;
            }

            [$innerHtml, $nextOffset, $closed] = self::renderSegments($text, $offset, $tag, $depth + 1);

            if (! $closed) {
                $result .= self::escapeSegment($fullMatch).$innerHtml;
                $offset = $nextOffset;

                continue;
            }

            $result .= self::wrapTag($tag, $innerHtml);
            $offset = $nextOffset;
        }

        $result .= self::escapeSegment(substr($text, $offset));

        return [$result, strlen($text), false];
    }

    private static function escapeSegment(string $text): string
    {
        return nl2br(e($text), false);
    }

    private static function wrapTag(string $tag, string $content): string
    {
        $token = strtolower(trim(strip_tags(html_entity_decode(str_replace('<br>', "\n", $content), ENT_QUOTES | ENT_HTML5, 'UTF-8'))));

        return match ($tag) {
            'b' => '<strong class="font-semibold text-base-content">'.$content.'</strong>',
            'i' => '<em class="italic">'.$content.'</em>',
            'emoji' => self::renderEmojiToken($token),
            'sticker' => self::renderStickerToken($token),
            'spoiler' => <<<HTML
<details class="group my-1 inline-block max-w-full align-middle [&_summary::-webkit-details-marker]:hidden">
    <summary class="inline-flex cursor-pointer list-none items-center gap-2 rounded-lg border border-warning/30 bg-warning/10 px-2 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-base-content/65 shadow-sm transition hover:border-warning/40 hover:bg-warning/15">
        <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-warning/30 bg-warning/15 text-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 group-open:hidden" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.21.07.434 0 .644C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-3.5 w-3.5 group-open:block" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M3 3l18 18" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M10.58 10.58A3 3 0 0 0 9 12a3 3 0 0 0 5.4 1.8M6.53 6.53C4.57 7.82 3.06 9.73 2.04 11.68a1.01 1.01 0 0 0 0 .64C3.42 16.49 7.36 19.5 12 19.5c1.93 0 3.73-.52 5.28-1.43M9.88 4.68A12.2 12.2 0 0 1 12 4.5c4.64 0 8.58 3.01 9.96 7.18.07.21.07.43 0 .64a12.6 12.6 0 0 1-2.18 3.59" />
            </svg>
        </span>
        <span class="group-open:hidden">Spoiler</span>
        <span class="hidden group-open:inline">Spoiler</span>
        <span class="ml-1 inline-flex h-5 w-5 items-center justify-center text-base-content/45">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 group-open:hidden" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m9 6 6 6-6 6" />
            </svg>
            <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-3.5 w-3.5 group-open:block" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m15 18-6-6 6-6" />
            </svg>
        </span>
    </summary>
    <div class="mt-1 max-w-full bg-warning/8 px-3 py-2.5 text-sm leading-7 text-base-content/80">{$content}</div>
</details>
HTML,
            default => $content,
        };
    }

    private static function renderEmojiToken(string $token): string
    {
        $emoji = CommentDecorations::emojis()[$token] ?? null;

        if (! $emoji) {
            return self::escapeSegment("[emoji]{$token}[/emoji]");
        }

        $char = e($emoji['char']);
        $label = e($emoji['label']);

        return <<<HTML
<span class="mx-0.5 inline-flex h-8 min-w-8 items-center justify-center rounded-full border border-base-300/70 bg-base-100 text-lg shadow-sm" title="{$label}" aria-label="{$label}">
    {$char}
</span>
HTML;
    }

    private static function renderStickerToken(string $token): string
    {
        $sticker = CommentDecorations::stickers()[$token] ?? null;

        if (! $sticker) {
            return self::escapeSegment("[sticker]{$token}[/sticker]");
        }

        $toneClass = match ($sticker['tone']) {
            'error' => 'border-error/30 bg-error/10 text-error',
            'success' => 'border-success/30 bg-success/10 text-success',
            'info' => 'border-info/30 bg-info/10 text-info',
            default => 'border-warning/30 bg-warning/10 text-warning',
        };

        $emoji = e($sticker['emoji']);
        $label = e($sticker['label']);

        return <<<HTML
<span class="my-1 inline-flex items-center gap-2 rounded-2xl border px-3 py-2 text-sm font-semibold shadow-sm {$toneClass}">
    <span class="text-xl leading-none">{$emoji}</span>
    <span>{$label}</span>
</span>
HTML;
    }
}
