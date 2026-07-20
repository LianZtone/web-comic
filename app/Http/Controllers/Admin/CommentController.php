<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChapterComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('q'));
        $visibility = trim((string) $request->string('visibility'));

        $query = ChapterComment::query()
            ->with(['chapter.comic', 'user'])
            ->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('display_name', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhereHas('chapter', function ($chapterQuery) use ($search) {
                        $chapterQuery
                            ->where('title', 'like', "%{$search}%")
                            ->orWhereHas('comic', function ($comicQuery) use ($search) {
                                $comicQuery->where('title', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($visibility === 'visible') {
            $query->where('is_visible', true);
        } elseif ($visibility === 'hidden') {
            $query->where('is_visible', false);
        }

        $comments = $query->paginate(12)->withQueryString();

        return view('admin.comments.index', [
            'comments' => $comments,
            'filters' => [
                'q' => $search,
                'visibility' => $visibility,
            ],
            'stats' => [
                'total' => ChapterComment::query()->count(),
                'visible' => ChapterComment::query()->where('is_visible', true)->count(),
                'hidden' => ChapterComment::query()->where('is_visible', false)->count(),
            ],
        ]);
    }

    public function updateVisibility(Request $request, ChapterComment $comment): RedirectResponse
    {
        $data = $request->validate([
            'is_visible' => ['required', 'boolean'],
        ]);

        $comment->update([
            'is_visible' => (bool) $data['is_visible'],
        ]);

        return redirect()
            ->back()
            ->with('success', $comment->is_visible ? 'Komentar ditampilkan kembali.' : 'Komentar disembunyikan dari publik.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'string', 'in:show,hide,delete'],
            'comment_ids' => ['required', 'array', 'min:1'],
            'comment_ids.*' => ['integer', 'distinct', 'exists:chapter_comments,id'],
        ]);

        $commentIds = collect($data['comment_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $comments = ChapterComment::query()
            ->whereIn('id', $commentIds)
            ->get();

        if ($comments->isEmpty()) {
            return redirect()
                ->back()
                ->with('error', 'Tidak ada komentar yang dipilih.');
        }

        $affected = $comments->count();

        if ($data['action'] === 'delete') {
            ChapterComment::query()->whereIn('id', $commentIds)->delete();

            return redirect()
                ->back()
                ->with('success', "{$affected} komentar berhasil dihapus.");
        }

        $visible = $data['action'] === 'show';

        ChapterComment::query()
            ->whereIn('id', $commentIds)
            ->update(['is_visible' => $visible]);

        return redirect()
            ->back()
            ->with('success', $visible
                ? "{$affected} komentar ditampilkan kembali."
                : "{$affected} komentar disembunyikan dari publik.");
    }

    public function destroy(ChapterComment $comment): RedirectResponse
    {
        $comment->delete();

        return redirect()
            ->back()
            ->with('success', 'Komentar berhasil dihapus.');
    }
}
