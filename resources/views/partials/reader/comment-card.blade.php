@php
    $depth = $depth ?? 0;
    $isReply = $depth > 0;
    $deleteModalId = 'reader-delete-comment-' . $comment['id'];
    $replyCount = count($comment['replies'] ?? []);
    $visibleReplyCount = min(1, $replyCount);
    $hiddenReplyCount = max(0, $replyCount - $visibleReplyCount);
    $mentionOptions = collect([$comment['name']])
        ->merge(collect($comment['replies'] ?? [])->pluck('name'))
        ->filter()
        ->unique()
        ->values();
@endphp

<article @class([
    'rounded-[1.4rem] border border-base-300/70 bg-base-200/35 p-4 shadow-sm' => !$isReply,
    'rounded-[1.25rem] border border-base-300/50 bg-base-100/55 p-4 shadow-sm' => $isReply,
]) data-reader-comment-card="{{ $comment['id'] }}"
    data-root-comment-id="{{ $comment['root_id'] ?? $comment['id'] }}">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            @include('partials.comments.comment-header', ['comment' => $comment])
        </div>
    </div>

    @include('partials.comments.rendered-content', ['comment' => $comment])

    <div class="mt-4 flex flex-wrap items-center gap-2">
        @if (($commentVotesReady ?? false) === true)
            <form method="POST"
                action="{{ route('chapters.comments.vote', ['slug' => $comicSlug, 'chapter' => $chapterNumber, 'comment' => $comment['id']]) }}"
                data-reader-comment-vote-form>
                @csrf
                <input type="hidden" name="vote" value="like">
                <button type="submit" @class([
                    'btn btn-xs gap-2 rounded-full' => true,
                    'btn-primary' => ($comment['user_vote'] ?? null) === 'like',
                    'btn-ghost border border-base-300/70 bg-base-100/70' =>
                        ($comment['user_vote'] ?? null) !== 'like',
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M14 9V5a3 3 0 0 0-6 0v4H5.5A1.5 1.5 0 0 0 4 10.5v7A1.5 1.5 0 0 0 5.5 19H15a2 2 0 0 0 1.93-1.47l1.5-5.5A2 2 0 0 0 16.5 9H14Z" />
                    </svg>
                    <span>{{ $comment['like_count'] }}</span>
                </button>
            </form>

            <form method="POST"
                action="{{ route('chapters.comments.vote', ['slug' => $comicSlug, 'chapter' => $chapterNumber, 'comment' => $comment['id']]) }}"
                data-reader-comment-vote-form>
                @csrf
                <input type="hidden" name="vote" value="dislike">
                <button type="submit" @class([
                    'btn btn-xs gap-2 rounded-full' => true,
                    'btn-error text-white' => ($comment['user_vote'] ?? null) === 'dislike',
                    'btn-ghost border border-base-300/70 bg-base-100/70' =>
                        ($comment['user_vote'] ?? null) !== 'dislike',
                ])>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M10 15v4a3 3 0 0 0 6 0v-4h2.5a1.5 1.5 0 0 0 1.5-1.5v-7A1.5 1.5 0 0 0 18.5 5H9a2 2 0 0 0-1.93 1.47l-1.5 5.5A2 2 0 0 0 7.5 15H10Z" />
                    </svg>
                    <span>{{ $comment['dislike_count'] }}</span>
                </button>
            </form>
        @endif

        @auth
            @if (($commentRepliesReady ?? false) === true)
                <button type="button"
                    class="btn btn-ghost btn-xs gap-2 rounded-full border border-base-300/70 bg-base-100/70"
                    data-reader-reply-toggle data-reader-reply-target="{{ '@' . $comment['name'] }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 15l-6-6m0 0 6-6m-6 6h13a5 5 0 0 1 5 5v5" />
                    </svg>
                    <span>Balas</span>
                </button>
            @endif

            @if (($comment['can_manage'] ?? false) === true)
                <button type="button"
                    class="btn btn-ghost btn-xs gap-2 rounded-full border border-base-300/70 bg-base-100/70"
                    data-reader-edit-toggle>
                    Edit
                </button>

                <label for="{{ $deleteModalId }}"
                    class="btn btn-ghost btn-xs gap-2 rounded-full border border-error/30 text-error">
                    Hapus
                </label>
            @endif
        @endauth
    </div>

    @auth
        @if (($comment['can_manage'] ?? false) === true)
            <div class="mt-4 hidden rounded-[1.2rem] border border-base-300/60 bg-base-100/50 p-4"
                data-reader-edit-form-shell>
                <form method="POST"
                    action="{{ route('chapters.comments.update', ['slug' => $comicSlug, 'chapter' => $chapterNumber, 'comment' => $comment['id']]) }}"
                    class="space-y-3">
                    @csrf
                    @method('PATCH')

                    @include('partials.comments.markup-toolbar', ['includeImage' => false])

                    <textarea name="body" class="textarea textarea-bordered field-textarea h-24 w-full bg-base-100/80"
                        placeholder="Edit komentarmu">{{ $comment['text'] }}</textarea>

                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-ghost btn-xs rounded-full border border-base-300/70"
                            data-reader-edit-cancel>Batal</button>
                        <button type="submit" class="btn btn-primary btn-xs rounded-full px-4">Simpan</button>
                    </div>
                </form>
            </div>
        @endif

        @if (($commentRepliesReady ?? false) === true)
            <div class="mt-4 hidden rounded-[1.2rem] border border-base-300/60 bg-base-100/50 p-4"
                data-reader-reply-form-shell>
                <form method="POST"
                    action="{{ route('chapters.comments.store', ['slug' => $comicSlug, 'chapter' => $chapterNumber]) }}"
                    class="space-y-3" data-reader-comment-form data-captcha-scope="reader-comment"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $comment['id'] }}">

                    <div
                        class="rounded-2xl border border-base-300/70 bg-base-100/80 px-4 py-3 text-sm text-base-content/65">
                        Balasan akan dikirim sebagai <span class="font-semibold">{{ auth()->user()->name }}</span>.
                    </div>

                    @include('partials.comments.markup-toolbar')

                    <textarea name="body" class="textarea textarea-bordered field-textarea h-24 w-full bg-base-100/80"
                        placeholder="Tulis balasanmu"></textarea>

                    @include('partials.forms.captcha', [
                        'captchaField' => 'captcha_answer',
                        'captchaLabel' => 'Verifikasi anti-spam',
                        'captchaQuestion' => \App\Support\FormCaptcha::question(request(), 'reader-comment'),
                        'captchaScope' => 'reader-comment',
                    ])

                    @if ($mentionOptions->isNotEmpty())
                        <div class="space-y-2">
                            <div class="text-xs font-medium uppercase tracking-[0.18em] text-base-content/45">Tag reader
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($mentionOptions as $mentionName)
                                    <button type="button"
                                        class="btn btn-ghost btn-xs rounded-full border border-base-300/70 bg-base-100/70"
                                        data-reader-mention-chip data-reader-mention-value="{{ '@' . $mentionName }}">
                                        {{ '@' . $mentionName }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-ghost btn-xs rounded-full border border-base-300/70"
                            data-reader-reply-cancel>Batal</button>
                        <button type="submit" class="btn btn-primary btn-xs rounded-full px-4">Kirim balasan</button>
                    </div>
                </form>
            </div>
        @endif
    @endauth

    @if (($comment['can_manage'] ?? false) === true)
        <input type="checkbox" id="{{ $deleteModalId }}" class="modal-toggle" />
        <div class="modal" role="dialog">
            <div class="modal-box max-w-md rounded-[1.6rem] border border-base-300/70 bg-base-100">
                <h3 class="text-lg font-semibold">Hapus komentar?</h3>
                <p class="mt-3 text-sm leading-7 text-base-content/65">
                    Komentar ini akan dihapus permanen dari thread. Balasan yang terkait juga bisa ikut terpengaruh.
                </p>

                <div class="modal-action">
                    <label for="{{ $deleteModalId }}" class="btn btn-ghost rounded-full border border-base-300/70">
                        Batal
                    </label>
                    <form method="POST"
                        action="{{ route('chapters.comments.destroy', ['slug' => $comicSlug, 'chapter' => $chapterNumber, 'comment' => $comment['id']]) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error rounded-full text-white">Ya, hapus</button>
                    </form>
                </div>
            </div>
            <label class="modal-backdrop" for="{{ $deleteModalId }}">Tutup</label>
        </div>
    @endif

    @if (!$isReply && $replyCount > 0)
        <div class="mt-4 space-y-3" data-reader-replies-shell data-reader-replies-expanded="false">
            <div class="space-y-3 border-l border-base-300/60 pl-4" data-reader-replies-list>
                @foreach ($comment['replies'] as $reply)
                    <div @class(['hidden' => $loop->index >= $visibleReplyCount]) data-reader-reply-item>
                        @include('partials.reader.comment-card', [
                            'comment' => $reply,
                            'comicSlug' => $comicSlug,
                            'chapterNumber' => $chapterNumber,
                            'commentVotesReady' => $commentVotesReady,
                            'commentRepliesReady' => $commentRepliesReady,
                            'depth' => 1,
                        ])
                    </div>
                @endforeach
            </div>

            @if ($hiddenReplyCount > 0)
                <button type="button"
                    class="btn btn-ghost btn-xs rounded-full border border-base-300/70 bg-base-100/70"
                    data-reader-replies-toggle
                    data-reader-replies-label-collapsed="Tampilkan {{ $hiddenReplyCount }} balasan"
                    data-reader-replies-label-expanded="Sembunyikan balasan">
                    Tampilkan {{ $hiddenReplyCount }} balasan
                </button>
            @endif
        </div>
    @elseif (!$isReply && ($commentRepliesReady ?? false) === true)
        <div class="mt-4 space-y-3" data-reader-replies-shell data-reader-replies-expanded="false">
            <div class="space-y-3 border-l border-base-300/60 pl-4" data-reader-replies-list></div>
        </div>
    @endif
</article>
