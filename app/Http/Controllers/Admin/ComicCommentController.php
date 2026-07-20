<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComicComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ComicCommentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $visibility = trim((string) $request->string('visibility'));
        $commentsReady = $this->commentsReady();

        if (! $commentsReady) {
            return view('admin.comic-comments.index', [
                'comments' => new LengthAwarePaginator([], 0, 12),
                'filters' => [
                    'q' => $search,
                    'visibility' => $visibility,
                ],
                'stats' => [
                    'total' => 0,
                    'visible' => 0,
                    'hidden' => 0,
                ],
                'setupRequired' => true,
            ]);
        }

        $query = ComicComment::query()
            ->with(['comic', 'user'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('display_name', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhereHas('comic', function ($comicQuery) use ($search) {
                        $comicQuery->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($visibility === 'visible') {
            $query->where('is_visible', true);
        } elseif ($visibility === 'hidden') {
            $query->where('is_visible', false);
        }

        $comments = $query->paginate(12)->withQueryString();

        return view('admin.comic-comments.index', [
            'comments' => $comments,
            'filters' => [
                'q' => $search,
                'visibility' => $visibility,
            ],
            'stats' => [
                'total' => ComicComment::query()->count('*'),
                'visible' => Schema::hasColumn('comic_comments', 'is_visible')
                    ? ComicComment::query()->where('is_visible', true)->count('*')
                    : 0,
                'hidden' => Schema::hasColumn('comic_comments', 'is_visible')
                    ? ComicComment::query()->where('is_visible', false)->count('*')
                    : 0,
            ],
            'setupRequired' => false,
        ]);
    }

    public function updateVisibility(Request $request, ComicComment $comment): RedirectResponse
    {
        $data = $request->validate([
            'is_visible' => ['required', 'boolean'],
        ]);

        $comment->update([
            'is_visible' => (bool) $data['is_visible'],
        ]);

        return redirect()
            ->back()
            ->with('success', $comment->is_visible ? 'Komentar seri ditampilkan kembali.' : 'Komentar seri disembunyikan dari publik.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:show,hide,delete'],
            'comment_ids' => ['required', 'array', 'min:1'],
            'comment_ids.*' => ['integer', 'distinct', 'exists:comic_comments,id'],
        ]);

        $commentIds = collect($data['comment_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $comments = ComicComment::query()
            ->whereIn('id', $commentIds, 'and', false)
            ->get();

        if ($comments->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'Tidak ada komentar seri yang dipilih.');
        }

        $affected = $comments->count();

        if ($data['action'] === 'delete') {
            ComicComment::query()
                ->whereIn('id', $commentIds, 'and', false)
                ->toBase()
                ->delete(null);

            return redirect()
                ->back()
                ->with('success', "{$affected} komentar seri berhasil dihapus.");
        }

        $visible = $data['action'] === 'show';

        ComicComment::query()
            ->whereIn('id', $commentIds, 'and', false)
            ->update(['is_visible' => $visible]);

        return redirect()
            ->back()
            ->with('success', $visible
                ? "{$affected} komentar seri ditampilkan kembali."
                : "{$affected} komentar seri disembunyikan dari publik.");
    }

    public function destroy(ComicComment $comment): RedirectResponse
    {
        ComicComment::query()
            ->whereKey($comment->getKey())
            ->toBase()
            ->delete(null);

        return redirect()
            ->back()
            ->with('success', 'Komentar seri berhasil dihapus.');
    }

    private function commentsReady(): bool
    {
        return Schema::hasTable('comic_comments');
    }
}
