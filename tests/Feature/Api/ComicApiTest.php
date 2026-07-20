<?php

namespace Tests\Feature\Api;

use App\Models\ChapterReaction;
use App\Models\Comic;
use App\Models\User;
use App\Support\ComicCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ComicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_comics_index_returns_json_list_with_meta(): void
    {
        $response = $this->getJson('/api/comics');

        $response->assertOk();
        $response->assertJsonStructure([
            'data',
            'meta' => [
                'total',
                'per_page',
                'current_page',
                'last_page',
                'from',
                'to',
                'filters' => [
                    'q',
                    'genres',
                    'status',
                    'type',
                    'order_by',
                ],
            ],
        ]);
        $response->assertJsonPath('meta.current_page', 1);
    }

    public function test_comic_detail_can_be_loaded(): void
    {
        $comic = ComicCatalog::all()->firstOrFail();

        $response = $this->getJson('/api/comics/'.$comic['slug']);

        $response->assertOk();
        $response->assertJsonPath('data.slug', $comic['slug']);
        $response->assertJsonPath('data.title', $comic['title']);
    }

    public function test_comic_chapter_can_be_loaded(): void
    {
        $comic = ComicCatalog::all()->firstOrFail();
        $chapter = $comic['first_chapter'];

        $response = $this->getJson('/api/comics/'.$comic['slug'].'/chapters/'.$chapter['number']);

        $response->assertOk();
        $response->assertJsonPath('data.comic.slug', $comic['slug']);
        $response->assertJsonPath('data.chapter.number', $chapter['number']);
    }

    public function test_api_bookmark_toggle_returns_json(): void
    {
        $user = User::factory()->create();
        $comic = $this->createBackendComic();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/comics/'.$comic->slug.'/bookmark');

        $response->assertOk();
        $response->assertJsonPath('bookmarked', true);
        $this->assertDatabaseHas('comic_bookmarks', [
            'comic_id' => $comic->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_api_rating_store_returns_json(): void
    {
        $user = User::factory()->create();
        $comic = $this->createBackendComic();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/comics/'.$comic->slug.'/rating', [
            'score' => 5,
        ]);

        $response->assertOk();
        $response->assertJsonPath('score', 5);
        $this->assertDatabaseHas('comic_ratings', [
            'comic_id' => $comic->id,
            'user_id' => $user->id,
            'score' => 5,
        ]);
    }

    public function test_api_chapter_reaction_can_toggle_for_guest(): void
    {
        $comic = $this->createBackendComic();
        $chapter = $comic->chapters()->firstOrFail();

        $response = $this->postJson('/api/comics/'.$comic->slug.'/chapters/'.$chapter->number.'/reactions', [
            'type' => 'like',
        ]);

        $response->assertOk();
        $this->assertSame(
            1,
            ChapterReaction::query()->where('chapter_id', $chapter->id)->where('type', 'like')->count()
        );
    }

    public function test_api_chapter_comment_store_returns_json(): void
    {
        $user = User::factory()->create([
            'name' => 'API Reader',
        ]);
        $comic = $this->createBackendComic();
        $chapter = $comic->chapters()->firstOrFail();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/comics/'.$comic->slug.'/chapters/'.$chapter->number.'/comments', [
            'body' => 'Komentar dari API.',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('comment.text', 'Komentar dari API.');
        $this->assertDatabaseHas('chapter_comments', [
            'chapter_id' => $chapter->id,
            'display_name' => 'API Reader',
            'body' => 'Komentar dari API.',
        ]);
    }

    public function test_api_auth_register_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Reader API',
            'email' => 'reader.api@example.test',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'token_type',
            'access_token',
            'user' => ['id', 'name', 'email'],
        ]);
    }

    public function test_api_auth_login_me_and_logout_flow(): void
    {
        $user = User::factory()->create([
            'email' => 'login.api@example.test',
            'password' => bcrypt('Password123!'),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
            'device_name' => 'phpunit',
        ]);

        $login->assertOk();
        $token = (string) $login->json('access_token');
        $this->assertNotSame('', $token);

        $me = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me');
        $me->assertOk();
        $me->assertJsonPath('user.email', $user->email);

        $logout = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/logout');
        $logout->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    private function createBackendComic(): Comic
    {
        $comic = Comic::query()->create([
            'title' => 'API Backend Comic',
            'slug' => 'api-backend-comic-'.Str::lower(Str::random(6)),
            'summary' => 'Summary',
            'author' => 'Tester',
            'status' => 'Ongoing',
            'genres' => ['Action'],
        ]);

        $comic->chapters()->create([
            'number' => 1,
            'title' => 'Chapter 1',
            'summary' => 'Intro',
            'pages' => ['Panel 1', 'Panel 2'],
            'is_published' => true,
        ]);

        return $comic;
    }
}
