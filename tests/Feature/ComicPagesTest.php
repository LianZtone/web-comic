<?php

namespace Tests\Feature;

use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\ChapterCommentVote;
use App\Models\ChapterReaction;
use App\Models\Comic;
use App\Models\ComicComment;
use App\Models\ComicCommentVote;
use App\Models\ComicReaction;
use App\Models\User;
use App\Support\ComicCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ComicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_is_accessible(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Velmics');
        $response->assertSee('Rekomendasi');
    }

    public function test_library_page_supports_filters(): void
    {
        $response = $this->get(route('comics.index', ['genre' => 'Sci-Fi']));

        $response->assertOk();
        $response->assertSee('Hasil pencarian');
        $response->assertSee('Afterglow Protocol');
    }

    public function test_comic_detail_page_is_accessible(): void
    {
        $comic = ComicCatalog::all()->first();

        $response = $this->get(route('comics.show', $comic['slug']));

        $response->assertOk();
        $response->assertSee($comic['title']);
        $response->assertSee('Daftar chapter');
    }

    public function test_reader_page_is_accessible(): void
    {
        $comic = ComicCatalog::all()->first();
        $chapter = $comic['first_chapter'];

        $response = $this->get(route('chapters.show', [
            'slug' => $comic['slug'],
            'chapter' => $chapter['number'],
        ]));

        $response->assertOk();
        $response->assertSee($chapter['title']);
        $response->assertSee('Bagaimana chapter ini menurutmu?');
    }

    public function test_reader_comment_can_be_stored(): void
    {
        $user = User::factory()->create([
            'name' => 'Pembaca Login',
        ]);

        $comic = Comic::query()->create([
            'title' => 'Reader Backend Comic',
            'slug' => 'reader-backend-comic',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Opening',
            'summary' => 'Intro chapter',
            'pages' => ['Panel 1', 'Panel 2'],
            'is_published' => true,
        ]);

        $this->actingAs($user)->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $response = $this->actingAs($user)->post(route('chapters.comments.store', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]), [
            'body' => 'Komentarnya masuk dari backend.',
            'captcha_answer' => $this->captchaAnswer('reader-comment'),
        ]);

        $response->assertRedirect(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $this->assertDatabaseHas((new ChapterComment())->getTable(), [
            'chapter_id' => $chapter->id,
            'display_name' => 'Pembaca Login',
        ]);

        $this->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]))
            ->assertOk()
            ->assertSee('Komentarnya masuk dari backend.');
    }

    public function test_reader_comment_rejects_spam_links(): void
    {
        $user = User::factory()->create([
            'name' => 'Pembaca Login',
        ]);

        $comic = Comic::query()->create([
            'title' => 'Spam Filter Comic',
            'slug' => 'spam-filter-comic',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Opening',
            'summary' => 'Intro chapter',
            'pages' => ['Panel 1'],
            'is_published' => true,
        ]);

        $this->actingAs($user)->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $this->actingAs($user)->from(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]))->post(route('chapters.comments.store', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]), [
            'body' => 'slot gacor terbaik di https://jahat.test',
            'captcha_answer' => $this->captchaAnswer('reader-comment'),
        ])->assertRedirect(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]))->assertSessionHasErrors('body');

        $this->assertDatabaseMissing((new ChapterComment())->getTable(), [
            'chapter_id' => $chapter->id,
            'display_name' => 'Pembaca Login',
        ]);
    }

    public function test_guest_is_redirected_when_trying_to_store_reader_comment(): void
    {
        $comic = Comic::query()->create([
            'title' => 'Guest Comment Comic',
            'slug' => 'guest-comment-comic',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Opening',
            'summary' => 'Intro chapter',
            'pages' => ['Panel 1'],
            'is_published' => true,
        ]);

        $this->post(route('chapters.comments.store', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]), [
            'body' => 'Komentar tamu.',
            'captcha_answer' => '4',
        ])->assertRedirect(route('login'));
    }

    public function test_reader_reaction_can_be_toggled(): void
    {
        $comic = Comic::query()->create([
            'title' => 'Reader Reaction Comic',
            'slug' => 'reader-reaction-comic',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Opening',
            'summary' => 'Intro chapter',
            'pages' => ['Panel 1', 'Panel 2'],
            'is_published' => true,
        ]);

        $this->post(route('chapters.reactions.toggle', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]), [
            'type' => 'like',
        ])->assertRedirect();

        $this->assertSame(1, ChapterReaction::query()->where('chapter_id', $chapter->id)->where('type', 'like')->count());

        $this->post(route('chapters.reactions.toggle', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]), [
            'type' => 'like',
        ])->assertRedirect();

        $this->assertSame(0, ChapterReaction::query()->where('chapter_id', $chapter->id)->where('type', 'like')->count());
    }

    public function test_comic_feedback_supports_replies_and_comment_votes(): void
    {
        $owner = User::factory()->create([
            'name' => 'Pemilik Komentar',
        ]);

        $user = User::factory()->create([
            'name' => 'Pembaca Comic',
        ]);

        $comic = Comic::query()->create([
            'title' => 'Comic Reply Backend',
            'slug' => 'comic-reply-backend',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $captchaSession = [
            'form_captcha.series-feedback' => [
                'question' => '1 + 1 = ?',
                'answer' => '2',
                'generated_at' => now()->timestamp,
            ],
        ];

        $this->actingAs($owner)->withSession($captchaSession)->post(route('comics.comments.store', $comic->slug), [
            'body' => 'Komentar utama untuk comic.',
            'score' => 5,
            'captcha_answer' => '2',
        ])->assertRedirect(route('comics.show', $comic->slug).'#series-feedback');

        $comment = ComicComment::query()->where('comic_id', $comic->id)->whereNull('parent_id')->firstOrFail();

        $this->actingAs($user)->withSession($captchaSession)->post(route('comics.comments.store', $comic->slug), [
            'body' => 'Ini balasan untuk komentar comic.',
            'parent_id' => $comment->id,
            'score' => 5,
            'captcha_answer' => '2',
        ])->assertRedirect(route('comics.show', $comic->slug).'#series-feedback');

        $reply = ComicComment::query()
            ->where('comic_id', $comic->id)
            ->where('parent_id', $comment->id)
            ->firstOrFail();

        $this->actingAs($owner)->withSession($captchaSession)->post(route('comics.comments.store', $comic->slug), [
            'body' => 'Balasan tingkat tiga untuk comic.',
            'parent_id' => $reply->id,
            'score' => 5,
            'captcha_answer' => '2',
        ])->assertRedirect(route('comics.show', $comic->slug).'#series-feedback');

        $this->assertDatabaseHas((new ComicComment())->getTable(), [
            'comic_id' => $comic->id,
            'display_name' => 'Pembaca Comic',
            'parent_id' => $comment->id,
        ]);

        $this->assertDatabaseHas((new ComicComment())->getTable(), [
            'comic_id' => $comic->id,
            'display_name' => 'Pemilik Komentar',
            'parent_id' => $reply->id,
        ]);

        $this->assertSame(1, $owner->fresh()->unreadNotifications()->count());

        $this->post(route('comics.comments.vote', [
            'slug' => $comic->slug,
            'comment' => $comment->id,
        ]), [
            'vote' => 'like',
        ])->assertRedirect(route('comics.show', $comic->slug).'#series-feedback');

        $this->assertDatabaseHas((new ComicCommentVote())->getTable(), [
            'comic_comment_id' => $comment->id,
            'vote' => 'like',
        ]);

        $this->actingAs($owner)
            ->get(route('messages'))
            ->assertOk()
            ->assertSee('Balasan baru di feedback comic');

        $this->assertSame(0, $owner->fresh()->unreadNotifications()->count());
        $this->assertSame(1, $user->fresh()->unreadNotifications()->count());
    }

    public function test_comic_reaction_can_be_toggled(): void
    {
        $comic = Comic::query()->create([
            'title' => 'Comic Reaction Backend',
            'slug' => 'comic-reaction-backend',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $this->post(route('comics.reactions.toggle', [
            'slug' => $comic->slug,
        ]), [
            'type' => 'like',
        ])->assertRedirect(route('comics.show', $comic->slug).'#series-feedback');

        $this->assertSame(1, ComicReaction::query()->where('comic_id', $comic->id)->where('type', 'like')->count());

        $this->post(route('comics.reactions.toggle', [
            'slug' => $comic->slug,
        ]), [
            'type' => 'like',
        ])->assertRedirect(route('comics.show', $comic->slug).'#series-feedback');

        $this->assertSame(0, ComicReaction::query()->where('comic_id', $comic->id)->where('type', 'like')->count());
    }

    public function test_comment_owner_can_edit_and_delete_chapter_comment(): void
    {
        $user = User::factory()->create([
            'name' => 'Pemilik Chapter',
        ]);

        $comic = Comic::query()->create([
            'title' => 'Chapter Edit Delete',
            'slug' => 'chapter-edit-delete',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Opening',
            'summary' => 'Intro chapter',
            'pages' => ['Panel 1'],
            'is_published' => true,
        ]);

        $this->actingAs($user)->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $this->actingAs($user)->post(route('chapters.comments.store', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]), [
            'body' => 'Komentar lama chapter.',
            'captcha_answer' => $this->captchaAnswer('reader-comment'),
        ]);

        $comment = ChapterComment::query()->where('chapter_id', $chapter->id)->firstOrFail();

        $this->actingAs($user)->patch(route('chapters.comments.update', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
            'comment' => $comment->id,
        ]), [
            'body' => 'Komentar chapter sudah diedit.',
        ])->assertRedirect(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $this->assertDatabaseHas((new ChapterComment())->getTable(), [
            'id' => $comment->id,
            'body' => 'Komentar chapter sudah diedit.',
        ]);

        $this->actingAs($user)->delete(route('chapters.comments.destroy', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
            'comment' => $comment->id,
        ]))->assertRedirect(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $this->assertDatabaseMissing((new ChapterComment())->getTable(), [
            'id' => $comment->id,
        ]);
    }

    public function test_comment_owner_can_edit_and_delete_comic_comment(): void
    {
        $user = User::factory()->create([
            'name' => 'Pemilik Comic',
        ]);

        $comic = Comic::query()->create([
            'title' => 'Comic Edit Delete',
            'slug' => 'comic-edit-delete',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $captchaSession = [
            'form_captcha.series-feedback' => [
                'question' => '1 + 1 = ?',
                'answer' => '2',
                'generated_at' => now()->timestamp,
            ],
        ];

        $this->actingAs($user)->withSession($captchaSession)->post(route('comics.comments.store', $comic->slug), [
            'body' => 'Komentar lama comic.',
            'score' => 5,
            'captcha_answer' => '2',
        ]);

        $comment = ComicComment::query()->where('comic_id', $comic->id)->firstOrFail();

        $this->actingAs($user)->patch(route('comics.comments.update', [
            'slug' => $comic->slug,
            'comment' => $comment->id,
        ]), [
            'body' => 'Komentar comic sudah diedit.',
        ])->assertRedirect(route('comics.show', $comic->slug).'#series-feedback');

        $this->assertDatabaseHas((new ComicComment())->getTable(), [
            'id' => $comment->id,
            'body' => 'Komentar comic sudah diedit.',
        ]);

        $this->actingAs($user)->delete(route('comics.comments.destroy', [
            'slug' => $comic->slug,
            'comment' => $comment->id,
        ]))->assertRedirect(route('comics.show', $comic->slug).'#series-feedback');

        $this->assertDatabaseMissing((new ComicComment())->getTable(), [
            'id' => $comment->id,
        ]);
    }

    public function test_reader_page_keeps_spoiler_comments_marked_after_reload(): void
    {
        $comic = Comic::query()->create([
            'title' => 'Chapter Spoiler Persist',
            'slug' => 'chapter-spoiler-persist',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Opening',
            'summary' => 'Intro chapter',
            'pages' => ['Panel 1'],
            'is_published' => true,
        ]);

        $chapter->comments()->create([
            'display_name' => 'Pembaca Spoiler',
            'body' => 'Komentar spoiler chapter.',
            'is_spoiler' => true,
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $response = $this->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $response->assertOk();
        $response->assertSee('Tampilkan spoiler');
        $response->assertSee('spoiler');
        $response->assertSee('Komentar spoiler chapter.');
    }

    public function test_reader_comment_can_store_an_uploaded_image(): void
    {
        $user = User::factory()->create([
            'name' => 'Pembaca Image',
        ]);

        $comic = Comic::query()->create([
            'title' => 'Reader Image Comic',
            'slug' => 'reader-image-comic',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Opening',
            'summary' => 'Intro chapter',
            'pages' => ['Panel 1'],
            'is_published' => true,
        ]);

        $this->actingAs($user)->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $this->actingAs($user)->post(route('chapters.comments.store', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]), [
            'body' => '',
            'comment_image' => UploadedFile::fake()->image('reader-comment.png'),
            'captcha_answer' => $this->captchaAnswer('reader-comment'),
        ])->assertRedirect(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $comment = ChapterComment::query()->where('chapter_id', $chapter->id)->firstOrFail();

        $this->assertNotNull($comment->image_path);
        $this->assertFileExists(public_path($comment->image_path));

        $this->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]))
            ->assertOk()
            ->assertSee($comment->image_path);
    }

    public function test_comic_page_keeps_spoiler_comments_marked_after_reload(): void
    {
        $catalogComic = ComicCatalog::all()->first();

        $comic = Comic::query()->create([
            'title' => $catalogComic['title'],
            'slug' => $catalogComic['slug'],
            'summary' => $catalogComic['summary'],
            'author' => $catalogComic['author'],
            'status' => $catalogComic['status'],
            'genres' => $catalogComic['genres'],
        ]);

        $comic->comments()->create([
            'display_name' => 'Pembaca Spoiler',
            'body' => 'Komentar spoiler comic.',
            'score' => 5,
            'is_spoiler' => true,
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $response = $this->get(route('comics.show', $comic->slug));

        $response->assertOk();
        $response->assertSee('Tampilkan spoiler');
        $response->assertSee('spoiler');
        $response->assertSee('Komentar spoiler comic.');
    }

    public function test_comic_feedback_can_store_an_uploaded_image(): void
    {
        $user = User::factory()->create([
            'name' => 'Pembaca Gambar Comic',
        ]);

        $catalogComic = ComicCatalog::all()->first();

        $comic = Comic::query()->create([
            'title' => $catalogComic['title'],
            'slug' => $catalogComic['slug'],
            'summary' => $catalogComic['summary'],
            'author' => $catalogComic['author'],
            'status' => $catalogComic['status'],
            'genres' => $catalogComic['genres'],
        ]);

        $captchaSession = [
            'form_captcha.series-feedback' => [
                'question' => '1 + 1 = ?',
                'answer' => '2',
                'generated_at' => now()->timestamp,
            ],
        ];

        $this->actingAs($user)->withSession($captchaSession)->post(route('comics.comments.store', $comic->slug), [
            'body' => '',
            'score' => 5,
            'comment_image' => UploadedFile::fake()->image('comic-comment.png'),
            'captcha_answer' => '2',
        ])->assertRedirect(route('comics.show', $comic->slug).'#series-feedback');

        $comment = ComicComment::query()->where('comic_id', $comic->id)->firstOrFail();

        $this->assertNotNull($comment->image_path);
        $this->assertFileExists(public_path($comment->image_path));

        $this->get(route('comics.show', $comic->slug))
            ->assertOk()
            ->assertSee($comment->image_path);
    }

    public function test_chapter_feedback_flattens_nested_replies_into_one_visible_thread_level(): void
    {
        $comic = Comic::query()->create([
            'title' => 'Chapter Flat Thread',
            'slug' => 'chapter-flat-thread',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Opening',
            'summary' => 'Intro chapter',
            'pages' => ['Panel 1'],
            'is_published' => true,
        ]);

        $root = $chapter->comments()->create([
            'display_name' => 'Komentar 1',
            'body' => 'Komentar utama chapter.',
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $replyOne = $chapter->comments()->create([
            'display_name' => 'Komentar 2',
            'body' => 'Balasan pertama chapter.',
            'parent_id' => $root->id,
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $replyTwo = $chapter->comments()->create([
            'display_name' => 'Komentar 3',
            'body' => 'Balasan kedua chapter.',
            'parent_id' => $replyOne->id,
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $chapter->comments()->create([
            'display_name' => 'Komentar 1',
            'body' => 'Balasan ketiga chapter.',
            'parent_id' => $replyTwo->id,
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $response = $this->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
        ]));

        $response->assertOk();
        $response->assertSee('Balasan pertama chapter.');
        $response->assertSee('Balasan kedua chapter.');
        $response->assertSee('Balasan ketiga chapter.');
        $response->assertSee('Tampilkan 2 balasan');
        $this->assertSame(1, substr_count($response->getContent(), 'data-reader-replies-shell'));
    }

    public function test_comic_feedback_flattens_nested_replies_into_one_visible_thread_level(): void
    {
        $catalogComic = ComicCatalog::all()->first();

        $comic = Comic::query()->create([
            'title' => $catalogComic['title'],
            'slug' => $catalogComic['slug'],
            'summary' => $catalogComic['summary'],
            'author' => $catalogComic['author'],
            'status' => $catalogComic['status'],
            'genres' => $catalogComic['genres'],
        ]);

        $root = $comic->comments()->create([
            'display_name' => 'Komentar 1',
            'body' => 'Komentar utama comic.',
            'score' => 5,
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $replyOne = $comic->comments()->create([
            'display_name' => 'Komentar 2',
            'body' => 'Balasan pertama comic.',
            'score' => 5,
            'parent_id' => $root->id,
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $replyTwo = $comic->comments()->create([
            'display_name' => 'Komentar 3',
            'body' => 'Balasan kedua comic.',
            'score' => 5,
            'parent_id' => $replyOne->id,
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $comic->comments()->create([
            'display_name' => 'Komentar 1',
            'body' => 'Balasan ketiga comic.',
            'score' => 5,
            'parent_id' => $replyTwo->id,
            'likes_count' => 0,
            'is_visible' => true,
        ]);

        $response = $this->get(route('comics.show', $comic->slug));

        $response->assertOk();
        $response->assertSee('Balasan pertama comic.');
        $response->assertSee('Balasan kedua comic.');
        $response->assertSee('Balasan ketiga comic.');
        $response->assertSee('Tampilkan 2 balasan');
        $this->assertSame(1, substr_count($response->getContent(), 'data-reader-replies-shell'));
    }

    public function test_comic_feedback_can_be_sorted_by_newest_oldest_and_popular(): void
    {
        $catalogComic = ComicCatalog::all()->first();

        $comic = Comic::query()->create([
            'title' => $catalogComic['title'],
            'slug' => $catalogComic['slug'],
            'summary' => $catalogComic['summary'],
            'author' => $catalogComic['author'],
            'status' => $catalogComic['status'],
            'genres' => $catalogComic['genres'],
        ]);

        $oldestComment = $comic->comments()->create([
            'display_name' => 'Komentar Lama',
            'body' => 'Komentar paling lama.',
            'score' => 5,
            'likes_count' => 0,
            'is_visible' => true,
        ]);
        $oldestComment->forceFill([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ])->saveQuietly();

        $popularComment = $comic->comments()->create([
            'display_name' => 'Komentar Populer',
            'body' => 'Komentar paling populer.',
            'score' => 5,
            'likes_count' => 0,
            'is_visible' => true,
        ]);
        $popularComment->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->saveQuietly();

        $newestComment = $comic->comments()->create([
            'display_name' => 'Komentar Baru',
            'body' => 'Komentar paling baru.',
            'score' => 5,
            'likes_count' => 0,
            'is_visible' => true,
        ]);
        $newestComment->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->saveQuietly();

        $popularReply = $comic->comments()->create([
            'display_name' => 'Pembalas Populer',
            'body' => 'Balasan untuk komentar populer.',
            'score' => 5,
            'parent_id' => $popularComment->id,
            'likes_count' => 0,
            'is_visible' => true,
        ]);
        $popularReply->forceFill([
            'created_at' => now()->subDay()->addHour(),
            'updated_at' => now()->subDay()->addHour(),
        ])->saveQuietly();

        ComicCommentVote::query()->create([
            'comic_comment_id' => $popularComment->id,
            'voter_key' => 'guest:popular-1',
            'vote' => 'like',
        ]);

        ComicCommentVote::query()->create([
            'comic_comment_id' => $popularComment->id,
            'voter_key' => 'guest:popular-2',
            'vote' => 'like',
        ]);

        ComicCommentVote::query()->create([
            'comic_comment_id' => $newestComment->id,
            'voter_key' => 'guest:newest-1',
            'vote' => 'like',
        ]);

        $this->get(route('comics.show', [
            'slug' => $comic->slug,
            'comment_sort' => 'newest',
        ]))
            ->assertOk()
            ->assertSeeInOrder([
                $newestComment->body,
                $popularComment->body,
                $oldestComment->body,
            ]);

        $this->get(route('comics.show', [
            'slug' => $comic->slug,
            'comment_sort' => 'oldest',
        ]))
            ->assertOk()
            ->assertSeeInOrder([
                $oldestComment->body,
                $popularComment->body,
                $newestComment->body,
            ]);

        $this->get(route('comics.show', [
            'slug' => $comic->slug,
            'comment_sort' => 'popular',
        ]))
            ->assertOk()
            ->assertSeeInOrder([
                $popularComment->body,
                $newestComment->body,
                $oldestComment->body,
            ]);
    }

    public function test_chapter_feedback_can_be_sorted_by_newest_oldest_and_popular(): void
    {
        $comic = Comic::query()->create([
            'title' => 'Chapter Sort Comic',
            'slug' => 'chapter-sort-comic',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $chapter = $comic->chapters()->create([
            'number' => 1,
            'title' => 'Chapter Sort',
            'summary' => 'Intro chapter',
            'pages' => ['Panel 1'],
            'is_published' => true,
        ]);

        $oldestComment = $chapter->comments()->create([
            'display_name' => 'Komentar Lama Chapter',
            'body' => 'Komentar chapter paling lama.',
            'likes_count' => 0,
            'is_visible' => true,
        ]);
        $oldestComment->forceFill([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ])->saveQuietly();

        $popularComment = $chapter->comments()->create([
            'display_name' => 'Komentar Populer Chapter',
            'body' => 'Komentar chapter paling populer.',
            'likes_count' => 0,
            'is_visible' => true,
        ]);
        $popularComment->forceFill([
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ])->saveQuietly();

        $newestComment = $chapter->comments()->create([
            'display_name' => 'Komentar Baru Chapter',
            'body' => 'Komentar chapter paling baru.',
            'likes_count' => 0,
            'is_visible' => true,
        ]);
        $newestComment->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->saveQuietly();

        $popularReply = $chapter->comments()->create([
            'display_name' => 'Pembalas Chapter',
            'body' => 'Balasan untuk komentar chapter populer.',
            'parent_id' => $popularComment->id,
            'likes_count' => 0,
            'is_visible' => true,
        ]);
        $popularReply->forceFill([
            'created_at' => now()->subDay()->addHour(),
            'updated_at' => now()->subDay()->addHour(),
        ])->saveQuietly();

        ChapterCommentVote::query()->create([
            'chapter_comment_id' => $popularComment->id,
            'voter_key' => 'guest:chapter-popular-1',
            'vote' => 'like',
        ]);

        ChapterCommentVote::query()->create([
            'chapter_comment_id' => $popularComment->id,
            'voter_key' => 'guest:chapter-popular-2',
            'vote' => 'like',
        ]);

        ChapterCommentVote::query()->create([
            'chapter_comment_id' => $newestComment->id,
            'voter_key' => 'guest:chapter-newest-1',
            'vote' => 'like',
        ]);

        $this->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
            'comment_sort' => 'newest',
        ]))
            ->assertOk()
            ->assertSeeInOrder([
                $newestComment->body,
                $popularComment->body,
                $oldestComment->body,
            ]);

        $this->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
            'comment_sort' => 'oldest',
        ]))
            ->assertOk()
            ->assertSeeInOrder([
                $oldestComment->body,
                $popularComment->body,
                $newestComment->body,
            ]);

        $this->get(route('chapters.show', [
            'slug' => $comic->slug,
            'chapter' => $chapter->number,
            'comment_sort' => 'popular',
        ]))
            ->assertOk()
            ->assertSeeInOrder([
                $popularComment->body,
                $newestComment->body,
                $oldestComment->body,
            ]);
    }

    public function test_admin_comics_page_is_accessible(): void
    {
        $admin = User::factory()->admin()->create();

        Comic::query()->create([
            'title' => 'Admin Test Comic',
            'slug' => 'admin-test-comic',
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.comics.index'));

        $response->assertOk();
        $response->assertSee('Kelola katalog komik');
        $response->assertSee('Admin Test Comic');
    }
}
