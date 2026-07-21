<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\Comic;
use App\Models\ComicBookmark;
use App\Models\ComicComment;
use App\Models\ComicCommentVote;
use App\Models\ComicReaction;
use App\Models\ComicRating;
use App\Models\ComicView;
use App\Notifications\CommentReplyNotification;
use App\Support\ComicLibrary;
use App\Support\ComicMedia;
use App\Support\FormCaptcha;
use App\Support\TextSanitizer;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReaderController extends Controller
{
    public function show(Request $request, string $slug, int $chapter): View|\Illuminate\Http\Response
    {
        $comic = ComicLibrary::find($slug);

        if (! $comic) {
            return response()->view('comics.not-found', [], 404);
        }

        $chapters = collect($comic['chapters'])->values();
        $currentChapter = $chapters->firstWhere('number', $chapter);

        if (! $currentChapter) {
            return response()->view('comics.not-found', [], 404);
        }

        $chapterIndex = $chapters->search(fn (array $item) => $item['number'] === $currentChapter['number']);
        $databaseChapter = $this->findDatabaseChapter($slug, $chapter);
        $readerCommentSort = $this->readerCommentSort($request);
        $this->trackView($databaseChapter, $request);

        return view('chapters.show', [
            'comic' => $comic,
            'chapter' => $currentChapter,
            'previousChapter' => $chapterIndex > 0 ? $chapters[$chapterIndex - 1] : null,
            'nextChapter' => $chapterIndex < $chapters->count() - 1 ? $chapters[$chapterIndex + 1] : null,
            'chapters' => $chapters,
            'readerReactions' => $this->reactionSummary($databaseChapter, $request),
            'readerComments' => $this->commentsForView($databaseChapter, $request, $readerCommentSort),
            'readerCommentTotal' => $databaseChapter ? $this->visibleCommentCount($databaseChapter) : 0,
            'readerCommentSort' => $readerCommentSort,
            'commentVotesReady' => $this->commentVotesReady(),
            'commentRepliesReady' => $this->commentRepliesReady(),
        ]);
    }

    public function toggleBookmark(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $comic = $this->findDatabaseComic($slug);

        if (! $comic || ! $this->bookmarkReady()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => 'Bookmark belum aktif sampai tabel backend siap dipakai.',
                ], 409);
            }

            return redirect()
                ->route('comics.show', $slug)
                ->with('reader_error', 'Bookmark belum aktif sampai tabel backend siap dipakai.');
        }

        $bookmark = ComicBookmark::query()
            ->where('comic_id', $comic->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $message = 'Bookmark dihapus dari akunmu.';
            $bookmarked = false;
        } else {
            ComicBookmark::query()->create([
                'comic_id' => $comic->id,
                'user_id' => $request->user()->id,
            ]);
            $message = 'Komik berhasil disimpan ke bookmark.';
            $bookmarked = true;
        }

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => $message,
                'bookmarked' => $bookmarked,
                'bookmarks_count' => $comic->bookmarks()->count(),
            ]);
        }

        return redirect()
            ->route('comics.show', $slug)
            ->with('reader_success', $message);
    }

    public function rateComic(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $comic = $this->findDatabaseComic($slug);

        if (! $comic || ! $this->ratingsReady()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => 'Rating belum aktif sampai tabel backend siap dipakai.',
                ], 409);
            }

            return redirect()
                ->route('comics.show', $slug)
                ->with('reader_error', 'Rating belum aktif sampai tabel backend siap dipakai.');
        }

        if ($message = $this->readerModerationBlockMessage($request)) {
            return $this->blockedComicActionResponse($request, $slug, $message);
        }

        $validated = $request->validate([
            'score' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        ComicRating::query()->updateOrCreate(
            [
                'comic_id' => $comic->id,
                'user_id' => $request->user()->id,
            ],
            [
                'score' => (int) $validated['score'],
            ],
        );

        if ($this->expectsJson($request)) {
            $ratings = ComicRating::query()->where('comic_id', $comic->id);

            return response()->json([
                'message' => 'Rating kamu berhasil disimpan.',
                'score' => (int) $validated['score'],
                'rating_average' => round((float) ($ratings->avg('score') ?? 0), 1),
                'rating_count' => $ratings->count(),
            ]);
        }

        return redirect()
            ->route('comics.show', $slug)
            ->with('reader_success', 'Rating kamu berhasil disimpan.');
    }

    public function toggleComicReaction(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:like,hype,sad,twist'],
        ]);

        if ($validator->fails()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return $this->redirectToComicFeedback($slug)
                ->withErrors($validator);
        }

        $comic = $this->findDatabaseComic($slug);

        if (! $comic || ! $this->comicReactionsReady()) {
            $message = 'Reaction comic belum aktif sampai tabel backend siap dipakai.';

            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $message,
                ], 409);
            }

            return $this->redirectToComicFeedback($slug)
                ->with('reader_error', $message);
        }

        if ($message = $this->readerModerationBlockMessage($request)) {
            return $this->blockedComicActionResponse($request, $slug, $message, true);
        }

        $reaction = $comic->reactions()
            ->where('reactor_key', $this->readerKey($request))
            ->first();

        if ($reaction && $reaction->type === $validator->validated()['type']) {
            $reaction->delete();
            $message = 'Reaction kamu dibatalkan.';
        } elseif ($reaction) {
            $reaction->update([
                'type' => $validator->validated()['type'],
            ]);
            $message = 'Reaction kamu berhasil diganti.';
        } else {
            $comic->reactions()->create([
                'type' => $validator->validated()['type'],
                'reactor_key' => $this->readerKey($request),
            ]);
            $message = 'Reaction berhasil dikirim.';
        }

        $comic->load('reactions');

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => $message,
                'reactions' => $this->comicReactionSummary($comic, $request),
            ]);
        }

        return $this->redirectToComicFeedback($slug)
            ->with('reader_success', $message);
    }

    public function storeComicComment(Request $request, string $slug): RedirectResponse|JsonResponse
    {
        $input = [
            'score' => $request->input('score', 5),
            'body' => TextSanitizer::plain($request->input('body'), true) ?: null,
            'parent_id' => $request->input('parent_id'),
            'comment_image' => $request->file('comment_image'),
            'is_spoiler' => $request->boolean('is_spoiler'),
        ];

        $validator = Validator::make($input, [
            'score' => ['nullable', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'min:3', 'max:1500', 'required_without:comment_image'],
            'parent_id' => ['nullable', 'integer'],
            'comment_image' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp,image/gif', 'max:5120', 'required_without:body'],
            'is_spoiler' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return $this->redirectToComicFeedback($slug)
                ->withErrors($validator)
                ->withInput();
        }

        if ($message = $this->readerModerationBlockMessage($request, true)) {
            return $this->blockedComicActionResponse($request, $slug, $message, true);
        }

        if ($captchaResponse = $this->validateCaptchaForRequest($request, 'series-feedback')) {
            return $captchaResponse;
        }

        TextSanitizer::ensureNoSpam([
            'body' => $validator->validated()['body'],
        ], 'Komentar mengandung link atau kata yang terindikasi spam.');

        $comic = $this->findDatabaseComic($slug);

        if (! $comic || ! $this->comicCommentsReady()) {
            $message = 'Feedback komik belum aktif sampai tabel backend siap dipakai.';

            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $message,
                ], 409);
            }

            return $this->redirectToComicFeedback($slug)
                ->with('reader_error', $message);
        }

        $displayName = trim((string) $request->user()?->name);

        abort_unless($displayName !== '', 403);

        $parentComment = null;

        if ($this->comicCommentRepliesReady() && ! empty($validator->validated()['parent_id'])) {
            $parentComment = $comic->comments()
                ->whereKey($validator->validated()['parent_id'])
                ->where('is_visible', true)
                ->first();

            if (! $parentComment) {
                $message = 'Komentar induk untuk balasan tidak ditemukan.';

                if ($this->expectsJson($request)) {
                    return response()->json([
                        'message' => $message,
                        'errors' => [
                            'parent_id' => [$message],
                        ],
                    ], 422);
                }

                return $this->redirectToComicFeedback($slug)
                    ->withErrors([
                        'parent_id' => $message,
                    ])
                    ->withInput();
            }
        }

        $payload = [
            'user_id' => $request->user()?->id,
            'display_name' => $displayName,
            'score' => (int) ($validator->validated()['score'] ?? 5),
            'body' => $validator->validated()['body'] ?? '',
            'likes_count' => 0,
            'is_visible' => true,
        ];

        if ($this->comicCommentSpoilersReady()) {
            $payload['is_spoiler'] = (bool) ($validator->validated()['is_spoiler'] ?? false);
        }

        if ($this->comicCommentRepliesReady()) {
            $payload['parent_id'] = $parentComment?->id;
        }

        $comment = $comic->comments()->create($payload);

        if ($request->hasFile('comment_image') && $this->comicCommentImagesReady()) {
            $comment->update([
                'image_path' => ComicMedia::storeCommentImage($request->file('comment_image'), 'comic', $slug, $comment->id),
            ]);
            $comment->refresh();
        }

        if ($parentComment) {
            $this->notifyReplyRecipient(
                $parentComment->user,
                [
                    'title' => 'Balasan baru di feedback comic',
                    'message' => $displayName.' membalas komentar kamu di '.$comic->title.'.',
                    'url' => route('comics.show', $slug).'#series-feedback',
                    'context' => 'comic_reply',
                    'actor_name' => $displayName,
                    'comic_title' => $comic->title,
                    'excerpt' => Str::limit($validator->validated()['body'], 120),
                ],
                $request->user()?->id,
            );
        }

        if ($this->comicCommentVotesReady()) {
            $comment->load('votes');
        }

        $commentPayload = $this->comicCommentPayload($comment, $request);

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => $parentComment ? 'Balasan berhasil dikirim.' : 'Ulasan seri berhasil dikirim.',
                'comment' => $commentPayload,
                'comment_html' => $this->renderComicCommentHtml($commentPayload, $slug),
                'comment_count' => $this->visibleComicCommentCount($comic),
                'captcha_question' => $this->requestHasSession($request)
                    ? FormCaptcha::question($request, 'series-feedback')
                    : null,
            ], 201);
        }

        return $this->redirectToComicFeedback($slug)
            ->with('reader_success', $parentComment ? 'Balasan berhasil dikirim.' : 'Ulasan seri berhasil dikirim.');
    }

    public function voteComicComment(Request $request, string $slug, ComicComment $comment): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vote' => ['required', 'string', 'in:like,dislike'],
        ]);

        if ($validator->fails()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return $this->redirectToComicFeedback($slug)
                ->withErrors($validator);
        }

        $comic = $this->findDatabaseComic($slug);

        if (! $comic || ! $this->comicCommentsReady() || ! $this->comicCommentVotesReady()) {
            $message = 'Vote feedback komik belum aktif sampai tabel backend siap dipakai.';

            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $message,
                ], 409);
            }

            return $this->redirectToComicFeedback($slug)
                ->with('reader_error', $message);
        }

        if ($message = $this->readerModerationBlockMessage($request)) {
            return $this->blockedComicActionResponse($request, $slug, $message, true);
        }

        if ($comment->comic_id !== $comic->id) {
            abort(404);
        }

        $voteType = $validator->validated()['vote'];
        $existingVote = $comment->votes()
            ->where('voter_key', $this->readerKey($request))
            ->first();

        if ($existingVote && $existingVote->vote === $voteType) {
            $existingVote->delete();
            $message = $voteType === 'like' ? 'Like ulasan dibatalkan.' : 'Dislike ulasan dibatalkan.';
        } elseif ($existingVote) {
            $existingVote->update([
                'vote' => $voteType,
            ]);
            $message = $voteType === 'like' ? 'Ulasan diberi like.' : 'Ulasan diberi dislike.';
        } else {
            $comment->votes()->create([
                'voter_key' => $this->readerKey($request),
                'vote' => $voteType,
            ]);
            $message = $voteType === 'like' ? 'Ulasan diberi like.' : 'Ulasan diberi dislike.';
        }

        $comment->load('votes');
        $comment->update([
            'likes_count' => $comment->votes()->where('vote', 'like')->count(),
        ]);

        $commentPayload = $this->comicCommentPayload($comment->fresh('votes'), $request);

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => $message,
                'comment' => $commentPayload,
                'comment_html' => $this->renderComicCommentHtml($commentPayload, $slug),
            ]);
        }

        return $this->redirectToComicFeedback($slug)
            ->with('reader_success', $message);
    }

    public function updateComicComment(Request $request, string $slug, ComicComment $comment): RedirectResponse|JsonResponse
    {
        $comic = $this->findDatabaseComic($slug);

        if (! $comic || ! $this->comicCommentsReady() || $comment->comic_id !== $comic->id) {
            abort(404);
        }

        abort_unless((int) $comment->user_id === (int) $request->user()?->id, 403);

        if ($message = $this->readerModerationBlockMessage($request, true)) {
            return $this->blockedComicActionResponse($request, $slug, $message, true);
        }

        $input = [
            'body' => TextSanitizer::plain($request->input('body'), true),
        ];

        $validator = Validator::make($input, [
            'body' => ['required', 'string', 'min:3', 'max:1500'],
        ]);

        if ($validator->fails()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return $this->redirectToComicFeedback($slug)
                ->withErrors($validator)
                ->withInput();
        }

        TextSanitizer::ensureNoSpam([
            'body' => $validator->validated()['body'],
        ], 'Komentar mengandung link atau kata yang terindikasi spam.');

        $payload = [
            'body' => $validator->validated()['body'],
        ];

        $comment->update($payload);

        if ($this->comicCommentVotesReady()) {
            $comment->load('votes');
        }

        $commentPayload = $this->comicCommentPayload($comment->fresh('votes'), $request);

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => 'Komentar berhasil diperbarui.',
                'comment' => $commentPayload,
                'comment_html' => $this->renderComicCommentHtml($commentPayload, $slug),
            ]);
        }

        return $this->redirectToComicFeedback($slug)
            ->with('reader_success', 'Komentar berhasil diperbarui.');
    }

    public function destroyComicComment(Request $request, string $slug, ComicComment $comment): RedirectResponse|JsonResponse
    {
        $comic = $this->findDatabaseComic($slug);

        if (! $comic || ! $this->comicCommentsReady() || $comment->comic_id !== $comic->id) {
            abort(404);
        }

        abort_unless((int) $comment->user_id === (int) $request->user()?->id, 403);

        $commentId = $comment->id;
        $parentId = $comment->parent_id;
        $comment->delete();

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => 'Komentar berhasil dihapus.',
                'comment_id' => $commentId,
                'parent_id' => $parentId,
                'comment_count' => $this->visibleComicCommentCount($comic),
            ]);
        }

        return $this->redirectToComicFeedback($slug)
            ->with('reader_success', 'Komentar berhasil dihapus.');
    }

    public function storeComment(Request $request, string $slug, int $chapter): RedirectResponse|JsonResponse
    {
        $input = [
            'body' => TextSanitizer::plain($request->input('body'), true) ?: null,
            'parent_id' => $request->input('parent_id'),
            'comment_image' => $request->file('comment_image'),
            'is_spoiler' => $request->boolean('is_spoiler'),
        ];

        $validator = Validator::make($input, [
            'body' => ['nullable', 'string', 'min:3', 'max:1500', 'required_without:comment_image'],
            'parent_id' => ['nullable', 'integer'],
            'comment_image' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp,image/gif', 'max:5120', 'required_without:body'],
            'is_spoiler' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return $this->redirectToReaderFeedback($slug, $chapter)
                ->withErrors($validator)
                ->withInput();
        }

        if ($message = $this->readerModerationBlockMessage($request, true)) {
            return $this->blockedChapterActionResponse($request, $slug, $chapter, $message);
        }

        $validated = $validator->validated();

        TextSanitizer::ensureNoSpam([
            'body' => $validated['body'],
        ], 'Komentar mengandung link atau kata yang terindikasi spam.');

        if ($captchaResponse = $this->validateCaptchaForRequest($request, 'reader-comment')) {
            return $captchaResponse;
        }

        $databaseChapter = $this->findDatabaseChapter($slug, $chapter);

        if (! $databaseChapter || ! $this->commentsReady()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => 'Komentar belum aktif sampai tabel backend siap dipakai.',
                ], 409);
            }

            return $this->redirectToReaderFeedback($slug, $chapter)
                ->with('reader_error', 'Komentar belum aktif sampai tabel backend siap dipakai.');
        }

        $displayName = trim((string) $request->user()?->name);

        abort_unless($displayName !== '', 403);

        $parentComment = null;

        if ($this->commentRepliesReady() && ! empty($validated['parent_id'])) {
            $parentComment = $databaseChapter->comments()
                ->whereKey($validated['parent_id'])
                ->where('is_visible', true)
                ->first();

            if (! $parentComment) {
                $message = 'Komentar induk untuk balasan tidak ditemukan.';

                if ($this->expectsJson($request)) {
                    return response()->json([
                        'message' => $message,
                        'errors' => [
                            'parent_id' => [$message],
                        ],
                    ], 422);
                }

                return $this->redirectToReaderFeedback($slug, $chapter)
                    ->withErrors([
                        'parent_id' => $message,
                    ])
                    ->withInput();
            }
        }

        $payload = [
            'user_id' => $request->user()?->id,
            'display_name' => $displayName,
            'body' => $validated['body'] ?? '',
            'likes_count' => 0,
            'is_visible' => true,
        ];

        if ($this->commentSpoilersReady()) {
            $payload['is_spoiler'] = (bool) ($validated['is_spoiler'] ?? false);
        }

        if ($this->commentRepliesReady()) {
            $payload['parent_id'] = $parentComment?->id;
        }

        $comment = $databaseChapter->comments()->create($payload);

        if ($request->hasFile('comment_image') && $this->commentImagesReady()) {
            $comment->update([
                'image_path' => ComicMedia::storeCommentImage($request->file('comment_image'), 'chapter', $slug, $comment->id),
            ]);
            $comment->refresh();
        }

        if ($parentComment) {
            $this->notifyReplyRecipient(
                $parentComment->user,
                [
                    'title' => 'Balasan baru di komentar chapter',
                    'message' => $displayName.' membalas komentar kamu di '.($databaseChapter->comic->title ?? 'chapter ini').'.',
                    'url' => route('chapters.show', ['slug' => $slug, 'chapter' => $chapter]).'#reader-feedback',
                    'context' => 'chapter_reply',
                    'actor_name' => $displayName,
                    'comic_title' => $databaseChapter->comic->title ?? null,
                    'chapter_title' => $databaseChapter->title,
                    'excerpt' => Str::limit($validated['body'], 120),
                ],
                $request->user()?->id,
            );
        }

        if ($this->commentVotesReady()) {
            $comment->load('votes');
        }

        if ($this->expectsJson($request)) {
            $commentView = $this->commentPayload($comment, $request);

            return response()->json([
                'message' => $parentComment ? 'Balasan berhasil dikirim.' : 'Komentar berhasil dikirim.',
                'comment' => $commentView,
                'comment_html' => $this->renderCommentHtml($commentView, $slug, $chapter),
                'comment_count' => $this->visibleCommentCount($databaseChapter),
                'captcha_question' => $this->requestHasSession($request)
                    ? FormCaptcha::question($request, 'reader-comment')
                    : null,
            ], 201);
        }

        return $this->redirectToReaderFeedback($slug, $chapter)
            ->with('reader_success', $parentComment ? 'Balasan berhasil dikirim.' : 'Komentar berhasil dikirim.');
    }

    public function toggleReaction(Request $request, string $slug, int $chapter): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', 'in:like,hype,sad,twist'],
        ]);

        if ($validator->fails()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return $this->redirectToReaderFeedback($slug, $chapter)
                ->withErrors($validator);
        }

        $validated = $validator->validated();

        $databaseChapter = $this->findDatabaseChapter($slug, $chapter);

        if (! $databaseChapter || ! $this->reactionsReady()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => 'Reaction belum aktif sampai tabel backend siap dipakai.',
                ], 409);
            }

            return $this->redirectToReaderFeedback($slug, $chapter)
                ->with('reader_error', 'Reaction belum aktif sampai tabel backend siap dipakai.');
        }

        if ($message = $this->readerModerationBlockMessage($request)) {
            return $this->blockedChapterActionResponse($request, $slug, $chapter, $message);
        }

        $reaction = $databaseChapter->reactions()
            ->where('reactor_key', $this->readerKey($request))
            ->first();

        if ($reaction && $reaction->type === $validated['type']) {
            $reaction->delete();
            $message = 'Reaction kamu dibatalkan.';
        } elseif ($reaction) {
            $reaction->update([
                'type' => $validated['type'],
            ]);
            $message = 'Reaction kamu berhasil diganti.';
        } else {
            $databaseChapter->reactions()->create([
                'type' => $validated['type'],
                'reactor_key' => $this->readerKey($request),
            ]);
            $message = 'Reaction berhasil dikirim.';
        }

        $databaseChapter->load('reactions');

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => $message,
                'reactions' => $this->reactionSummary($databaseChapter, $request),
            ]);
        }

        return $this->redirectToReaderFeedback($slug, $chapter)
            ->with('reader_success', $message);
    }

    public function voteComment(Request $request, string $slug, int $chapter, ChapterComment $comment): RedirectResponse|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'vote' => ['required', 'string', 'in:like,dislike'],
        ]);

        if ($validator->fails()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return $this->redirectToReaderFeedback($slug, $chapter)
                ->withErrors($validator);
        }

        $databaseChapter = $this->findDatabaseChapter($slug, $chapter);

        if (! $databaseChapter || ! $this->commentsReady() || ! $this->commentVotesReady()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => 'Vote komentar belum aktif sampai tabel backend siap dipakai.',
                ], 409);
            }

            return $this->redirectToReaderFeedback($slug, $chapter)
                ->with('reader_error', 'Vote komentar belum aktif sampai tabel backend siap dipakai.');
        }

        if ($message = $this->readerModerationBlockMessage($request)) {
            return $this->blockedChapterActionResponse($request, $slug, $chapter, $message);
        }

        if ($comment->chapter_id !== $databaseChapter->id) {
            abort(404);
        }

        $voteType = $validator->validated()['vote'];
        $existingVote = $comment->votes()
            ->where('voter_key', $this->readerKey($request))
            ->first();

        if ($existingVote && $existingVote->vote === $voteType) {
            $existingVote->delete();
            $message = $voteType === 'like' ? 'Like komentar dibatalkan.' : 'Dislike komentar dibatalkan.';
        } elseif ($existingVote) {
            $existingVote->update([
                'vote' => $voteType,
            ]);
            $message = $voteType === 'like' ? 'Komentar diberi like.' : 'Komentar diberi dislike.';
        } else {
            $comment->votes()->create([
                'voter_key' => $this->readerKey($request),
                'vote' => $voteType,
            ]);
            $message = $voteType === 'like' ? 'Komentar diberi like.' : 'Komentar diberi dislike.';
        }

        $comment->load('votes');
        $comment->update([
            'likes_count' => $comment->votes()->where('vote', 'like')->count(),
        ]);

        $commentView = $this->commentPayload($comment->fresh('votes'), $request);

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => $message,
                'comment' => $commentView,
                'comment_html' => $this->renderCommentHtml($commentView, $slug, $chapter),
            ]);
        }

        return $this->redirectToReaderFeedback($slug, $chapter)
            ->with('reader_success', $message);
    }

    public function updateComment(Request $request, string $slug, int $chapter, ChapterComment $comment): RedirectResponse|JsonResponse
    {
        $databaseChapter = $this->findDatabaseChapter($slug, $chapter);

        if (! $databaseChapter || ! $this->commentsReady() || $comment->chapter_id !== $databaseChapter->id) {
            abort(404);
        }

        abort_unless((int) $comment->user_id === (int) $request->user()?->id, 403);

        if ($message = $this->readerModerationBlockMessage($request, true)) {
            return $this->blockedChapterActionResponse($request, $slug, $chapter, $message);
        }

        $input = [
            'body' => TextSanitizer::plain($request->input('body'), true),
        ];

        $validator = Validator::make($input, [
            'body' => ['required', 'string', 'min:3', 'max:1500'],
        ]);

        if ($validator->fails()) {
            if ($this->expectsJson($request)) {
                return response()->json([
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            return $this->redirectToReaderFeedback($slug, $chapter)
                ->withErrors($validator)
                ->withInput();
        }

        TextSanitizer::ensureNoSpam([
            'body' => $validator->validated()['body'],
        ], 'Komentar mengandung link atau kata yang terindikasi spam.');

        $payload = [
            'body' => $validator->validated()['body'],
        ];

        $comment->update($payload);

        if ($this->commentVotesReady()) {
            $comment->load('votes');
        }

        $commentView = $this->commentPayload($comment->fresh('votes'), $request);

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => 'Komentar berhasil diperbarui.',
                'comment' => $commentView,
                'comment_html' => $this->renderCommentHtml($commentView, $slug, $chapter),
            ]);
        }

        return $this->redirectToReaderFeedback($slug, $chapter)
            ->with('reader_success', 'Komentar berhasil diperbarui.');
    }

    public function destroyComment(Request $request, string $slug, int $chapter, ChapterComment $comment): RedirectResponse|JsonResponse
    {
        $databaseChapter = $this->findDatabaseChapter($slug, $chapter);

        if (! $databaseChapter || ! $this->commentsReady() || $comment->chapter_id !== $databaseChapter->id) {
            abort(404);
        }

        abort_unless((int) $comment->user_id === (int) $request->user()?->id, 403);

        $commentId = $comment->id;
        $parentId = $comment->parent_id;
        $comment->delete();

        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => 'Komentar berhasil dihapus.',
                'comment_id' => $commentId,
                'parent_id' => $parentId,
                'comment_count' => $this->visibleCommentCount($databaseChapter),
            ]);
        }

        return $this->redirectToReaderFeedback($slug, $chapter)
            ->with('reader_success', 'Komentar berhasil dihapus.');
    }

    private function reactionSummary(?Chapter $chapter, Request $request): array
    {
        $definitions = [
            'like' => 'Suka',
            'hype' => '🔥 Hype',
            'sad' => '😭 Sedih',
            'twist' => '🤯 Twist',
        ];

        $counts = collect();
        $active = [];

        if ($chapter && $this->reactionsReady()) {
            $counts = $chapter->reactions
                ->groupBy('type')
                ->map(fn ($items) => $items->count());

            $active = $chapter->reactions
                ->where('reactor_key', $this->readerKey($request))
                ->pluck('type')
                ->all();
        }

        return collect($definitions)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
            'count' => (int) ($counts[$key] ?? 0),
            'active' => in_array($key, $active, true),
        ])->values()->all();
    }

    private function comicReactionSummary(?Comic $comic, Request $request): array
    {
        $definitions = [
            'like' => 'Suka',
            'hype' => '🔥 Hype',
            'sad' => '😭 Sedih',
            'twist' => '🤯 Twist',
        ];

        $counts = collect();
        $active = [];

        if ($comic && $this->comicReactionsReady()) {
            $counts = $comic->reactions
                ->groupBy('type')
                ->map(fn ($items) => $items->count());

            $active = $comic->reactions
                ->where('reactor_key', $this->readerKey($request))
                ->pluck('type')
                ->all();
        }

        return collect($definitions)->map(fn (string $label, string $key) => [
            'key' => $key,
            'label' => $label,
            'count' => (int) ($counts[$key] ?? 0),
            'active' => in_array($key, $active, true),
        ])->values()->all();
    }

    private function commentsForView(?Chapter $chapter, Request $request, string $sort = 'newest'): array
    {
        if (! $chapter || ! $this->commentsReady()) {
            return [];
        }

        $comments = $chapter->comments
            ->where('is_visible', true)
            ->values();

        if (! $this->commentRepliesReady()) {
            return $this->sortChapterTopLevelComments($comments, $sort)
                ->map(fn ($comment) => $this->commentPayload($comment, $request))
                ->values()
                ->all();
        }

        $parentLookup = $comments->pluck('parent_id', 'id')->all();

        $topLevelReplyCounts = $comments
            ->whereNotNull('parent_id')
            ->groupBy(fn (ChapterComment $comment) => $this->chapterCommentRootId($comment, $parentLookup))
            ->map(fn ($items) => $items->count())
            ->all();

        $topLevel = $comments
            ->whereNull('parent_id')
            ->values();

        $topLevel = $this->sortChapterTopLevelComments($topLevel, $sort, $topLevelReplyCounts);

        $repliesByRoot = $comments
            ->whereNotNull('parent_id')
            ->groupBy(fn (ChapterComment $comment) => $this->chapterCommentRootId($comment, $parentLookup));

        return $topLevel->map(function (ChapterComment $comment) use ($repliesByRoot, $parentLookup, $request) {
            $payload = $this->commentPayload($comment, $request, [
                'root_id' => $comment->id,
                'depth' => 0,
                'parent_lookup' => $parentLookup,
            ]);
            $payload['depth'] = 0;
            $payload['replies'] = collect($repliesByRoot->get($comment->id, []))
                ->sortBy(fn (ChapterComment $reply) => $reply->created_at?->getTimestamp() ?? 0)
                ->map(function (ChapterComment $reply) use ($comment, $parentLookup, $request) {
                    $replyPayload = $this->commentPayload($reply, $request, [
                        'root_id' => $comment->id,
                        'depth' => 1,
                        'parent_lookup' => $parentLookup,
                    ]);
                    $replyPayload['depth'] = 1;
                    $replyPayload['root_id'] = $comment->id;
                    $replyPayload['replies'] = [];

                    return $replyPayload;
                })
                ->values()
                ->all();

            return $payload;
        })->all();
    }

    private function sortChapterTopLevelComments($comments, string $sort, array $replyCounts = [])
    {
        return (match ($sort) {
            'oldest' => $comments
                ->sortBy(fn (ChapterComment $comment) => $comment->created_at?->getTimestamp() ?? 0),
            'popular' => $comments
                ->sortByDesc(function (ChapterComment $comment) use ($replyCounts) {
                    $likeCount = (int) $comment->likes_count;
                    $dislikeCount = 0;

                    if ($this->commentVotesReady() && $comment->relationLoaded('votes')) {
                        $likeCount = $comment->votes->where('vote', 'like')->count();
                        $dislikeCount = $comment->votes->where('vote', 'dislike')->count();
                    }

                    $replyCount = (int) ($replyCounts[$comment->id] ?? 0);

                    return [
                        ($likeCount * 3) + ($replyCount * 2) - $dislikeCount,
                        $likeCount,
                        $replyCount,
                        $comment->created_at?->getTimestamp() ?? 0,
                    ];
                }),
            default => $comments
                ->sortByDesc(fn (ChapterComment $comment) => $comment->created_at?->getTimestamp() ?? 0),
        })
            ->values();
    }

    private function trackView(?Chapter $chapter, Request $request): void
    {
        if (! $chapter || ! $this->viewsReady()) {
            return;
        }

        ComicView::query()->firstOrCreate(
            [
                'chapter_id' => $chapter->id,
                'viewer_key' => $this->readerKey($request),
                'viewed_on' => Carbon::today()->toDateString(),
            ],
            [
                'comic_id' => $chapter->comic_id,
                'user_id' => $request->user()?->id,
            ],
        );
    }

    private function findDatabaseChapter(string $slug, int $number): ?Chapter
    {
        if (! $this->chaptersReady()) {
            return null;
        }

        $query = Chapter::query();

        if ($this->commentsReady() && $this->reactionsReady()) {
            $query->with([
                'comments' => fn ($relation) => $relation
                    ->where('is_visible', true)
                    ->latest()
                    ->when($this->commentVotesReady(), fn ($comments) => $comments->with('votes')),
                'reactions',
            ]);
        } elseif ($this->commentsReady()) {
            $query->with([
                'comments' => fn ($relation) => $relation
                    ->where('is_visible', true)
                    ->latest()
                    ->when($this->commentVotesReady(), fn ($comments) => $comments->with('votes')),
            ]);
        } elseif ($this->reactionsReady()) {
            $query->with('reactions');
        }

        return $query
            ->where('number', $number)
            ->whereHas('comic', fn ($query) => $query->where('slug', $slug))
            ->first();
    }

    private function findDatabaseComic(string $slug): ?Comic
    {
        if (! $this->chaptersReady()) {
            return null;
        }

        $query = Comic::query();

        if ($this->comicCommentsReady() && $this->comicReactionsReady()) {
            $query->with([
                'comments' => fn ($relation) => $relation
                    ->where('is_visible', true)
                    ->latest()
                    ->when($this->comicCommentVotesReady(), fn ($comments) => $comments->with('votes')),
                'reactions',
            ]);
        } elseif ($this->comicCommentsReady()) {
            $query->with([
                'comments' => fn ($relation) => $relation
                    ->where('is_visible', true)
                    ->latest()
                    ->when($this->comicCommentVotesReady(), fn ($comments) => $comments->with('votes')),
            ]);
        } elseif ($this->comicReactionsReady()) {
            $query->with('reactions');
        }

        return $query->where('slug', $slug)->first();
    }

    private function readerKey(Request $request): string
    {
        if ($request->user()) {
            return 'user:'.$request->user()->getAuthIdentifier();
        }

        $headerKey = trim((string) $request->header('X-Reader-Key'));

        if ($headerKey !== '') {
            return 'guest:'.$headerKey;
        }

        if (! $this->requestHasSession($request)) {
            return 'guest:'.hash('sha256', (string) $request->ip().'|'.(string) $request->userAgent());
        }

        $existing = $request->session()->get('reader_reactor_key');

        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $generated = (string) Str::uuid();
        $request->session()->put('reader_reactor_key', $generated);

        return $generated;
    }

    private function chaptersReady(): bool
    {
        try {
            return Schema::hasTable('comics') && Schema::hasTable('chapters');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function commentsReady(): bool
    {
        try {
            return $this->chaptersReady() && Schema::hasTable('chapter_comments');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function reactionsReady(): bool
    {
        try {
            return $this->chaptersReady() && Schema::hasTable('chapter_reactions');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function viewsReady(): bool
    {
        try {
            return $this->chaptersReady() && Schema::hasTable('comic_views');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function bookmarkReady(): bool
    {
        try {
            return $this->chaptersReady() && Schema::hasTable('comic_bookmarks');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function ratingsReady(): bool
    {
        try {
            return $this->chaptersReady() && Schema::hasTable('comic_ratings');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function comicCommentsReady(): bool
    {
        try {
            return $this->chaptersReady() && Schema::hasTable('comic_comments');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function comicCommentVotesReady(): bool
    {
        try {
            return $this->comicCommentsReady() && Schema::hasTable('comic_comment_votes');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function comicReactionsReady(): bool
    {
        try {
            return $this->chaptersReady() && Schema::hasTable('comic_reactions');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function comicCommentRepliesReady(): bool
    {
        try {
            return $this->comicCommentsReady() && Schema::hasColumn('comic_comments', 'parent_id');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function comicCommentSpoilersReady(): bool
    {
        try {
            return $this->comicCommentsReady() && Schema::hasColumn('comic_comments', 'is_spoiler');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function comicCommentImagesReady(): bool
    {
        try {
            return $this->comicCommentsReady() && Schema::hasColumn('comic_comments', 'image_path');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function commentVotesReady(): bool
    {
        try {
            return $this->commentsReady() && Schema::hasTable('chapter_comment_votes');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function commentRepliesReady(): bool
    {
        try {
            return $this->commentsReady() && Schema::hasColumn('chapter_comments', 'parent_id');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function commentSpoilersReady(): bool
    {
        try {
            return $this->commentsReady() && Schema::hasColumn('chapter_comments', 'is_spoiler');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function commentImagesReady(): bool
    {
        try {
            return $this->commentsReady() && Schema::hasColumn('chapter_comments', 'image_path');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function expectsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function readerModerationBlockMessage(Request $request, bool $commenting = false): ?string
    {
        $user = $request->user();

        if (! $user) {
            return null;
        }

        if ($user->isBanned()) {
            return 'Akun kamu diblokir dari fitur komunitas karena pelanggaran komentar/spoiler.';
        }

        if ($user->hasActiveSuspension()) {
            $until = $user->suspended_until?->format('d M Y H:i');

            return $until
                ? "Akun kamu disuspend dari fitur komunitas sampai {$until}."
                : 'Akun kamu sedang disuspend dari fitur komunitas.';
        }

        if ($commenting && $user->hasCommentRestriction()) {
            return 'Komentar akunmu sedang dibatasi moderator. Kamu belum bisa mengirim atau mengubah komentar untuk sementara.';
        }

        return null;
    }

    private function blockedChapterActionResponse(Request $request, string $slug, int $chapter, string $message): RedirectResponse|JsonResponse
    {
        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => $message,
            ], 403);
        }

        return $this->redirectToReaderFeedback($slug, $chapter)
            ->with('reader_error', $message);
    }

    private function blockedComicActionResponse(Request $request, string $slug, string $message, bool $feedback = false): RedirectResponse|JsonResponse
    {
        if ($this->expectsJson($request)) {
            return response()->json([
                'message' => $message,
            ], 403);
        }

        if ($feedback) {
            return $this->redirectToComicFeedback($slug)
                ->with('reader_error', $message);
        }

        return redirect()
            ->route('comics.show', $slug)
            ->with('reader_error', $message);
    }

    private function commentPayload(ChapterComment $comment, Request $request, array $options = []): array
    {
        $likeCount = (int) $comment->likes_count;
        $dislikeCount = 0;
        $userVote = null;

        if ($this->commentVotesReady() && $comment->relationLoaded('votes')) {
            $votes = $comment->votes;
            $likeCount = $votes->where('vote', 'like')->count();
            $dislikeCount = $votes->where('vote', 'dislike')->count();
            $userVote = $votes
                ->firstWhere('voter_key', $this->readerKey($request))
                ?->vote;
        }

        return [
            'id' => $comment->id,
            'parent_id' => $comment->parent_id,
            'root_id' => $options['root_id'] ?? $this->chapterCommentRootId($comment, $options['parent_lookup'] ?? []),
            'depth' => $options['depth'] ?? ($comment->parent_id ? 1 : 0),
            'is_edited' => $comment->updated_at !== null
                && $comment->created_at !== null
                && $comment->updated_at->gt($comment->created_at),
            'can_manage' => (int) $comment->user_id === (int) $request->user()?->id,
            'name' => $comment->display_name,
            'time' => optional($comment->created_at)?->diffForHumans() ?? 'Baru saja',
            'text' => $comment->body,
            'image_url' => $this->commentImagesReady() ? ComicMedia::resolveMediaPath($comment->image_path) : null,
            'is_spoiler' => $this->commentSpoilersReady() ? (bool) $comment->is_spoiler : false,
            'like_count' => $likeCount,
            'dislike_count' => $dislikeCount,
            'user_vote' => $userVote,
            'replies' => [],
        ];
    }

    private function renderCommentHtml(array $comment, string $slug, int $chapter): string
    {
        if (! $this->requestHasSession(request())) {
            return '';
        }

        return view('partials.reader.comment-card', [
            'comment' => $comment,
            'comicSlug' => $slug,
            'chapterNumber' => $chapter,
            'commentVotesReady' => $this->commentVotesReady(),
            'commentRepliesReady' => $this->commentRepliesReady(),
            'depth' => $comment['depth'] ?? 0,
        ])->render();
    }

    private function comicCommentPayload(ComicComment $comment, Request $request, array $options = []): array
    {
        $likeCount = (int) $comment->likes_count;
        $dislikeCount = 0;
        $userVote = null;

        if ($this->comicCommentVotesReady() && $comment->relationLoaded('votes')) {
            $votes = $comment->votes;
            $likeCount = $votes->where('vote', 'like')->count();
            $dislikeCount = $votes->where('vote', 'dislike')->count();
            $userVote = $votes
                ->firstWhere('voter_key', $this->readerKey($request))
                ?->vote;
        }

        return [
            'id' => $comment->id,
            'parent_id' => $comment->parent_id,
            'root_id' => $options['root_id'] ?? $this->comicCommentRootId($comment, $options['parent_lookup'] ?? []),
            'depth' => $options['depth'] ?? ($comment->parent_id ? 1 : 0),
            'is_edited' => $comment->updated_at !== null
                && $comment->created_at !== null
                && $comment->updated_at->gt($comment->created_at),
            'can_manage' => (int) $comment->user_id === (int) $request->user()?->id,
            'name' => $comment->display_name,
            'time' => optional($comment->created_at)?->diffForHumans() ?? 'Baru saja',
            'text' => $comment->body,
            'image_url' => $this->comicCommentImagesReady() ? ComicMedia::resolveMediaPath($comment->image_path) : null,
            'is_spoiler' => $this->comicCommentSpoilersReady() ? (bool) $comment->is_spoiler : false,
            'score' => (int) $comment->score,
            'like_count' => $likeCount,
            'dislike_count' => $dislikeCount,
            'user_vote' => $userVote,
            'replies' => [],
        ];
    }

    private function chapterCommentRootId(ChapterComment $comment, array $parentLookup = []): int
    {
        $rootId = $comment->id;
        $parentId = $comment->parent_id;

        while ($parentId !== null) {
            $rootId = (int) $parentId;
            $parentId = array_key_exists($parentId, $parentLookup)
                ? $parentLookup[$parentId]
                : ChapterComment::query()->whereKey($parentId)->value('parent_id');
        }

        return $rootId;
    }

    private function comicCommentRootId(ComicComment $comment, array $parentLookup = []): int
    {
        $rootId = $comment->id;
        $parentId = $comment->parent_id;

        while ($parentId !== null) {
            $rootId = (int) $parentId;
            $parentId = array_key_exists($parentId, $parentLookup)
                ? $parentLookup[$parentId]
                : ComicComment::query()->whereKey($parentId)->value('parent_id');
        }

        return $rootId;
    }

    private function renderComicCommentHtml(array $comment, string $slug): string
    {
        if (! $this->requestHasSession(request())) {
            return '';
        }

        return view('partials.comics.comment-card', [
            'comment' => $comment,
            'comicSlug' => $slug,
            'commentVotesReady' => $this->comicCommentVotesReady(),
            'commentRepliesReady' => $this->comicCommentRepliesReady(),
            'depth' => $comment['depth'] ?? 0,
        ])->render();
    }

    private function visibleCommentCount(Chapter $chapter): int
    {
        return $chapter->comments()->where('is_visible', true)->count();
    }

    private function visibleComicCommentCount(Comic $comic): int
    {
        return $comic->comments()->where('is_visible', true)->count();
    }

    private function redirectToReaderFeedback(string $slug, int $chapter): RedirectResponse
    {
        return redirect()
            ->route('chapters.show', [
                'slug' => $slug,
                'chapter' => $chapter,
            ])
            ->with('reader_focus', true);
    }

    private function redirectToComicFeedback(string $slug): RedirectResponse
    {
        return redirect()
            ->to(route('comics.show', $slug).'#series-feedback');
    }

    private function readerCommentSort(Request $request): string
    {
        $sort = trim((string) $request->string('comment_sort'));

        if ($sort === '') {
            $query = parse_url(url()->previous(), PHP_URL_QUERY);

            if (is_string($query) && $query !== '') {
                parse_str($query, $queryParameters);
                $sort = trim((string) ($queryParameters['comment_sort'] ?? ''));
            }
        }

        return in_array($sort, ['popular', 'newest', 'oldest'], true) ? $sort : 'newest';
    }

    private function validateCaptchaForRequest(Request $request, string $key): ?JsonResponse
    {
        if (! $this->requestHasSession($request)) {
            return null;
        }

        try {
            FormCaptcha::validate($request, $key, $request->input('captcha_answer'));
        } catch (ValidationException $exception) {
            if (! $this->expectsJson($request)) {
                throw $exception;
            }

            return response()->json([
                'message' => $exception->errors()['captcha_answer'][0] ?? 'CAPTCHA tidak valid.',
                'errors' => $exception->errors(),
                'captcha_question' => FormCaptcha::question($request, $key),
            ], 422);
        }

        return null;
    }

    private function notifyReplyRecipient(?\App\Models\User $recipient, array $payload, ?int $actorId = null): void
    {
        if (! $recipient || ! $this->databaseNotificationsReady()) {
            return;
        }

        if ($actorId !== null && (int) $recipient->getKey() === $actorId) {
            return;
        }

        $recipient->notify(new CommentReplyNotification($payload));
    }

    private function databaseNotificationsReady(): bool
    {
        try {
            return Schema::hasTable('notifications');
        } catch (QueryException|\Throwable) {
            return false;
        }
    }

    private function requestHasSession(Request $request): bool
    {
        return method_exists($request, 'hasSession') && $request->hasSession();
    }
}
