<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Comic;
use App\Models\ComicComment;
use App\Support\ComicLibrary;
use App\Support\ComicMedia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ComicApiController extends Controller
{
    public function home(): JsonResponse
    {
        $comics = ComicLibrary::all();
        $homeRecommendationFormats = ['Manhwa', 'Manga', 'Manhua'];

        $recommendations = collect($homeRecommendationFormats)
            ->flatMap(function (string $format) use ($comics) {
                $items = $comics
                    ->filter(fn (array $comic) => strcasecmp((string) ($comic['comic_type'] ?? ''), $format) === 0)
                    ->values();

                if ($items->isEmpty()) {
                    return collect();
                }

                return $this->buildRecommendations($items, 4);
            })
            ->values();

        $recommendationTypes = collect($homeRecommendationFormats)
            ->filter(fn (string $format) => $recommendations->contains(
                fn (array $comic) => strcasecmp((string) ($comic['comic_type'] ?? ''), $format) === 0
            ))
            ->values();

        return response()->json([
            'data' => [
                'featured' => $comics->first(),
                'latest_updates' => $this->buildLatestUpdates($comics),
                'recommendations' => $recommendations->values(),
                'recommendation_types' => $recommendationTypes,
                'shelves' => $this->buildShelves($comics),
            ],
        ]);
    }

    public function explore(): JsonResponse
    {
        $comics = ComicLibrary::all();

        return response()->json([
            'data' => [
                'updates' => $this->buildExploreUpdates($comics),
                'recommendations' => $this->buildRecommendations($comics),
                'admin_picks' => $this->buildAdminPicks($comics, 6),
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $comics = ComicLibrary::all();
        $search = trim((string) $request->string('q'));
        $status = trim((string) $request->string('status'));
        $type = trim((string) $request->string('type'));
        $orderBy = trim((string) $request->string('order_by'));

        $genres = collect($request->input('genres', []));

        if (is_string($request->input('genres'))) {
            $genres = collect(explode(',', (string) $request->input('genres')));
        }

        $genres = $genres
            ->map(fn ($genre) => trim((string) $genre))
            ->filter()
            ->unique()
            ->values();

        $baseFiltered = $comics->filter(function (array $comic) use ($search, $status, $type) {
            $matchesSearch = $search === '' || str_contains(strtolower($comic['title'].' '.$comic['summary'].' '.$comic['author']), strtolower($search));
            $matchesStatus = $status === '' || $comic['status'] === $status;
            $matchesType = $type === '' || ($comic['comic_type'] ?? '') === $type;

            return $matchesSearch && $matchesStatus && $matchesType;
        })->values();

        $filtered = $baseFiltered->filter(function (array $comic) use ($genres) {
            if ($genres->isEmpty()) {
                return true;
            }

            return $genres->every(fn (string $genre) => in_array($genre, $comic['genres'], true));
        })->values();

        $sorted = $this->sortComics($filtered, $orderBy);

        $page = max(1, (int) $request->integer('page', 1));
        $perPage = max(1, min(50, (int) $request->integer('per_page', 12)));
        $total = $sorted->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);
        $from = (($page - 1) * $perPage) + 1;
        $to = min($page * $perPage, $total);

        return response()->json([
            'data' => $sorted
                ->forPage($page, $perPage)
                ->values(),
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'from' => $total > 0 ? $from : null,
                'to' => $total > 0 ? $to : null,
                'filters' => [
                    'q' => $search,
                    'genres' => $genres->all(),
                    'status' => $status,
                    'type' => $type,
                    'order_by' => $orderBy,
                ],
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $comic = ComicLibrary::find($slug);

        if (! $comic) {
            return response()->json([
                'message' => 'Comic not found.',
            ], 404);
        }

        $related = ComicLibrary::all()
            ->reject(fn (array $item) => $item['slug'] === $comic['slug'])
            ->filter(fn (array $item) => count(array_intersect($item['genres'], $comic['genres'])) > 0)
            ->take(3)
            ->values();

        return response()->json([
            'data' => $comic,
            'related' => $related,
        ]);
    }

    public function showChapter(string $slug, int $chapter): JsonResponse
    {
        $comic = ComicLibrary::find($slug);

        if (! $comic) {
            return response()->json([
                'message' => 'Comic not found.',
            ], 404);
        }

        $chapters = collect($comic['chapters'])->values();
        $chapterIndex = $chapters->search(fn (array $item) => (int) $item['number'] === $chapter);

        if ($chapterIndex === false) {
            return response()->json([
                'message' => 'Chapter not found.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'comic' => [
                    'slug' => $comic['slug'],
                    'title' => $comic['title'],
                    'subtitle' => $comic['subtitle'],
                    'cover' => $comic['cover'],
                    'banner' => $comic['banner'],
                ],
                'chapter' => $chapters[$chapterIndex],
                'previous_chapter' => $chapterIndex > 0 ? $chapters[$chapterIndex - 1] : null,
                'next_chapter' => $chapterIndex < $chapters->count() - 1 ? $chapters[$chapterIndex + 1] : null,
            ],
        ]);
    }

    public function comicFeedback(Request $request, string $slug): JsonResponse
    {
        $comic = Comic::query()->where('slug', $slug)->first();

        if (! $comic) {
            return response()->json([
                'message' => 'Comic not found.',
            ], 404);
        }

        $commentsReady = Schema::hasTable('comic_comments');
        $votesReady = $commentsReady && Schema::hasTable('comic_comment_votes');
        $repliesReady = $commentsReady && Schema::hasColumn('comic_comments', 'parent_id');
        $spoilersReady = $commentsReady && Schema::hasColumn('comic_comments', 'is_spoiler');
        $imagesReady = $commentsReady && Schema::hasColumn('comic_comments', 'image_path');
        $reactionsReady = Schema::hasTable('comic_reactions');

        $sort = $this->normalizeSort((string) $request->string('comment_sort'));
        $comments = collect();
        $total = 0;

        if ($commentsReady) {
            $query = ComicComment::query()
                ->where('comic_id', $comic->id)
                ->where('is_visible', true);

            $total = (clone $query)->count();

            if ($sort === 'oldest') {
                $query->oldest();
            } else {
                $query->latest();
            }

            if ($votesReady) {
                $query->with('votes');
            }

            $flatComments = $query->get()->values();
            $parentLookup = $flatComments->pluck('parent_id', 'id')->all();
            $reactorKey = $this->reactorKey($request);
            $currentUserId = (int) $request->user()?->id;

            $mapComment = function (ComicComment $comment, array $overrides = []) use ($currentUserId, $imagesReady, $spoilersReady, $votesReady, $reactorKey, $parentLookup) {
                $likeCount = (int) $comment->likes_count;
                $dislikeCount = 0;
                $userVote = null;

                if ($votesReady && $comment->relationLoaded('votes')) {
                    $likeCount = $comment->votes->where('vote', 'like')->count();
                    $dislikeCount = $comment->votes->where('vote', 'dislike')->count();
                    $userVote = $comment->votes->firstWhere('voter_key', $reactorKey)?->vote;
                }

                return [
                    'id' => $comment->id,
                    'parent_id' => $comment->parent_id,
                    'root_id' => $overrides['root_id'] ?? $this->resolveRootId($comment->id, $comment->parent_id, $parentLookup),
                    'depth' => $overrides['depth'] ?? ($comment->parent_id ? 1 : 0),
                    'is_edited' => $comment->updated_at !== null
                        && $comment->created_at !== null
                        && $comment->updated_at->gt($comment->created_at),
                    'can_manage' => (int) $comment->user_id === $currentUserId,
                    'name' => $comment->display_name,
                    'time' => optional($comment->created_at)?->diffForHumans() ?? 'Baru saja',
                    'text' => $comment->body,
                    'image_url' => $imagesReady ? ComicMedia::resolveMediaPath($comment->image_path) : null,
                    'is_spoiler' => $spoilersReady ? (bool) $comment->is_spoiler : false,
                    'score' => (int) $comment->score,
                    'like_count' => $likeCount,
                    'dislike_count' => $dislikeCount,
                    'user_vote' => $userVote,
                    'replies' => [],
                ];
            };

            if ($repliesReady) {
                $topLevel = $flatComments->whereNull('parent_id')->values();
                $repliesByRoot = $flatComments
                    ->whereNotNull('parent_id')
                    ->groupBy(fn (ComicComment $item) => $this->resolveRootId($item->id, $item->parent_id, $parentLookup));

                $comments = $topLevel->map(function (ComicComment $comment) use ($mapComment, $repliesByRoot) {
                    $payload = $mapComment($comment, [
                        'root_id' => $comment->id,
                        'depth' => 0,
                    ]);
                    $payload['replies'] = collect($repliesByRoot->get($comment->id, []))
                        ->sortBy(fn (ComicComment $reply) => $reply->created_at?->getTimestamp() ?? 0)
                        ->map(fn (ComicComment $reply) => $mapComment($reply, [
                            'root_id' => $comment->id,
                            'depth' => 1,
                        ]))
                        ->values()
                        ->all();

                    return $payload;
                })->values();
            } else {
                $comments = $flatComments->map(fn (ComicComment $comment) => $mapComment($comment))->values();
            }
        }

        return response()->json([
            'data' => [
                'comic' => [
                    'slug' => $comic->slug,
                    'title' => $comic->title,
                ],
                'sort' => $sort,
                'comments_total' => $total,
                'comments' => $comments,
                'reactions' => $reactionsReady
                    ? $this->reactionSummary($comic->reactions()->get(['id', 'type', 'reactor_key']), $this->reactorKey($request))
                    : [],
            ],
        ]);
    }

    public function chapterFeedback(Request $request, string $slug, int $chapter): JsonResponse
    {
        $chapterModel = Chapter::query()
            ->where('number', $chapter)
            ->whereHas('comic', fn ($query) => $query->where('slug', $slug))
            ->with('comic:id,slug,title')
            ->first();

        if (! $chapterModel) {
            return response()->json([
                'message' => 'Chapter not found.',
            ], 404);
        }

        $commentsReady = Schema::hasTable('chapter_comments');
        $votesReady = $commentsReady && Schema::hasTable('chapter_comment_votes');
        $repliesReady = $commentsReady && Schema::hasColumn('chapter_comments', 'parent_id');
        $spoilersReady = $commentsReady && Schema::hasColumn('chapter_comments', 'is_spoiler');
        $imagesReady = $commentsReady && Schema::hasColumn('chapter_comments', 'image_path');
        $reactionsReady = Schema::hasTable('chapter_reactions');

        $sort = $this->normalizeSort((string) $request->string('comment_sort'));
        $comments = collect();
        $total = 0;

        if ($commentsReady) {
            $query = ChapterComment::query()
                ->where('chapter_id', $chapterModel->id)
                ->where('is_visible', true);

            $total = (clone $query)->count();

            if ($sort === 'oldest') {
                $query->oldest();
            } else {
                $query->latest();
            }

            if ($votesReady) {
                $query->with('votes');
            }

            $flatComments = $query->get()->values();
            $parentLookup = $flatComments->pluck('parent_id', 'id')->all();
            $reactorKey = $this->reactorKey($request);
            $currentUserId = (int) $request->user()?->id;

            $mapComment = function (ChapterComment $comment, array $overrides = []) use ($currentUserId, $imagesReady, $spoilersReady, $votesReady, $reactorKey, $parentLookup) {
                $likeCount = (int) $comment->likes_count;
                $dislikeCount = 0;
                $userVote = null;

                if ($votesReady && $comment->relationLoaded('votes')) {
                    $likeCount = $comment->votes->where('vote', 'like')->count();
                    $dislikeCount = $comment->votes->where('vote', 'dislike')->count();
                    $userVote = $comment->votes->firstWhere('voter_key', $reactorKey)?->vote;
                }

                return [
                    'id' => $comment->id,
                    'parent_id' => $comment->parent_id,
                    'root_id' => $overrides['root_id'] ?? $this->resolveRootId($comment->id, $comment->parent_id, $parentLookup),
                    'depth' => $overrides['depth'] ?? ($comment->parent_id ? 1 : 0),
                    'is_edited' => $comment->updated_at !== null
                        && $comment->created_at !== null
                        && $comment->updated_at->gt($comment->created_at),
                    'can_manage' => (int) $comment->user_id === $currentUserId,
                    'name' => $comment->display_name,
                    'time' => optional($comment->created_at)?->diffForHumans() ?? 'Baru saja',
                    'text' => $comment->body,
                    'image_url' => $imagesReady ? ComicMedia::resolveMediaPath($comment->image_path) : null,
                    'is_spoiler' => $spoilersReady ? (bool) $comment->is_spoiler : false,
                    'like_count' => $likeCount,
                    'dislike_count' => $dislikeCount,
                    'user_vote' => $userVote,
                    'replies' => [],
                ];
            };

            if ($repliesReady) {
                $topLevel = $flatComments->whereNull('parent_id')->values();
                $repliesByRoot = $flatComments
                    ->whereNotNull('parent_id')
                    ->groupBy(fn (ChapterComment $item) => $this->resolveRootId($item->id, $item->parent_id, $parentLookup));

                $comments = $topLevel->map(function (ChapterComment $comment) use ($mapComment, $repliesByRoot) {
                    $payload = $mapComment($comment, [
                        'root_id' => $comment->id,
                        'depth' => 0,
                    ]);
                    $payload['replies'] = collect($repliesByRoot->get($comment->id, []))
                        ->sortBy(fn (ChapterComment $reply) => $reply->created_at?->getTimestamp() ?? 0)
                        ->map(fn (ChapterComment $reply) => $mapComment($reply, [
                            'root_id' => $comment->id,
                            'depth' => 1,
                        ]))
                        ->values()
                        ->all();

                    return $payload;
                })->values();
            } else {
                $comments = $flatComments->map(fn (ChapterComment $comment) => $mapComment($comment))->values();
            }
        }

        return response()->json([
            'data' => [
                'comic' => [
                    'slug' => $chapterModel->comic?->slug,
                    'title' => $chapterModel->comic?->title,
                ],
                'chapter' => [
                    'number' => (int) $chapterModel->number,
                    'title' => $chapterModel->title,
                ],
                'sort' => $sort,
                'comments_total' => $total,
                'comments' => $comments,
                'reactions' => $reactionsReady
                    ? $this->reactionSummary($chapterModel->reactions()->get(['id', 'type', 'reactor_key']), $this->reactorKey($request))
                    : [],
            ],
        ]);
    }

    private function sortComics(Collection $comics, string $orderBy): Collection
    {
        return match ($orderBy) {
            'popular' => $comics->sortByDesc(fn (array $comic) => [
                (int) ($comic['views_count'] ?? 0),
                (int) ($comic['bookmarks_count'] ?? 0),
                (float) ($comic['rating_average'] ?? 0),
            ])->values(),
            'bookmarks' => $comics->sortByDesc(fn (array $comic) => [
                (int) ($comic['bookmarks_count'] ?? 0),
                (int) ($comic['views_count'] ?? 0),
                (float) ($comic['rating_average'] ?? 0),
            ])->values(),
            'rating' => $comics->sortByDesc(fn (array $comic) => [
                (float) ($comic['rating_average'] ?? 0),
                (int) ($comic['rating_count'] ?? 0),
                (int) ($comic['views_count'] ?? 0),
            ])->values(),
            'latest_update' => $comics->sortByDesc(fn (array $comic) => [
                (int) ($comic['latest_chapter']['number'] ?? 0),
                (bool) ($comic['is_featured'] ?? false),
                (int) ($comic['views_count'] ?? 0),
            ])->values(),
            'title_asc' => $comics->sortBy(fn (array $comic) => strtolower($comic['title']))->values(),
            'title_desc' => $comics->sortByDesc(fn (array $comic) => strtolower($comic['title']))->values(),
            default => $comics->sortByDesc(fn (array $comic) => [
                (int) ($comic['latest_chapter']['number'] ?? 0),
                (bool) ($comic['is_featured'] ?? false),
                (int) ($comic['views_count'] ?? 0),
            ])->values(),
        };
    }

    private function buildLatestUpdates(Collection $comics, int $limit = 4): Collection
    {
        return $comics
            ->map(fn (array $comic) => [
                'comic' => $comic,
                'chapter' => $comic['latest_chapter'],
            ])
            ->sortByDesc(fn (array $item) => $item['chapter']['number'])
            ->values()
            ->take($limit);
    }

    private function buildShelves(Collection $comics): array
    {
        return [
            [
                'title' => 'Trending This Week',
                'caption' => 'Judul yang paling siap jadi pintu masuk pembaca baru.',
                'items' => $comics->take(3)->values(),
            ],
            [
                'title' => 'Binge Worthy',
                'caption' => 'Komik completed atau seasonal yang enak dibaca maraton.',
                'items' => $comics->whereIn('status', ['Completed', 'Seasonal'])->take(5)->values(),
            ],
        ];
    }

    private function buildExploreUpdates(Collection $comics, int $limit = 12): Collection
    {
        return $comics
            ->map(function (array $comic) {
                return [
                    'comic' => $comic,
                    'chapter' => $comic['latest_chapter'],
                    'type' => $comic['comic_type'] ?? 'Manhwa',
                    'source' => strtolower($comic['source_type'] ?? 'Project'),
                ];
            })
            ->sortByDesc(fn (array $item) => $item['chapter']['number'])
            ->values()
            ->take($limit);
    }

    private function buildRecommendations(Collection $comics, int $limit = 5): Collection
    {
        $curated = $comics
            ->filter(fn (array $comic) => (bool) ($comic['is_recommended'] ?? false))
            ->sort(function (array $left, array $right) {
                return (($left['recommended_order'] ?? 0) <=> ($right['recommended_order'] ?? 0))
                    ?: ((float) ($right['rating'] ?? 0) <=> (float) ($left['rating'] ?? 0))
                    ?: (($left['title'] ?? '') <=> ($right['title'] ?? ''));
            })
            ->values();

        return ($curated->isNotEmpty() ? $curated : $comics->sortByDesc(fn (array $comic) => (float) $comic['rating'])->values())
            ->take($limit)
            ->values();
    }

    private function buildAdminPicks(Collection $comics, int $limit = 4): Collection
    {
        $curated = $comics
            ->filter(fn (array $comic) => (bool) ($comic['is_admin_pick'] ?? false))
            ->sort(function (array $left, array $right) {
                return (($left['admin_pick_order'] ?? 0) <=> ($right['admin_pick_order'] ?? 0))
                    ?: ((float) ($right['rating'] ?? 0) <=> (float) ($left['rating'] ?? 0))
                    ?: (($left['title'] ?? '') <=> ($right['title'] ?? ''));
            })
            ->values();

        return ($curated->isNotEmpty() ? $curated : $comics)
            ->take($limit)
            ->values();
    }

    private function normalizeSort(?string $sort): string
    {
        $normalized = trim((string) $sort);

        return in_array($normalized, ['popular', 'newest', 'oldest'], true) ? $normalized : 'newest';
    }

    private function resolveRootId(int $id, ?int $parentId, array $parentLookup): int
    {
        $rootId = $id;

        while ($parentId !== null) {
            $rootId = (int) $parentId;
            $parentId = $parentLookup[$parentId] ?? null;
        }

        return $rootId;
    }

    private function reactorKey(Request $request): string
    {
        if ($request->user()) {
            return 'user:'.$request->user()->getAuthIdentifier();
        }

        $headerKey = trim((string) $request->header('X-Reader-Key'));

        if ($headerKey !== '') {
            return 'guest:'.$headerKey;
        }

        if (method_exists($request, 'hasSession') && $request->hasSession()) {
            $existing = $request->session()->get('reader_reactor_key');

            if (is_string($existing) && $existing !== '') {
                return $existing;
            }

            $generated = (string) Str::uuid();
            $request->session()->put('reader_reactor_key', $generated);

            return $generated;
        }

        return 'guest:'.hash('sha256', (string) $request->ip().'|'.(string) $request->userAgent());
    }

    private function reactionSummary(Collection $reactions, string $reactorKey): array
    {
        $definitions = [
            'like' => 'Suka',
            'hype' => '🔥 Hype',
            'sad' => '😭 Sedih',
            'twist' => '🤯 Twist',
        ];

        $counts = $reactions
            ->groupBy('type')
            ->map(fn ($items) => $items->count());

        $active = $reactions
            ->where('reactor_key', $reactorKey)
            ->pluck('type')
            ->all();

        return collect($definitions)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
            'count' => (int) ($counts[$key] ?? 0),
            'active' => in_array($key, $active, true),
        ])->values()->all();
    }
}
