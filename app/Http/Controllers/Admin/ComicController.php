<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\ChapterComment;
use App\Models\ChapterReaction;
use App\Models\Comic;
use App\Support\ComicGenres;
use App\Support\ComicMetadata;
use App\Support\ComicMedia;
use App\Support\TextSanitizer;
use Illuminate\Http\Request;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ComicController extends Controller
{
    public function index(Request $request): View
    {
        $statusOptions = $this->statusOptions();
        $search = trim((string) $request->string('q'));
        $status = trim((string) $request->string('status'));
        $backendReady = $this->backendReady();
        $commentsReady = Schema::hasTable('chapter_comments');
        $reactionsReady = Schema::hasTable('chapter_reactions');

        if ($backendReady) {
            $query = Comic::query()->withCount('chapters');

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('artist', 'like', "%{$search}%");
                });
            }

            if ($status !== '' && in_array($status, $statusOptions, true)) {
                $query->where('status', $status);
            }

            $comics = $query
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->paginate(8)
                ->withQueryString();

            $stats = [
                'total_comics' => Comic::query()->count('*'),
                'total_chapters' => Chapter::query()->count('*'),
                'featured_total' => Comic::query()->where('is_featured', true)->count('*'),
                'total_comments' => $commentsReady ? ChapterComment::query()->count('*') : 0,
                // Catatan: kolom "is_visible" di chapter_comments bisa saja tidak ada pada schema versi lama.
                'hidden_comments' => $commentsReady
                    ? (Schema::hasColumn('chapter_comments', 'is_visible')
                        ? ChapterComment::query()->where('is_visible', false)->count('*')
                        : 0)
                    : 0,
                'total_reactions' => $reactionsReady ? ChapterReaction::query()->count('*') : 0,

            ];
            $recentChapters = Chapter::query()
                ->with('comic')
                ->latest('updated_at')
                ->take(5)
                ->get();
            $recentComments = $commentsReady
                ? ChapterComment::query()
                    ->with('chapter.comic')
                    ->latest()
                    ->take(5)
                    ->get()
                : collect();
        } else {
            $comics = new LengthAwarePaginator([], 0, 8);
            $stats = [
                'total_comics' => 0,
                'total_chapters' => 0,
                'featured_total' => 0,
                'total_comments' => 0,
                'hidden_comments' => 0,
                'total_reactions' => 0,
            ];
            $recentChapters = collect();
            $recentComments = collect();
        }

        return view('admin.comics.index', [
            'comics' => $comics,
            'stats' => $stats,
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
            'statusOptions' => $statusOptions,
            'setupRequired' => ! $backendReady,
            'commentsReady' => $commentsReady,
            'reactionsReady' => $reactionsReady,
            'recentChapters' => $recentChapters,
            'recentComments' => $recentComments,
        ]);
    }

    public function curation(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $backendReady = $this->backendReady();
        $curationReady = $this->curationReady();

        if ($backendReady) {
            $query = Comic::query()->withCount('chapters');

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('artist', 'like', "%{$search}%");
                });
            }

            $comics = $query
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->orderBy('title')
                ->paginate(12)
                ->withQueryString();
        } else {
            $comics = new LengthAwarePaginator([], 0, 12);
        }

        return view('admin.comics.curation', [
            'comics' => $comics,
            'filters' => [
                'q' => $search,
            ],
            'setupRequired' => ! $backendReady,
            'curationReady' => $curationReady,
            'stats' => [
                'featured' => $backendReady ? Comic::query()->where('is_featured', true)->count('*') : 0,
                'recommended' => $curationReady ? Comic::query()->where('is_recommended', true)->count('*') : 0,
                'admin_picks' => $curationReady ? Comic::query()->where('is_admin_pick', true)->count('*') : 0,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.comics.create', [
            'comic' => new Comic([
                'status' => 'Ongoing',
                'comic_type' => 'Manhwa',
                'source_type' => 'Project',
                'genres' => [],
                'features' => [],
                'is_featured' => false,
                'sort_order' => 0,
            ]),
            'statusOptions' => $this->statusOptions(),
            'genreOptions' => ComicGenres::all(),
            'formatOptions' => ComicMetadata::formats(),
            'sourceOptions' => ComicMetadata::sources(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data = $this->handleArtworkUploads($request, $data);
        Comic::query()->create($data);

        return redirect()
            ->route('admin.comics.index')
            ->with('success', 'Komik baru berhasil ditambahkan.');
    }

    public function edit(Comic $comic): View
    {
        $comic->load('chapters');

        return view('admin.comics.edit', [
            'comic' => $comic,
            'statusOptions' => $this->statusOptions(),
            'genreOptions' => ComicGenres::all(),
            'formatOptions' => ComicMetadata::formats(),
            'sourceOptions' => ComicMetadata::sources(),
        ]);
    }

    public function update(Request $request, Comic $comic): RedirectResponse
    {
        $data = $this->validatedData($request, $comic);
        $data = $this->handleArtworkUploads($request, $data, $comic);
        $comic->update($data);

        return redirect()
            ->route('admin.comics.edit', $comic)
            ->with('success', 'Data komik berhasil diperbarui.');
    }

    public function updateCuration(Request $request, Comic $comic): RedirectResponse
    {
        if (! $this->backendReady()) {
            return redirect()
                ->back()
                ->withErrors(['curation' => 'Backend komik belum siap. Jalankan migrasi terlebih dulu.']);
        }

        $rules = [
            'is_featured' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];

        if ($this->curationReady()) {
            $rules['is_recommended'] = ['required', 'boolean'];
            $rules['recommended_order'] = ['required', 'integer', 'min:0'];
            $rules['is_admin_pick'] = ['required', 'boolean'];
            $rules['admin_pick_order'] = ['required', 'integer', 'min:0'];
        }

        $data = $request->validate($rules);

        $payload = [
            'is_featured' => (bool) $data['is_featured'],
            'sort_order' => (int) $data['sort_order'],
        ];

        if ($this->curationReady()) {
            $payload['is_recommended'] = (bool) $data['is_recommended'];
            $payload['recommended_order'] = (int) $data['recommended_order'];
            $payload['is_admin_pick'] = (bool) $data['is_admin_pick'];
            $payload['admin_pick_order'] = (int) $data['admin_pick_order'];
        }

        $comic->update($payload);

        return redirect()
            ->back()
            ->with('success', 'Kurasi explore berhasil diperbarui.');
    }

    public function batchUpdateCuration(Request $request): RedirectResponse
    {
        if (! $this->backendReady()) {
            return redirect()
                ->back()
                ->withErrors(['curation' => 'Backend komik belum siap. Jalankan migrasi terlebih dulu.']);
        }

        $comicIds = collect($request->input('comic_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (count($comicIds) === 0) {
            return redirect()
                ->back()
                ->withErrors(['curation' => 'Tidak ada komik yang dipilih untuk diperbarui.']);
        }

        $rules = [
            'comic_ids' => ['required', 'array', 'min:1'],
            'comic_ids.*' => ['integer', 'distinct', 'min:1', Rule::exists('comics', 'id')],
            'is_featured' => ['required', 'array'],
            'is_featured.*' => ['required', 'boolean'],
            'sort_order' => ['required', 'array'],
            'sort_order.*' => ['required', 'integer', 'min:0'],
        ];

        if ($this->curationReady()) {
            $rules['is_recommended'] = ['required', 'array'];
            $rules['is_recommended.*'] = ['required', 'boolean'];
            $rules['recommended_order'] = ['required', 'array'];
            $rules['recommended_order.*'] = ['required', 'integer', 'min:0'];

            $rules['is_admin_pick'] = ['required', 'array'];
            $rules['is_admin_pick.*'] = ['required', 'boolean'];
            $rules['admin_pick_order'] = ['required', 'array'];
            $rules['admin_pick_order.*'] = ['required', 'integer', 'min:0'];
        }

        $data = $request->validate($rules);

        $pinnedCuration = collect($comicIds)->map(function (int $id) use ($data) {
            return [
                'id' => $id,
                'payload' => [
                    'is_featured' => (bool) $data['is_featured'][$id],
                    'sort_order' => (int) $data['sort_order'][$id],
                    ...( $this->curationReady() ? [
                        'is_recommended' => (bool) $data['is_recommended'][$id],
                        'recommended_order' => (int) $data['recommended_order'][$id],
                        'is_admin_pick' => (bool) $data['is_admin_pick'][$id],
                        'admin_pick_order' => (int) $data['admin_pick_order'][$id],
                    ] : [] ),
                ],
            ];
        });

        foreach ($pinnedCuration as $item) {
            Comic::query()->whereKey($item['id'])->update($item['payload']);
        }

        return redirect()
            ->route('admin.comics.curation')
            ->with('success', 'Kurasi explore berhasil diperbarui (batch).');
    }


    public function destroy(Comic $comic): RedirectResponse
    {
        foreach ($comic->chapters as $chapter) {
            ComicMedia::deleteManagedPages($chapter->pages ?? []);
        }

        ComicMedia::deleteManagedPath($comic->cover_url);
        ComicMedia::deleteManagedPath($comic->banner_url);
        Comic::query()
            ->whereKey($comic->getKey())
            ->toBase()
            ->delete(null);

        return redirect()
            ->route('admin.comics.index')
            ->with('success', 'Komik berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Comic $comic = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('comics', 'slug')->ignore($comic?->getKey())],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'summary' => ['required', 'string'],
            'author' => ['required', 'string', 'max:255'],
            'artist' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'string', Rule::in($this->statusOptions())],
            'comic_type' => ['required', 'string', Rule::in(ComicMetadata::formats())],
            'source_type' => ['required', 'string', Rule::in(ComicMetadata::sources())],
            'schedule' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:10'],
            'genres' => ['nullable', 'array'],
            'genres.*' => ['string', Rule::in(ComicGenres::all())],
            'features' => ['nullable', 'string'],
            'cover_url' => ['nullable', 'string', 'max:2048'],
            'banner_url' => ['nullable', 'string', 'max:2048'],
            'cover_image' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp,image/gif', 'max:5120'],
            'banner_image' => ['nullable', 'image', 'mimetypes:image/jpeg,image/png,image/webp,image/gif', 'max:8192'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $validated['title'] = TextSanitizer::plain($validated['title']);
        $validated['subtitle'] = TextSanitizer::plain($validated['subtitle'] ?? null);
        $validated['tagline'] = TextSanitizer::plain($validated['tagline'] ?? null);
        $validated['summary'] = TextSanitizer::plain($validated['summary'], true);
        $validated['author'] = TextSanitizer::plain($validated['author']);
        $validated['artist'] = TextSanitizer::plain($validated['artist'] ?? null);
        $validated['schedule'] = TextSanitizer::plain($validated['schedule'] ?? null);
        $validated['year'] = TextSanitizer::plain($validated['year'] ?? null);
        $validated['slug'] = Str::slug($validated['slug'] ?: $validated['title']);
        $validated['genres'] = collect($request->input('genres', []))
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
        $validated['features'] = TextSanitizer::lines($request->string('features')->toString());
        $validated['cover_url'] = $this->normalizeMediaReference($validated['cover_url'] ?? null, 'cover_url');
        $validated['banner_url'] = $this->normalizeMediaReference($validated['banner_url'] ?? null, 'banner_url');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        TextSanitizer::ensureNoSpam([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'],
            'tagline' => $validated['tagline'],
            'summary' => $validated['summary'],
            'author' => $validated['author'],
            'artist' => $validated['artist'],
            'schedule' => $validated['schedule'],
            'features' => $validated['features'],
        ]);

        return $validated;
    }

    private function handleArtworkUploads(Request $request, array $data, ?Comic $comic = null): array
    {
        if ($request->hasFile('cover_image')) {
            ComicMedia::deleteManagedPath($comic?->cover_url);
            $data['cover_url'] = ComicMedia::storeComicImage($request->file('cover_image'), $data['slug'], 'cover');
        }

        if ($request->hasFile('banner_image')) {
            ComicMedia::deleteManagedPath($comic?->banner_url);
            $data['banner_url'] = ComicMedia::storeComicImage($request->file('banner_image'), $data['slug'], 'banner');
        }

        return $data;
    }

    private function normalizeMediaReference(?string $value, string $field): ?string
    {
        try {
            return ComicMedia::normalizeMediaReference($value);
        } catch (\Illuminate\Validation\ValidationException $exception) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                $field => $field === 'cover_url'
                    ? 'URL/path cover tidak valid atau tidak aman.'
                    : 'URL/path banner tidak valid atau tidak aman.',
            ]);
        }
    }

    private function statusOptions(): array
    {
        return ['Ongoing', 'Completed', 'Seasonal', 'Hiatus'];
    }

    private function backendReady(): bool
    {
        return Schema::hasTable('comics') && Schema::hasTable('chapters');
    }

    private function curationReady(): bool
    {
        return Schema::hasTable('comics')
            && Schema::hasColumn('comics', 'is_recommended')
            && Schema::hasColumn('comics', 'recommended_order')
            && Schema::hasColumn('comics', 'is_admin_pick')
            && Schema::hasColumn('comics', 'admin_pick_order');
    }

    public function search(Request $request)
    {
        $search = trim((string) $request->string('q'));

        if ($search === '') {
            return response()->json([]);
        }

        if (! $this->backendReady()) {
            return response()->json([]);
        }

        $results = Comic::query()
            ->where('title', 'like', "%{$search}%")
            ->orWhere('slug', 'like', "%{$search}%")
            // ->orWhere('cover_image', 'like', "%{$search}%")
            ->orWhere('author', 'like', "%{$search}%")
            ->orWhere('artist', 'like', "%{$search}%")
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->take(10)
            ->get(['id', 'title', 'slug', 'cover_url']);

        return response()->json($results);       
    }
    
}
