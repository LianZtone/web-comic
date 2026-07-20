<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use App\Models\ComicBookmark;
use App\Models\ComicComment;
use App\Models\ComicRating;
use App\Support\ComicLibrary;
use App\Support\ComicMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ComicPageController extends Controller
{
    public function show(Request $request, string $slug): View
    {
        $comic = ComicLibrary::findOrFail($slug);
        $seriesFeedbackSort = $this->normalizeCommentSort($request);

        $related = ComicLibrary::all()
            ->reject(fn (array $item) => $item['slug'] === $comic['slug'])
            ->filter(fn (array $item) => count(array_intersect($item['genres'], $comic['genres'])) > 0)
            ->take(3)
            ->values();

        try {
            $databaseComic = Schema::hasTable('comics')
                ? Comic::query()->where('slug', $slug)->first()
                : null;
            $bookmarkReady = Schema::hasTable('comic_bookmarks');
            $ratingReady = Schema::hasTable('comic_ratings');
        } catch (\Throwable) {
            $databaseComic = null;
            $bookmarkReady = false;
            $ratingReady = false;
        }

        $userBookmark = false;
        $userRating = null;
        $seriesFeedbackReady = false;
        $seriesFeedbackVotesReady = false;
        $seriesFeedbackRepliesReady = false;
        $seriesFeedbackSpoilersReady = false;
        $seriesFeedbackImagesReady = false;
        $seriesFeedbackReactionsReady = false;
        $seriesFeedbackTotal = 0;
        $seriesFeedbackReactions = collect();
        $seriesFeedbackStats = collect([
            [
                'label' => 'Rating umum',
                'value' => number_format($comic['rating_average'], 1).'/5',
                'description' => 'Rata-rata penilaian seri saat ini.',
            ],
            [
                'label' => 'Bookmark',
                'value' => $comic['bookmarks_label'],
                'description' => 'Reader yang menyimpan seri ini.',
            ],
            [
                'label' => 'Views',
                'value' => $comic['views_label'],
                'description' => 'Minat pembaca pada halaman seri.',
            ],
        ]);
        $seriesFeedback = collect();

        if ($request->user() && $databaseComic && $bookmarkReady) {
            $userBookmark = ComicBookmark::query()
                ->where('comic_id', $databaseComic->id)
                ->where('user_id', $request->user()->id)
                ->exists();
        }

        if ($request->user() && $databaseComic && $ratingReady) {
            $userRating = ComicRating::query()
                ->where('comic_id', $databaseComic->id)
                ->where('user_id', $request->user()->id)
                ->value('score');
        }

        if ($databaseComic) {
            try {
                $seriesFeedbackReady = Schema::hasTable('comic_comments');
                $seriesFeedbackVotesReady = $seriesFeedbackReady && Schema::hasTable('comic_comment_votes');
                $seriesFeedbackRepliesReady = $seriesFeedbackReady && Schema::hasColumn('comic_comments', 'parent_id');
                $seriesFeedbackSpoilersReady = $seriesFeedbackReady && Schema::hasColumn('comic_comments', 'is_spoiler');
                $seriesFeedbackImagesReady = $seriesFeedbackReady && Schema::hasColumn('comic_comments', 'image_path');
                $seriesFeedbackReactionsReady = Schema::hasTable('comic_reactions');
            } catch (\Throwable) {
                $seriesFeedbackReady = false;
                $seriesFeedbackVotesReady = false;
                $seriesFeedbackRepliesReady = false;
                $seriesFeedbackSpoilersReady = false;
                $seriesFeedbackImagesReady = false;
                $seriesFeedbackReactionsReady = false;
            }
        }

        $readerKey = $request->user()
            ? 'user:'.$request->user()->getAuthIdentifier()
            : $request->session()->get('reader_reactor_key');

        if ($databaseComic && $seriesFeedbackReactionsReady) {
            $databaseComic->load('reactions');

            $reactionDefinitions = collect([
                'like' => 'Suka',
                'hype' => '🔥 Hype',
                'sad' => '😭 Sedih',
                'twist' => '🤯 Twist',
            ]);

            $reactionCounts = $databaseComic->reactions
                ->groupBy('type')
                ->map(fn ($items) => $items->count());

            $activeReactions = $databaseComic->reactions
                ->where('reactor_key', $readerKey)
                ->pluck('type')
                ->all();

            $seriesFeedbackReactions = $reactionDefinitions
                ->map(fn (string $label, string $key) => [
                    'key' => $key,
                    'label' => $label,
                    'count' => (int) ($reactionCounts[$key] ?? 0),
                    'active' => in_array($key, $activeReactions, true),
                ])
                ->values();
        }

        if ($databaseComic && $seriesFeedbackReady) {
            $commentBaseQuery = ComicComment::query()
                ->where('comic_id', $databaseComic->id)
                ->where('is_visible', true);

            $seriesFeedbackTotal = (clone $commentBaseQuery)->count();
            $averageScore = (float) ((clone $commentBaseQuery)->avg('score') ?? 0);
            $totalLikes = (int) ((clone $commentBaseQuery)->sum('likes_count'));

            $commentsQuery = clone $commentBaseQuery;

            if ($seriesFeedbackSort === 'oldest') {
                $commentsQuery->oldest();
            } else {
                $commentsQuery->latest();
            }

            if ($seriesFeedbackVotesReady) {
                $commentsQuery->with('votes');
            }

            $flatComments = $commentsQuery->get()->values();
            $currentUserId = (int) $request->user()?->id;
            $commentParentLookup = $flatComments->pluck('parent_id', 'id')->all();

            $resolveComicRootId = function (ComicComment $comment) use ($commentParentLookup) {
                $rootId = $comment->id;
                $parentId = $comment->parent_id;

                while ($parentId !== null) {
                    $rootId = (int) $parentId;
                    $parentId = $commentParentLookup[$parentId] ?? null;
                }

                return $rootId;
            };

            $mapComicComment = function (ComicComment $comment, array $overrides = []) use ($currentUserId, $readerKey, $resolveComicRootId, $seriesFeedbackVotesReady, $seriesFeedbackSpoilersReady, $seriesFeedbackImagesReady) {
                $likeCount = (int) $comment->likes_count;
                $dislikeCount = 0;
                $userVote = null;

                if ($seriesFeedbackVotesReady && $comment->relationLoaded('votes')) {
                    $votes = $comment->votes;
                    $likeCount = $votes->where('vote', 'like')->count();
                    $dislikeCount = $votes->where('vote', 'dislike')->count();
                    $userVote = $votes->firstWhere('voter_key', $readerKey)?->vote;
                }

                return [
                    'id' => $comment->id,
                    'parent_id' => $comment->parent_id,
                    'root_id' => $overrides['root_id'] ?? $resolveComicRootId($comment),
                    'depth' => $overrides['depth'] ?? ($comment->parent_id ? 1 : 0),
                    'is_edited' => $comment->updated_at !== null
                        && $comment->created_at !== null
                        && $comment->updated_at->gt($comment->created_at),
                    'can_manage' => (int) $comment->user_id === $currentUserId,
                    'name' => $comment->display_name,
                    'time' => optional($comment->created_at)?->diffForHumans() ?? 'Baru saja',
                    'text' => $comment->body,
                    'image_url' => $seriesFeedbackImagesReady ? ComicMedia::resolveMediaPath($comment->image_path) : null,
                    'is_spoiler' => $seriesFeedbackSpoilersReady ? (bool) $comment->is_spoiler : false,
                    'score' => (int) $comment->score,
                    'like_count' => $likeCount,
                    'dislike_count' => $dislikeCount,
                    'user_vote' => $userVote,
                    'replies' => [],
                ];
            };

            if ($seriesFeedbackRepliesReady) {
                $topLevelReplyCounts = $flatComments
                    ->whereNotNull('parent_id')
                    ->groupBy(fn (ComicComment $comment) => $resolveComicRootId($comment))
                    ->map(fn ($items) => $items->count());

                $topLevelComments = $flatComments
                    ->whereNull('parent_id');

                $topLevelComments = (match ($seriesFeedbackSort) {
                    'oldest' => $topLevelComments
                        ->sortBy(fn (ComicComment $comment) => $comment->created_at?->getTimestamp() ?? 0),
                    'popular' => $topLevelComments
                        ->sortByDesc(function (ComicComment $comment) use ($topLevelReplyCounts, $seriesFeedbackVotesReady) {
                            $likeCount = (int) $comment->likes_count;
                            $dislikeCount = 0;

                            if ($seriesFeedbackVotesReady && $comment->relationLoaded('votes')) {
                                $likeCount = $comment->votes->where('vote', 'like')->count();
                                $dislikeCount = $comment->votes->where('vote', 'dislike')->count();
                            }

                            $replyCount = (int) ($topLevelReplyCounts[$comment->id] ?? 0);

                            return [
                                ($likeCount * 3) + ($replyCount * 2) - $dislikeCount,
                                $likeCount,
                                $replyCount,
                                $comment->created_at?->getTimestamp() ?? 0,
                            ];
                        }),
                    default => $topLevelComments
                        ->sortByDesc(fn (ComicComment $comment) => $comment->created_at?->getTimestamp() ?? 0),
                })
                    ->values();

                $repliesByRoot = $flatComments
                    ->whereNotNull('parent_id')
                    ->groupBy(fn (ComicComment $comment) => $resolveComicRootId($comment));

                $seriesFeedback = $topLevelComments
                    ->map(function (ComicComment $comment) use ($mapComicComment, $repliesByRoot) {
                        $payload = $mapComicComment($comment, [
                            'root_id' => $comment->id,
                            'depth' => 0,
                        ]);
                        $payload['replies'] = collect($repliesByRoot->get($comment->id, []))
                            ->sortBy(fn (ComicComment $reply) => $reply->created_at?->getTimestamp() ?? 0)
                            ->map(fn (ComicComment $reply) => $mapComicComment($reply, [
                                'root_id' => $comment->id,
                                'depth' => 1,
                            ]))
                            ->values()
                            ->all();

                        return $payload;
                    })
                    ->values();
            } else {
                $seriesFeedback = $flatComments
                    ->map(fn (ComicComment $comment) => $mapComicComment($comment))
                    ->values();
            }

            $seriesFeedbackStats = collect([
                [
                    'label' => 'Rating komunitas',
                    'value' => $seriesFeedbackTotal > 0 ? number_format($averageScore, 1).'/5' : 'Belum ada',
                    'description' => 'Rata-rata dari ulasan seri yang tampil.',
                ],
                [
                    'label' => 'Total ulasan',
                    'value' => (string) $seriesFeedbackTotal,
                    'description' => 'Jumlah feedback yang masuk untuk judul ini.',
                ],
                [
                    'label' => 'Like komentar',
                    'value' => (string) $totalLikes,
                    'description' => 'Apresiasi pembaca pada ulasan seri.',
                ],
            ]);
        }

        return view('comics.show', [
            'comic' => $comic,
            'related' => $related,
            'bookmarkReady' => $bookmarkReady,
            'ratingReady' => $ratingReady,
            'userBookmark' => $userBookmark,
            'userRating' => $userRating,
            'seriesFeedbackReady' => $seriesFeedbackReady,
            'seriesFeedbackVotesReady' => $seriesFeedbackVotesReady,
            'seriesFeedbackRepliesReady' => $seriesFeedbackRepliesReady,
            'seriesFeedbackReactions' => $seriesFeedbackReactions,
            'seriesFeedbackTotal' => $seriesFeedbackTotal,
            'seriesFeedbackStats' => $seriesFeedbackStats,
            'seriesFeedback' => $seriesFeedback,
            'seriesFeedbackSort' => $seriesFeedbackSort,
        ]);
    }

    private function normalizeCommentSort(Request $request): string
    {
        $sort = trim((string) $request->string('comment_sort'));

        return in_array($sort, ['popular', 'newest', 'oldest'], true) ? $sort : 'newest';
    }
}
