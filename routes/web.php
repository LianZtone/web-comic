<?php

use App\Http\Controllers\Admin\ChapterController as AdminChapterController;
use App\Http\Controllers\Admin\ComicController as AdminComicController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\ComicCommentController as AdminComicCommentController;
use App\Http\Controllers\Admin\UserModerationController as AdminUserModerationController;

use App\Http\Controllers\Admin\ComicController;
use App\Http\Controllers\ComicPageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReaderController;
use App\Support\ComicGenres;
use App\Support\ComicLibrary;
use App\Support\ComicMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

$buildLatestUpdates = function (Collection $comics, int $limit = 4): Collection {
    return $comics
        ->map(fn (array $comic) => [
            'comic' => $comic,
            'chapter' => $comic['latest_chapter'],
        ])
        ->sortByDesc(fn (array $item) => $item['chapter']['number'])
        ->values()
        ->take($limit);
};

$homeRecommendationFormats = ['Manhwa', 'Manga', 'Manhua'];

$buildShelves = function (Collection $comics): array {
    return [
        [
            'title' => 'Trending This Week',
            'caption' => 'Judul yang paling siap jadi pintu masuk pembaca baru.',
            'items' => $comics->take(3),
        ],
        [
            'title' => 'Binge Worthy',
            'caption' => 'Komik completed atau seasonal yang enak dibaca maraton.',
            'items' => $comics->whereIn('status', ['Completed', 'Seasonal'])->take(5)->values(),
        ],
    ];
};

$buildExploreUpdates = function (Collection $comics, int $limit = 12): Collection {
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
};

$buildRecommendations = function (Collection $comics, int $limit = 5): Collection {
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
};

$buildHomeRecommendations = function (Collection $comics, int $perType = 4) use ($buildRecommendations, $homeRecommendationFormats): Collection {
    return collect($homeRecommendationFormats)
        ->flatMap(function (string $format) use ($buildRecommendations, $comics, $perType) {
            $items = $comics
                ->filter(fn (array $comic) => strcasecmp((string) ($comic['comic_type'] ?? ''), $format) === 0)
                ->values();

            if ($items->isEmpty()) {
                return collect();
            }

            return $buildRecommendations($items, $perType);
        })
        ->values();
};

$buildAdminPicks = function (Collection $comics, int $limit = 4): Collection {
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
};

Route::get('/', function () use ($buildHomeRecommendations, $buildLatestUpdates, $buildShelves, $homeRecommendationFormats) {
    $comics = ComicLibrary::all();
    $featured = $comics->first();
    $latestUpdates = $buildLatestUpdates($comics);
    $shelves = $buildShelves($comics);
    $recommendations = $buildHomeRecommendations($comics);
    $recommendationTypes = collect($homeRecommendationFormats)
        ->filter(fn (string $format) => $recommendations->contains(
            fn (array $comic) => strcasecmp((string) ($comic['comic_type'] ?? ''), $format) === 0
        ))
        ->values();

    return view('home', [
        'featured' => $featured,
        'latestUpdates' => $latestUpdates,
        'recommendations' => $recommendations,
        'recommendationTypes' => $recommendationTypes,
        'shelves' => $shelves,
        'genreOptions' => collect(ComicGenres::all()),
        'schedule' => $comics->map(fn (array $comic) => [
            'title' => $comic['title'],
            'schedule' => $comic['schedule'],
            'status' => $comic['status'],
            'slug' => $comic['slug'],
        ]),
    ]);
})->name('home');

Route::get('/explore', function () use ($buildExploreUpdates, $buildRecommendations, $buildAdminPicks) {
    $comics = ComicLibrary::all();

    return view('explore', [
        'updates' => $buildExploreUpdates($comics),
        'recommendations' => $buildRecommendations($comics),
        'adminPicks' => $buildAdminPicks($comics, 6),
    ]);
})->name('explore');

Route::redirect('/dashboard', '/library')
    ->middleware('auth')
    ->name('dashboard');

Route::get('/library', function () {
    $comics = ComicLibrary::all();

    return view('library', [
        'catalogPreview' => $comics->take(6)->values(),
    ]);
})->name('library');

Route::get('/library/bookmarks', function () {
    return view('library.collection', [
        'collectionKey' => 'bookmarks',
        'collectionTitle' => 'Bookmark',
        'collectionDescription' => 'Simpan komik favorit yang ingin kamu pantau tanpa harus langsung masuk antrean baca.',
    ]);
})->name('library.bookmarks');

Route::get('/library/readlist', function () {
    return view('library.collection', [
        'collectionKey' => 'readlist',
        'collectionTitle' => 'Readlist',
        'collectionDescription' => 'Kumpulkan judul untuk dibaca nanti saat ada waktu kosong atau sesi maraton.',
    ]);
})->name('library.readlist');

Route::get('/library/history', function () {
    return view('library.collection', [
        'collectionKey' => 'history',
        'collectionTitle' => 'History',
        'collectionDescription' => 'Lacak chapter terakhir yang kamu buka dan lanjutkan lagi tanpa cari manual.',
    ]);
})->name('library.history');

Route::get('/comics', function (Request $request) {
    $comics = ComicLibrary::all();

    $search = trim((string) $request->string('q'));
    $genres = collect($request->input('genres', []))
        ->map(fn ($genre) => trim((string) $genre))
        ->filter()
        ->unique()
        ->values();
    $status = trim((string) $request->string('status'));
    $type = trim((string) $request->string('type'));
    $orderBy = trim((string) $request->string('order_by'));

    $baseFiltered = $comics->filter(function (array $comic) use ($search, $status, $type) {
        $matchesSearch = $search === '' || str_contains(strtolower($comic['title'].' '.$comic['summary'].' '.$comic['author']), strtolower($search));
        $matchesStatus = $status === '' || $comic['status'] === $status;
        $matchesType = $type === '' || ($comic['comic_type'] ?? '') === $type;

        return $matchesSearch && $matchesStatus && $matchesType;
    })->values();

    $genreOptions = collect(ComicGenres::all());

    $filtered = $baseFiltered->filter(function (array $comic) use ($genres) {
        if ($genres->isEmpty()) {
            return true;
        }

        return $genres->every(fn (string $genre) => in_array($genre, $comic['genres'], true));
    })->values();

    $sorted = match ($orderBy) {
        'popular' => $filtered->sortByDesc(fn (array $comic) => [
            (int) ($comic['views_count'] ?? 0),
            (int) ($comic['bookmarks_count'] ?? 0),
            (float) ($comic['rating_average'] ?? 0),
        ])->values(),
        'bookmarks' => $filtered->sortByDesc(fn (array $comic) => [
            (int) ($comic['bookmarks_count'] ?? 0),
            (int) ($comic['views_count'] ?? 0),
            (float) ($comic['rating_average'] ?? 0),
        ])->values(),
        'rating' => $filtered->sortByDesc(fn (array $comic) => [
            (float) ($comic['rating_average'] ?? 0),
            (int) ($comic['rating_count'] ?? 0),
            (int) ($comic['views_count'] ?? 0),
        ])->values(),
        'latest_update' => $filtered->sortByDesc(fn (array $comic) => [
            (int) ($comic['latest_chapter']['number'] ?? 0),
            (bool) ($comic['is_featured'] ?? false),
            (int) ($comic['views_count'] ?? 0),
        ])->values(),
        'title_asc' => $filtered->sortBy(fn (array $comic) => strtolower($comic['title']))->values(),
        'title_desc' => $filtered->sortByDesc(fn (array $comic) => strtolower($comic['title']))->values(),
        default => $filtered->sortByDesc(fn (array $comic) => [
            (int) ($comic['latest_chapter']['number'] ?? 0),
            (bool) ($comic['is_featured'] ?? false),
            (int) ($comic['views_count'] ?? 0),
        ])->values(),
    };

    return view('comics.index', [
        'comics' => $sorted,
        'total' => $sorted->count(),
        'filters' => [
            'q' => $search,
            'genres' => $genres->all(),
            'status' => $status,
            'type' => $type,
            'order_by' => $orderBy,
        ],
        'genreOptions' => $genreOptions,
        'statusOptions' => $comics->pluck('status')->unique()->sort()->values(),
        'typeOptions' => collect(ComicMetadata::formats()),
        'orderByOptions' => [
            '' => 'Default',
            'popular' => 'Paling populer',
            'bookmarks' => 'Paling banyak bookmark',
            'rating' => 'Rating tertinggi',
            'latest_update' => 'Terbaru diupdate',
            'title_asc' => 'Judul A-Z',
            'title_desc' => 'Judul Z-A',
        ],
        'featured' => $sorted->first() ?? $baseFiltered->first() ?? $comics->first(),
    ]);
})->name('comics.index');

Route::get('/comics/{slug}', [ComicPageController::class, 'show'])->name('comics.show');


Route::get('/search/comics', [ComicController::class, 'search'])
    ->name('comics.search');

Route::get('/comics/{slug}/chapters/{chapter}', [ReaderController::class, 'show'])
    ->whereNumber('chapter')
    ->name('chapters.show');
Route::post('/comics/{slug}/chapters/{chapter}/comments', [ReaderController::class, 'storeComment'])
    ->whereNumber('chapter')
    ->middleware(['auth', 'throttle:reader-feedback'])
    ->name('chapters.comments.store');
Route::post('/comics/{slug}/chapters/{chapter}/comments/{comment}/vote', [ReaderController::class, 'voteComment'])
    ->whereNumber('chapter')
    ->middleware('throttle:reader-actions')
    ->name('chapters.comments.vote');
Route::patch('/comics/{slug}/chapters/{chapter}/comments/{comment}', [ReaderController::class, 'updateComment'])
    ->whereNumber('chapter')
    ->middleware(['auth', 'throttle:reader-feedback'])
    ->name('chapters.comments.update');
Route::delete('/comics/{slug}/chapters/{chapter}/comments/{comment}', [ReaderController::class, 'destroyComment'])
    ->whereNumber('chapter')
    ->middleware(['auth', 'throttle:reader-feedback'])
    ->name('chapters.comments.destroy');
Route::post('/comics/{slug}/chapters/{chapter}/reactions', [ReaderController::class, 'toggleReaction'])
    ->whereNumber('chapter')
    ->middleware('throttle:reader-actions')
    ->name('chapters.reactions.toggle');
Route::post('/comics/{slug}/feedback', [ReaderController::class, 'storeComicComment'])
    ->middleware(['auth', 'throttle:reader-feedback'])
    ->name('comics.comments.store');
Route::post('/comics/{slug}/feedback/{comment}/vote', [ReaderController::class, 'voteComicComment'])
    ->middleware('throttle:reader-actions')
    ->name('comics.comments.vote');
Route::patch('/comics/{slug}/feedback/{comment}', [ReaderController::class, 'updateComicComment'])
    ->middleware(['auth', 'throttle:reader-feedback'])
    ->name('comics.comments.update');
Route::delete('/comics/{slug}/feedback/{comment}', [ReaderController::class, 'destroyComicComment'])
    ->middleware(['auth', 'throttle:reader-feedback'])
    ->name('comics.comments.destroy');
Route::post('/comics/{slug}/reactions', [ReaderController::class, 'toggleComicReaction'])
    ->middleware('throttle:reader-actions')
    ->name('comics.reactions.toggle');

Route::middleware('auth')->group(function () {
    Route::get('/messages', [MessageController::class, 'index'])->name('messages');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/comics/{slug}/bookmark', [ReaderController::class, 'toggleBookmark'])->middleware('throttle:reader-actions')->name('comics.bookmarks.toggle');
    Route::post('/comics/{slug}/rating', [ReaderController::class, 'rateComic'])->middleware('throttle:reader-actions')->name('comics.ratings.store');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.store');
    Route::get('/two-factor', [AdminAuthController::class, 'showTwoFactorChallenge'])->name('two-factor.challenge');
    Route::post('/two-factor', [AdminAuthController::class, 'verifyTwoFactor'])->middleware('throttle:admin-2fa')->name('two-factor.verify');
    Route::post('/two-factor/resend', [AdminAuthController::class, 'resendTwoFactor'])->middleware('throttle:admin-2fa')->name('two-factor.resend');

    Route::middleware(['auth', 'admin', 'throttle:admin-panel'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::redirect('/', '/admin/comics')->name('home');
        Route::get('comments', [AdminCommentController::class, 'index'])->name('comments.index');
        Route::match(['post', 'patch'], 'comments/bulk', [AdminCommentController::class, 'bulkUpdate'])->name('comments.bulk');
        Route::patch('comments/{comment}/visibility', [AdminCommentController::class, 'updateVisibility'])->name('comments.visibility');
        Route::delete('comments/{comment}', [AdminCommentController::class, 'destroy'])->name('comments.destroy');
        Route::get('comic-comments', [AdminComicCommentController::class, 'index'])->name('comic-comments.index');
        Route::post('comic-comments/bulk', [AdminComicCommentController::class, 'bulkUpdate'])->name('comic-comments.bulk');
        Route::patch('comic-comments/{comment}/visibility', [AdminComicCommentController::class, 'updateVisibility'])->name('comic-comments.visibility');
        Route::delete('comic-comments/{comment}', [AdminComicCommentController::class, 'destroy'])->name('comic-comments.destroy');
        Route::post('users/{user}/warn', [AdminUserModerationController::class, 'warn'])->name('users.warn');
        Route::post('users/{user}/hide-comments', [AdminUserModerationController::class, 'hideComments'])->name('users.hide-comments');
        Route::post('users/{user}/suspend', [AdminUserModerationController::class, 'suspend'])->name('users.suspend');
        Route::post('users/{user}/ban', [AdminUserModerationController::class, 'ban'])->name('users.ban');
        Route::post('users/{user}/clear-restrictions', [AdminUserModerationController::class, 'clearRestrictions'])->name('users.clear-restrictions');
        Route::get('curation', [AdminComicController::class, 'curation'])->name('comics.curation');
        Route::patch('curation/{comic}', [AdminComicController::class, 'updateCuration'])->name('comics.curation.update');
        Route::get('chapters', [AdminChapterController::class, 'index'])->name('chapters.index');
        Route::patch('chapters/{chapter}/publication', [AdminChapterController::class, 'updatePublication'])->name('chapters.publication');
        Route::resource('comics', AdminComicController::class)->except('show');

        Route::get('comics/{comic}/chapters/create', [AdminChapterController::class, 'create'])->name('chapters.create');
        Route::post('comics/{comic}/chapters', [AdminChapterController::class, 'store'])->name('chapters.store');
        Route::get('comics/{comic}/chapters/{chapter}/edit', [AdminChapterController::class, 'edit'])->name('chapters.edit');
        Route::put('comics/{comic}/chapters/{chapter}', [AdminChapterController::class, 'update'])->name('chapters.update');
        Route::delete('comics/{comic}/chapters/{chapter}', [AdminChapterController::class, 'destroy'])->name('chapters.destroy');
    });
});

require __DIR__.'/auth.php';
