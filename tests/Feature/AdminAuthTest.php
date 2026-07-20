<?php

namespace Tests\Feature;

use App\Models\Comic;
use App\Models\ChapterComment;
use App\Models\User;
use App\Notifications\AdminTwoFactorCodeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.comics.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_page_is_accessible(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Masuk ke panel Velmics');
    }

    public function test_admin_login_page_sends_security_headers(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_admin_can_log_in_and_access_admin_area(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $captcha = $this->primeCaptcha(route('admin.login'), 'admin-login');

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'password',
            'captcha_answer' => $captcha,
        ])->assertRedirect(route('admin.two-factor.challenge'));

        $this->assertGuest();
        Notification::assertSentTo($admin, AdminTwoFactorCodeNotification::class);

        $notification = collect(Notification::sent($admin, AdminTwoFactorCodeNotification::class))->first();

        $this->post(route('admin.two-factor.verify'), [
            'code' => $notification->code,
        ])->assertRedirect(route('admin.comics.index'));

        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin.comics.index'))
            ->assertOk()
            ->assertSee('Kelola katalog komik');
    }

    public function test_non_admin_cannot_log_in_to_admin_panel(): void
    {
        $user = User::factory()->create([
            'email' => 'reader@example.com',
            'password' => 'password',
        ]);
        $captcha = $this->primeCaptcha(route('admin.login'), 'admin-login');

        $this->post(route('admin.login.store'), [
            'email' => 'reader@example.com',
            'password' => 'password',
            'captcha_answer' => $captcha,
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertNotNull($user);
    }

    public function test_admin_login_is_rate_limited_after_too_many_attempts(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->primeCaptcha(route('admin.login'), 'admin-login');

        foreach (range(1, 5) as $attempt) {
            $this->post(route('admin.login.store'), [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
                'captcha_answer' => $this->captchaAnswer('admin-login'),
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
            'captcha_answer' => $this->captchaAnswer('admin-login'),
        ])->assertStatus(429);
    }

    public function test_non_admin_gets_forbidden_in_admin_area(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.comics.index'))
            ->assertForbidden();
    }

    public function test_admin_can_log_out(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_admin_dashboard_supports_search_filter_and_pagination(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (range(1, 9) as $index) {
            Comic::query()->create([
                'title' => "Comic {$index}",
                'slug' => "comic-{$index}",
                'summary' => 'Summary',
                'author' => 'Seeder',
                'status' => 'Ongoing',
                'genres' => ['Action'],
            ]);
        }

        Comic::query()->create([
            'title' => 'Needle in Library',
            'slug' => 'needle-in-library',
            'summary' => 'Summary',
            'author' => 'Admin Search',
            'status' => 'Completed',
            'genres' => ['Drama'],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.comics.index', [
            'q' => 'Needle',
            'status' => 'Completed',
            'page' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Needle in Library');
        $response->assertSee('Total hasil');
    }

    public function test_admin_can_moderate_comments(): void
    {
        $admin = User::factory()->admin()->create();

        $comic = Comic::query()->create([
            'title' => 'Comment Moderation Comic',
            'slug' => 'comment-moderation-comic',
            'summary' => 'Summary',
            'author' => 'Admin',
            'status' => 'Ongoing',
            'genres' => ['Drama'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Opening',
            'summary' => 'Intro',
            'pages' => ['Page 1'],
            'is_published' => true,
        ]);

        $comment = ChapterComment::query()->create([
            'chapter_id' => $chapter->id,
            'display_name' => 'Guest Reader',
            'body' => 'Komentar yang perlu dimoderasi.',
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.comments.index', ['q' => 'Guest Reader', 'visibility' => 'visible']))
            ->assertOk()
            ->assertSee('Komentar yang perlu dimoderasi.')
            ->assertSee('Moderasi Komentar');

        $this->actingAs($admin)
            ->patch(route('admin.comments.visibility', $comment), [
                'is_visible' => 0,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('chapter_comments', [
            'id' => $comment->id,
            'is_visible' => false,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.comments.destroy', $comment))
            ->assertRedirect();

        $this->assertDatabaseMissing('chapter_comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_admin_can_upload_comic_artwork_and_chapter_images(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.comics.store'), [
                'title' => 'Upload Comic',
                'slug' => 'upload-comic',
                'summary' => 'Summary',
                'author' => 'Uploader',
                'status' => 'Ongoing',
                'comic_type' => 'Manhwa',
                'source_type' => 'Project',
                'genres' => ['Action', 'Fantasy'],
                'cover_image' => UploadedFile::fake()->image('cover.jpg', 400, 600),
                'banner_image' => UploadedFile::fake()->image('banner.jpg', 1200, 675),
            ])->assertRedirect(route('admin.comics.index'));

        $comic = Comic::query()->where('slug', 'upload-comic')->firstOrFail();

        $this->assertStringStartsWith('assets/komik/uploads/upload-comic/cover.', (string) $comic->cover_url);
        $this->assertStringStartsWith('assets/komik/uploads/upload-comic/banner.', (string) $comic->banner_url);
        $this->assertFileExists(public_path($comic->cover_url));
        $this->assertFileExists(public_path($comic->banner_url));

        $this->actingAs($admin)
            ->post(route('admin.chapters.store', $comic), [
                'number' => 1,
                'title' => 'Bab Upload',
                'release_label' => '20 Mar 2026',
                'summary' => 'Bab pertama dari upload.',
                'pages' => "Halaman pembuka\nHalaman penutup",
                'page_images' => [
                    UploadedFile::fake()->image('page-1.png', 800, 1200),
                    UploadedFile::fake()->image('page-2.png', 800, 1200),
                ],
                'is_published' => 1,
            ])->assertRedirect(route('admin.comics.edit', $comic));

        $chapter = $comic->fresh()->chapters()->firstOrFail();

        $this->assertCount(2, $chapter->pages);
        $this->assertSame('Halaman pembuka', $chapter->pages[0]['caption']);
        $this->assertSame('Halaman penutup', $chapter->pages[1]['caption']);
        $this->assertStringStartsWith('assets/komik/uploads/upload-comic/chapter-01/1.', $chapter->pages[0]['image']);
        $this->assertStringStartsWith('assets/komik/uploads/upload-comic/chapter-01/2.', $chapter->pages[1]['image']);
        $this->assertFileExists(public_path($chapter->pages[0]['image']));
        $this->assertFileExists(public_path($chapter->pages[1]['image']));

        File::deleteDirectory(public_path('assets/komik/uploads/upload-comic'));
    }

    public function test_admin_cannot_store_spammy_summary_or_unsafe_cover_reference(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.comics.create'))
            ->post(route('admin.comics.store'), [
                'title' => 'Spam Comic',
                'slug' => 'spam-comic',
                'summary' => 'Kunjungi slot gacor di https://jahat.test sekarang juga.',
                'author' => 'Uploader',
                'status' => 'Ongoing',
                'comic_type' => 'Manhwa',
                'source_type' => 'Project',
                'genres' => ['Action'],
            ])
            ->assertRedirect(route('admin.comics.create'))
            ->assertSessionHasErrors('summary');

        $this->actingAs($admin)
            ->from(route('admin.comics.create'))
            ->post(route('admin.comics.store'), [
                'title' => 'Unsafe Cover Comic',
                'slug' => 'unsafe-cover-comic',
                'summary' => 'Summary aman tanpa spam.',
                'author' => 'Uploader',
                'status' => 'Ongoing',
                'comic_type' => 'Manhwa',
                'source_type' => 'Project',
                'genres' => ['Action'],
                'cover_url' => 'data:image/svg+xml,<svg></svg>',
            ])
            ->assertRedirect(route('admin.comics.create'))
            ->assertSessionHasErrors('cover_url');

        $this->assertDatabaseMissing('comics', [
            'slug' => 'spam-comic',
        ]);

        $this->assertDatabaseMissing('comics', [
            'slug' => 'unsafe-cover-comic',
        ]);
    }

    public function test_admin_can_import_chapter_pages_from_public_folder(): void
    {
        $admin = User::factory()->admin()->create();

        $comic = Comic::query()->create([
            'title' => 'Folder Import Comic',
            'slug' => 'folder-import-comic',
            'summary' => 'Summary',
            'author' => 'Importer',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $directory = public_path('assets/komik/manga/testing-import/chapter1');
        File::ensureDirectoryExists($directory);

        $tinyPng = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9WlH0p8AAAAASUVORK5CYII=');
        File::put($directory.'/1.png', $tinyPng);
        File::put($directory.'/2.png', $tinyPng);

        try {
            $this->actingAs($admin)
                ->post(route('admin.chapters.store', $comic), [
                    'number' => 1,
                    'title' => 'Import Folder',
                    'release_label' => '20 Mar 2026',
                    'summary' => 'Chapter dari folder contoh.',
                    'page_source_folder' => 'assets/komik/manga/testing-import/chapter1',
                    'pages' => "Cover page\nEnding page",
                    'is_published' => 1,
                ])->assertRedirect(route('admin.comics.edit', $comic));

            $chapter = $comic->fresh()->chapters()->firstOrFail();

            $this->assertCount(2, $chapter->pages);
            $this->assertSame('assets/komik/manga/testing-import/chapter1/1.png', $chapter->pages[0]['image']);
            $this->assertSame('assets/komik/manga/testing-import/chapter1/2.png', $chapter->pages[1]['image']);
            $this->assertSame('Cover page', $chapter->pages[0]['caption']);
            $this->assertSame('Ending page', $chapter->pages[1]['caption']);
        } finally {
            File::deleteDirectory(public_path('assets/komik/manga/testing-import'));
        }
    }
}
