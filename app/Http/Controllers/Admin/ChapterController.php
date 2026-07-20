<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Comic;
use App\Support\ComicMedia;
use App\Support\TextSanitizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChapterController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $publication = trim((string) $request->string('publication'));
        $commentsReady = Schema::hasTable('chapter_comments');

        $query = Chapter::query()
            ->with('comic')
            ->when($commentsReady, fn ($builder) => $builder->withCount('comments'))
            ->latest('updated_at');

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('number', 'like', "%{$search}%")
                    ->orWhereHas('comic', function ($comicQuery) use ($search) {
                        $comicQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
            });
        }

        if ($publication === 'published') {
            $query->where('is_published', true);
        } elseif ($publication === 'draft') {
            $query->where('is_published', false);
        }

        $chapters = $query
            ->paginate(12)
            ->withQueryString();

        return view('admin.chapters.index', [
            'chapters' => $chapters,
            'commentsReady' => $commentsReady,
            'filters' => [
                'q' => $search,
                'publication' => $publication,
            ],
            'stats' => [
                'total' => Chapter::query()->count(),
                'published' => Chapter::query()->where('is_published', true)->count(),
                'draft' => Chapter::query()->where('is_published', false)->count(),
            ],
        ]);
    }

    public function create(Comic $comic): View
    {
        return view('admin.chapters.create', [
            'comic' => $comic,
            'chapter' => new Chapter([
                'number' => (int) ($comic->chapters()->max('number') ?? 0) + 1,
                'pages' => [],
                'is_published' => true,
            ]),
        ]);
    }

    public function store(Request $request, Comic $comic): RedirectResponse
    {
        $comic->chapters()->create($this->validatedData($request, $comic));

        return redirect()
            ->route('admin.comics.edit', $comic)
            ->with('success', 'Chapter berhasil ditambahkan.');
    }

    public function edit(Comic $comic, Chapter $chapter): View
    {
        abort_unless($chapter->comic_id === $comic->id, 404);

        return view('admin.chapters.edit', [
            'comic' => $comic,
            'chapter' => $chapter,
        ]);
    }

    public function update(Request $request, Comic $comic, Chapter $chapter): RedirectResponse
    {
        abort_unless($chapter->comic_id === $comic->id, 404);

        $chapter->update($this->validatedData($request, $comic, $chapter));

        return redirect()
            ->route('admin.comics.edit', $comic)
            ->with('success', 'Chapter berhasil diperbarui.');
    }

    public function destroy(Comic $comic, Chapter $chapter): RedirectResponse
    {
        abort_unless($chapter->comic_id === $comic->id, 404);
        ComicMedia::deleteManagedPages($chapter->pages ?? []);
        $chapter->delete();

        return redirect()
            ->route('admin.comics.edit', $comic)
            ->with('success', 'Chapter berhasil dihapus.');
    }

    public function updatePublication(Request $request, Chapter $chapter): RedirectResponse
    {
        $data = $request->validate([
            'is_published' => ['required', 'boolean'],
        ]);

        $chapter->update([
            'is_published' => (bool) $data['is_published'],
        ]);

        return redirect()
            ->back()
            ->with('success', $chapter->is_published ? 'Chapter dipublikasikan.' : 'Chapter dipindahkan ke draft.');
    }

    private function validatedData(Request $request, Comic $comic, ?Chapter $chapter = null): array
    {
        $validated = $request->validate([
            'number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('chapters', 'number')
                    ->where(fn ($query) => $query->where('comic_id', $comic->id))
                    ->ignore($chapter),
            ],
            'title' => ['required', 'string', 'max:255'],
            'release_label' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string'],
            'pages' => ['nullable', 'string'],
            'page_source_folder' => ['nullable', 'string', 'max:1024'],
            'page_images' => ['nullable', 'array'],
            'page_images.*' => ['image', 'mimetypes:image/jpeg,image/png,image/webp,image/gif', 'max:8192'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['title'] = TextSanitizer::plain($validated['title']);
        $validated['release_label'] = TextSanitizer::plain($validated['release_label'] ?? null);
        $validated['summary'] = TextSanitizer::plain($validated['summary'] ?? null, true);
        $captions = TextSanitizer::lines($request->string('pages')->toString());
        $validated['page_source_folder'] = trim((string) ($validated['page_source_folder'] ?? ''));

        TextSanitizer::ensureNoSpam([
            'title' => $validated['title'],
            'release_label' => $validated['release_label'],
            'summary' => $validated['summary'],
            'pages' => $captions,
        ]);

        $validated['pages'] = $this->resolvePages($request, $comic, (int) $validated['number'], $captions, $chapter);
        $validated['is_published'] = $request->boolean('is_published');
        unset($validated['page_source_folder'], $validated['page_images']);

        return $validated;
    }

    /**
     * @param  array<int, string>  $captions
     * @return array<int, mixed>
     */
    private function resolvePages(Request $request, Comic $comic, int $chapterNumber, array $captions, ?Chapter $chapter = null): array
    {
        if ($request->hasFile('page_images')) {
            ComicMedia::deleteManagedPages($chapter?->pages ?? []);

            return ComicMedia::storeChapterImages($request->file('page_images'), $comic->slug, $chapterNumber, $captions);
        }

        $sourceFolder = trim((string) $request->input('page_source_folder', ''));

        if ($sourceFolder !== '') {
            ComicMedia::deleteManagedPages($chapter?->pages ?? []);

            return ComicMedia::importChapterDirectory($sourceFolder, $captions);
        }

        $textPages = Collection::make($captions)
            ->map(fn (?string $item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        if ($chapter && ! empty($chapter->pages)) {
            if ($textPages !== [] && collect($chapter->pages)->contains(fn ($page) => is_array($page) && ! empty($page['image']))) {
                ComicMedia::deleteManagedPages($chapter->pages);

                return $textPages;
            }

            return ComicMedia::mergeExistingPages($chapter->pages, $captions);
        }

        if ($textPages !== []) {
            return $textPages;
        }

        throw ValidationException::withMessages([
            'pages' => 'Unggah gambar chapter, isi folder sumber, atau masukkan daftar halaman.',
        ]);
    }
}
