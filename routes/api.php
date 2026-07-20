<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\ComicApiController;
use App\Http\Controllers\ReaderController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/login', [AuthApiController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthApiController::class, 'me']);
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::post('/logout-all', [AuthApiController::class, 'logoutAll']);
    });
});

Route::get('/home', [ComicApiController::class, 'home']);
Route::get('/explore', [ComicApiController::class, 'explore']);

Route::prefix('comics')->group(function () {
    Route::get('/', [ComicApiController::class, 'index']);
    Route::get('/{slug}', [ComicApiController::class, 'show']);
    Route::get('/{slug}/feedback', [ComicApiController::class, 'comicFeedback']);
    Route::get('/{slug}/chapters/{chapter}', [ComicApiController::class, 'showChapter'])
        ->whereNumber('chapter');
    Route::get('/{slug}/chapters/{chapter}/feedback', [ComicApiController::class, 'chapterFeedback'])
        ->whereNumber('chapter');

    Route::post('/{slug}/reactions', [ReaderController::class, 'toggleComicReaction'])
        ->middleware('throttle:reader-actions');
    Route::post('/{slug}/feedback', [ReaderController::class, 'storeComicComment'])
        ->middleware(['auth:sanctum', 'throttle:reader-feedback']);
    Route::post('/{slug}/feedback/{comment}/vote', [ReaderController::class, 'voteComicComment'])
        ->middleware('throttle:reader-actions');
    Route::patch('/{slug}/feedback/{comment}', [ReaderController::class, 'updateComicComment'])
        ->middleware(['auth:sanctum', 'throttle:reader-feedback']);
    Route::delete('/{slug}/feedback/{comment}', [ReaderController::class, 'destroyComicComment'])
        ->middleware(['auth:sanctum', 'throttle:reader-feedback']);

    Route::post('/{slug}/chapters/{chapter}/comments', [ReaderController::class, 'storeComment'])
        ->whereNumber('chapter')
        ->middleware(['auth:sanctum', 'throttle:reader-feedback']);
    Route::post('/{slug}/chapters/{chapter}/comments/{comment}/vote', [ReaderController::class, 'voteComment'])
        ->whereNumber('chapter')
        ->middleware('throttle:reader-actions');
    Route::patch('/{slug}/chapters/{chapter}/comments/{comment}', [ReaderController::class, 'updateComment'])
        ->whereNumber('chapter')
        ->middleware(['auth:sanctum', 'throttle:reader-feedback']);
    Route::delete('/{slug}/chapters/{chapter}/comments/{comment}', [ReaderController::class, 'destroyComment'])
        ->whereNumber('chapter')
        ->middleware(['auth:sanctum', 'throttle:reader-feedback']);
    Route::post('/{slug}/chapters/{chapter}/reactions', [ReaderController::class, 'toggleReaction'])
        ->whereNumber('chapter')
        ->middleware('throttle:reader-actions');
});

Route::middleware(['auth:sanctum', 'throttle:reader-actions'])->group(function () {
    Route::post('/comics/{slug}/bookmark', [ReaderController::class, 'toggleBookmark']);
    Route::post('/comics/{slug}/rating', [ReaderController::class, 'rateComic']);
});
