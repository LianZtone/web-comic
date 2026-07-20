<?php

namespace App\Providers;

use App\Models\ChapterComment;
use App\Models\ComicComment;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.partials.navbar', function ($view) {
            $unreadNotificationCount = 0;
            $recentNotifications = collect();

            if (Auth::check() && Schema::hasTable('notifications')) {
                $user = Auth::user();
                $unreadNotificationCount = $user->unreadNotifications()->count();
                $recentNotifications = $user->notifications()->latest()->limit(5)->get();
            }

            $view->with([
                'unreadNotificationCount' => $unreadNotificationCount,
                'recentNotifications' => $recentNotifications,
            ]);
        });

        View::composer('layouts.partials.admin-sidebar', function ($view) {
            $adminSidebarCounts = [
                'hiddenChapterComments' => 0,
                'hiddenComicComments' => 0,
            ];

            if (
                Auth::check()
                && (bool) Auth::user()?->is_admin
                && Schema::hasTable('chapter_comments')
                && Schema::hasTable('comic_comments')
            ) {
                $adminSidebarCounts['hiddenChapterComments'] = ChapterComment::query()
                    ->where('is_visible', false)
                    ->count();
                $adminSidebarCounts['hiddenComicComments'] = ComicComment::query()
                    ->where('is_visible', false)
                    ->count();
            }

            $view->with('adminSidebarCounts', $adminSidebarCounts);
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by(
                mb_strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });

        RateLimiter::for('admin-panel', function (Request $request) {
            return Limit::perMinute(180)->by(
                ($request->user()?->getAuthIdentifier() ?? 'guest').'|'.$request->ip()
            );
        });

        RateLimiter::for('admin-2fa', function (Request $request) {
            return Limit::perMinute(6)->by(
                ($this->requestSessionId($request) ?? 'guest').'|'.$request->ip()
            );
        });

        RateLimiter::for('reader-feedback', function (Request $request) {
            return Limit::perMinute(8)->by(
                ($request->user()?->getAuthIdentifier() ?? $this->requestSessionId($request) ?? $this->requestFingerprint($request)).'|'.$request->ip()
            );
        });

        RateLimiter::for('reader-actions', function (Request $request) {
            return Limit::perMinute(30)->by(
                ($request->user()?->getAuthIdentifier() ?? $this->requestSessionId($request) ?? $this->requestFingerprint($request)).'|'.$request->ip()
            );
        });
    }

    private function requestSessionId(Request $request): ?string
    {
        if (! method_exists($request, 'hasSession') || ! $request->hasSession()) {
            return null;
        }

        return $request->session()->getId();
    }

    private function requestFingerprint(Request $request): string
    {
        return 'guest:'.hash('sha256', (string) $request->ip().'|'.(string) $request->userAgent());
    }
}
