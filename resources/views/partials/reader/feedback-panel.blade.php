@php
    $readerCommentSort = $readerCommentSort ?? 'newest';
    $readerReactionTotal = collect($readerReactions ?? [])->sum('count');
    $commentSortOptions = [
        'popular' => 'Populer',
        'newest' => 'Terbaru',
        'oldest' => 'Terlama',
    ];
@endphp

<div id="reader-feedback" class="scroll-mt-24 mx-auto w-full max-w-6xl px-1 pb-24 pt-2">
    <div class="rounded-[1.8rem] border border-base-300/70 bg-base-100/80 p-5 shadow-lg sm:p-6">
        <div class="flex flex-col gap-5">

            <div class="flex flex-col gap-4 border-b border-base-300/70 pb-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Reaksi pembaca</div>
                        <h2 class="mt-1 text-xl font-semibold">Bagaimana chapter ini menurutmu?</h2>
                    </div>

                </div>

                {{-- total reaction count  --}}
                <span class="text-sm text-base-content/60 mt-2 text-center" data-reader-reaction-total>
                    {{ $readerReactionTotal }} reaksi
                </span>

                <div class="flex flex-wrap gap-3 justify-center" data-reader-reactions>
                    @foreach ($readerReactions as $reaction)
                        <form method="POST"
                            action="{{ route('chapters.reactions.toggle', ['slug' => $comic['slug'], 'chapter' => $chapter['number']]) }}"
                            data-reader-reaction-form data-reaction-type="{{ $reaction['key'] }}">
                            @csrf
                            <input type="hidden" name="type" value="{{ $reaction['key'] }}">
                            <button type="submit" @class([
                                'btn btn-sm rounded-full shadow-sm' => true,
                                'btn-primary' => $reaction['active'],
                                'border border-base-300/70 bg-base-200/45 text-base-content hover:border-primary/30 hover:bg-primary/10' => !$reaction[
                                    'active'
                                ],
                            ]) data-reader-reaction-button>
                                <span>{{ $reaction['label'] }}</span>
                                <span @class([
                                    'badge badge-sm' => true,
                                    'badge-neutral' => $reaction['active'],
                                    'badge-ghost' => !$reaction['active'],
                                ])
                                    data-reader-reaction-count>{{ $reaction['count'] }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>


            </div>

            <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)]">
                <form method="POST"
                    action="{{ route('chapters.comments.store', ['slug' => $comic['slug'], 'chapter' => $chapter['number']]) }}"
                    class="rounded-[1.4rem] border border-base-300/70 bg-base-200/35 p-4 shadow-sm"
                    data-reader-comment-form data-captcha-scope="reader-comment" enctype="multipart/form-data">
                    @csrf
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold">Tulis komentar</h3>
                        </div>
                        <span class="text-sm text-base-content/55">Singkat dan sopan</span>
                    </div>

                    <div class="mt-4 space-y-3">
                        @auth

                            @include('partials.comments.markup-toolbar')

                            <textarea name="body" class="textarea textarea-bordered field-textarea h-28 w-full bg-base-100/80"
                                placeholder="Tulis pendapatmu tentang chapter ini">{{ old('body') }}</textarea>

                            @include('partials.forms.captcha', [
                                'captchaField' => 'captcha_answer',
                                'captchaLabel' => 'Verifikasi anti-spam',
                                'captchaQuestion' => \App\Support\FormCaptcha::question(
                                    request(),
                                    'reader-comment'),
                                'captchaScope' => 'reader-comment',
                            ])

                            <div class="flex items-center justify-between gap-3">
                                <div class="flex flex-wrap gap-2 text-sm text-base-content/55">
                                    <span class="badge badge-outline">Spoiler seperlunya</span>
                                    <span class="badge badge-outline">No spam</span>
                                    {{-- <span class="badge badge-outline">Komentar tampil langsung</span> --}}
                                </div>
                                <button type="submit" class="btn btn-primary rounded-full px-5">Kirim</button>
                            </div>
                        @else
                            <div
                                class="rounded-2xl border border-dashed border-base-300/70 bg-base-100/70 p-5 text-sm leading-7 text-base-content/60">
                                Login dulu supaya komentar chapter terhubung ke akun kamu dan nama otomatis diambil dari
                                profil.
                                <div class="mt-4 flex flex-wrap gap-3">
                                    <a href="{{ route('login') }}" class="btn btn-primary rounded-2xl px-5">Login</a>
                                    <a href="{{ route('register') }}"
                                        class="btn btn-ghost rounded-2xl border border-base-300/70 px-5">Register</a>
                                </div>
                            </div>
                        @endauth
                    </div>
                </form>

                <div>
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span
                            class="text-[11px] font-medium text-base-content/60 sm:badge sm:badge-outline sm:badge-sm sm:px-4 sm:text-sm sm:text-base-content"
                            data-reader-comment-count>{{ $readerCommentTotal }} komentar</span>
                        <div class="flex flex-wrap gap-2 text-sm text-base-content/55">
                            @foreach ($commentSortOptions as $sortKey => $sortLabel)
                                <a href="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $chapter['number'], 'comment_sort' => $sortKey]) }}#reader-feedback"
                                    @class([
                                        'btn btn-sm rounded-2xl' => true,
                                        'btn-primary' => $readerCommentSort === $sortKey,
                                        'btn-ghost border border-base-300/70' => $readerCommentSort !== $sortKey,
                                    ])
                                    aria-current="{{ $readerCommentSort === $sortKey ? 'page' : 'false' }}">
                                    {{ $sortLabel }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="space-y-3 h-[500px] overflow-y-auto " data-reader-comments-list>
                        @forelse ($readerComments as $comment)
                            @include('partials.reader.comment-card', [
                                'comment' => $comment,
                                'comicSlug' => $comic['slug'],
                                'chapterNumber' => $chapter['number'],
                                'commentVotesReady' => $commentVotesReady,
                                'commentRepliesReady' => $commentRepliesReady,
                            ])
                        @empty
                        @endforelse

                        <div @class([
                            'rounded-[1.4rem] border border-dashed border-base-300/70 bg-base-200/25 p-6 text-center text-sm text-base-content/60',
                            'hidden' => count($readerComments) > 0,
                        ]) data-reader-comments-empty>
                            Belum ada komentar. Kamu bisa jadi pembaca pertama yang meninggalkan pendapat.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
