<?php

namespace App\Support;

use App\Models\Comic;
use Carbon\CarbonInterface;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ComicLibrary
{
    public static function all(): Collection
    {
        if (! self::databaseReady()) {
            return ComicCatalog::all();
        }

        $query = Comic::query()
            ->with(['chapters' => fn ($query) => $query->where('is_published', true)->orderBy('number')])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title');

        if (self::bookmarkMetricsReady()) {
            $query->withCount('bookmarks');
        }

        if (self::viewMetricsReady()) {
            $query->withCount('views');
        }

        if (self::ratingMetricsReady()) {
            $query
                ->withCount('ratings')
                ->withAvg('ratings', 'score');
        }

        $comics = $query
            ->get()
            ->filter(fn (Comic $comic) => $comic->chapters->isNotEmpty())
            ->map(fn (Comic $comic) => self::mapComic($comic))
            ->values();

        return $comics->isNotEmpty() ? $comics : ComicCatalog::all();
    }

    public static function find(?string $slug): ?array
    {
        return self::all()->firstWhere('slug', $slug);
    }

    public static function findOrFail(string $slug): array
    {
        return self::find($slug) ?? abort(404);
    }

    public static function findChapterOrFail(string $slug, int $chapterNumber): array
    {
        $comic = self::findOrFail($slug);

        $chapter = collect($comic['chapters'])->firstWhere('number', $chapterNumber);

        if (! $chapter) {
            abort(404);
        }

        return [
            'comic' => $comic,
            'chapter' => $chapter,
        ];
    }

    private static function databaseReady(): bool
    {
        try {
            return Schema::hasTable('comics') && Schema::hasTable('chapters');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private static function mapComic(Comic $comic): array
    {
        $genres = collect($comic->genres)->filter()->values()->all();
        $features = collect($comic->features)->filter()->values()->all();
        $chapters = $comic->chapters->values()->map(function ($chapter, int $index) use ($comic) {
            $pages = collect($chapter->pages)->filter()->values();
            $firstPage = $pages->first();
            $firstCaption = is_array($firstPage) ? trim((string) ($firstPage['caption'] ?? '')) : trim((string) $firstPage);
            $firstImage = is_array($firstPage) ? ComicMedia::resolveMediaPath($firstPage['image'] ?? null) : null;
            $releaseLabel = self::humanizeReleaseLabel($chapter->created_at, $chapter->release_label);

            return [
                'number' => (int) $chapter->number,
                'title' => $chapter->title,
                'release' => $releaseLabel,
                'release_label' => $releaseLabel,
                'label' => 'Chapter '.str_pad((string) $chapter->number, 2, '0', STR_PAD_LEFT),
                'summary' => $chapter->summary ?: ($firstCaption !== '' ? $firstCaption : 'Chapter baru telah dirilis.'),
                'page_count' => $pages->count(),
                'reading_time' => max(4, (int) ceil(max(1, $pages->count()) * 0.8)).' min read',
                'preview' => $firstImage ?: self::chapterPreviewSvg($comic, $chapter->title),
                'pages' => $pages->values()->map(function ($page, int $pageIndex) use ($comic, $chapter) {
                    if (is_array($page)) {
                        $caption = trim((string) ($page['caption'] ?? ''));

                        return [
                            'number' => (int) ($page['number'] ?? ($pageIndex + 1)),
                            'caption' => $caption,
                            'image' => ComicMedia::resolveMediaPath($page['image'] ?? null)
                                ?: self::readerPageSvg($comic, $chapter->title, $pageIndex + 1, $caption ?: 'Halaman '.($pageIndex + 1)),
                        ];
                    }

                    $caption = trim((string) $page);

                    return [
                        'number' => $pageIndex + 1,
                        'caption' => $caption,
                        'image' => self::readerPageSvg($comic, $chapter->title, $pageIndex + 1, $caption),
                    ];
                })->all(),
                'is_latest' => $index === $comic->chapters->count() - 1,
            ];
        })->all();

        return [
            'slug' => $comic->slug,
            'title' => $comic->title,
            'subtitle' => $comic->subtitle,
            'tagline' => $comic->tagline ?: $comic->summary,
            'summary' => $comic->summary,
            'author' => $comic->author,
            'artist' => $comic->artist ?: $comic->author,
            'status' => $comic->status,
            'comic_type' => $comic->comic_type ?: 'Manhwa',
            'source_type' => $comic->source_type ?: 'Project',
            'schedule' => $comic->schedule ?: 'TBA',
            'year' => $comic->year ?: now()->format('Y'),
            'rating' => number_format(self::ratingAverage($comic), 1),
            'rating_average' => self::ratingAverage($comic),
            'rating_count' => self::ratingCount($comic),
            'rating_count_label' => self::compactNumber(self::ratingCount($comic)),
            'views_count' => self::viewCount($comic),
            'views_label' => self::compactNumber(self::viewCount($comic)),
            'bookmarks_count' => self::bookmarkCount($comic),
            'bookmarks_label' => self::compactNumber(self::bookmarkCount($comic)),
            'next_release_time' => self::nextReleaseTime($comic->schedule),
            'genres' => $genres,
            'genre_line' => implode(' / ', $genres),
            'features' => $features,
            'cover' => ComicMedia::resolveMediaPath($comic->cover_url) ?: self::posterSvg($comic),
            'banner' => ComicMedia::resolveMediaPath($comic->banner_url) ?: self::bannerSvg($comic),
            'is_featured' => (bool) ($comic->is_featured ?? false),
            'sort_order' => (int) ($comic->sort_order ?? 0),
            'is_recommended' => (bool) ($comic->is_recommended ?? false),
            'recommended_order' => (int) ($comic->recommended_order ?? 0),
            'is_admin_pick' => (bool) ($comic->is_admin_pick ?? false),
            'admin_pick_order' => (int) ($comic->admin_pick_order ?? 0),
            'chapters' => $chapters,
            'chapter_total' => count($chapters),
            'page_total' => collect($chapters)->sum('page_count'),
            'latest_chapter' => collect($chapters)->last(),
            'first_chapter' => collect($chapters)->first(),
        ];
    }

    private static function humanizeReleaseLabel(mixed $date, ?string $fallback): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->locale(app()->getLocale())->diffForHumans();
        }

        return $fallback ?: 'Baru saja';
    }

    private static function bookmarkMetricsReady(): bool
    {
        return Schema::hasTable('comic_bookmarks');
    }

    private static function ratingMetricsReady(): bool
    {
        return Schema::hasTable('comic_ratings');
    }

    private static function viewMetricsReady(): bool
    {
        return Schema::hasTable('comic_views');
    }

    private static function ratingAverage(Comic $comic): float
    {
        $average = $comic->ratings_avg_score;

        if (is_numeric($average)) {
            return max(0, min(5, round((float) $average, 1)));
        }

        return max(0, min(5, round((float) ($comic->rating ?: 4.8), 1)));
    }

    private static function ratingCount(Comic $comic): int
    {
        if (is_numeric($comic->ratings_count)) {
            return (int) $comic->ratings_count;
        }

        return 0;
    }

    private static function bookmarkCount(Comic $comic): int
    {
        if (is_numeric($comic->bookmarks_count)) {
            return (int) $comic->bookmarks_count;
        }

        return 0;
    }

    private static function nextReleaseTime(?string $schedule): ?string
    {
        $normalized = strtolower(trim((string) $schedule));

        if ($normalized === '' || in_array($normalized, ['tba', 'tamat', 'arsip lengkap', 'tidak tentu'], true)) {
            return null;
        }

        $dayMap = [
            'senin' => Carbon::MONDAY,
            'selasa' => Carbon::TUESDAY,
            'rabu' => Carbon::WEDNESDAY,
            'kamis' => Carbon::THURSDAY,
            'jumat' => Carbon::FRIDAY,
            'sabtu' => Carbon::SATURDAY,
            'minggu' => Carbon::SUNDAY,
        ];

        $hour = match (true) {
            str_contains($normalized, 'pagi') => 9,
            str_contains($normalized, 'siang') => 13,
            str_contains($normalized, 'sore') => 16,
            str_contains($normalized, 'malam') => 20,
            default => 19,
        };

        $targetDay = collect($dayMap)->first(fn (int $value, string $label) => str_contains($normalized, $label));

        if ($targetDay === null) {
            return null;
        }

        $next = now()->copy()->startOfMinute();

        while ((int) $next->dayOfWeek !== $targetDay) {
            $next->addDay();
        }

        $next->setTime($hour, 0, 0);

        if ($next->isPast()) {
            $next->addWeek();
        }

        return $next->toIso8601String();
    }

    private static function viewCount(Comic $comic): int
    {
        if (is_numeric($comic->views_count)) {
            return (int) $comic->views_count;
        }

        return self::parseCompactNumber((string) ($comic->readers ?: '0'));
    }

    private static function compactNumber(int $value): string
    {
        if ($value >= 1000000) {
            return rtrim(rtrim(number_format($value / 1000000, 1), '0'), '.').'M';
        }

        if ($value >= 1000) {
            return rtrim(rtrim(number_format($value / 1000, 1), '0'), '.').'k';
        }

        return (string) $value;
    }

    private static function parseCompactNumber(string $value): int
    {
        $normalized = strtolower(trim($value));

        if ($normalized === '') {
            return 0;
        }

        if (preg_match('/^([\d.]+)\s*([km]?)$/', $normalized, $matches) !== 1) {
            return (int) preg_replace('/\D+/', '', $normalized);
        }

        $number = (float) $matches[1];
        $suffix = $matches[2] ?? '';

        return match ($suffix) {
            'm' => (int) round($number * 1000000),
            'k' => (int) round($number * 1000),
            default => (int) round($number),
        };
    }

    private static function posterSvg(Comic $comic): string
    {
        return self::svgDataUri(
            800,
            1000,
            '#1f2847',
            '#11131a',
            $comic->title,
            implode(' • ', array_slice($comic->genres ?? [], 0, 2))
        );
    }

    private static function bannerSvg(Comic $comic): string
    {
        return self::svgDataUri(
            1600,
            900,
            '#304f7a',
            '#11131a',
            $comic->title,
            $comic->tagline ?: $comic->summary
        );
    }

    private static function chapterPreviewSvg(Comic $comic, string $title): string
    {
        return self::svgDataUri(
            1200,
            700,
            '#314866',
            '#161923',
            $comic->title,
            $title
        );
    }

    private static function readerPageSvg(Comic $comic, string $chapterTitle, int $pageNumber, string $caption): string
    {
        return self::svgDataUri(
            1200,
            1800,
            '#1d2438',
            '#0f1117',
            $comic->title.' · '.$chapterTitle,
            'Page '.$pageNumber.' — '.$caption
        );
    }

    private static function svgDataUri(int $width, int $height, string $start, string $end, string $title, string $subtitle): string
    {
        $title = e($title);
        $subtitle = e($subtitle);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$width} {$height}">
  <defs>
    <linearGradient id="g" x1="0%" x2="100%" y1="0%" y2="100%">
      <stop offset="0%" stop-color="{$start}" />
      <stop offset="100%" stop-color="{$end}" />
    </linearGradient>
  </defs>
  <rect width="100%" height="100%" fill="url(#g)" />
  <circle cx="78%" cy="18%" r="120" fill="rgba(255,255,255,0.08)" />
  <circle cx="16%" cy="82%" r="160" fill="rgba(255,255,255,0.05)" />
  <text x="8%" y="18%" fill="rgba(255,255,255,0.72)" font-size="28" font-family="Arial, sans-serif" letter-spacing="4">SCRIPTORIA</text>
  <text x="8%" y="58%" fill="#ffffff" font-size="72" font-weight="700" font-family="Arial, sans-serif">{$title}</text>
  <text x="8%" y="66%" fill="rgba(255,255,255,0.72)" font-size="30" font-family="Arial, sans-serif">{$subtitle}</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
