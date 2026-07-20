@php
    $seriesFeedbackSort = $seriesFeedbackSort ?? 'newest';
    $seriesReactionTotal = collect($seriesFeedbackReactions ?? [])->sum('count');
    $commentSortOptions = [
        'popular' => 'Populer',
        'newest' => 'Terbaru',
        'oldest' => 'Terlama',
    ];
@endphp

<div id="series-feedback" class="scroll-mt-24 mx-auto w-full  px-1 pb-24 pt-2" data-feedback-shell
    data-reader-feedback-url="{{ route('comics.show', ['slug' => $comic['slug'], 'comment_sort' => $seriesFeedbackSort]) }}#series-feedback">
    <div class="rounded-[1.8rem] border border-base-300/70 bg-base-100/80 p-5 shadow-lg sm:p-6">
        <div class="flex flex-col gap-5">
            <div class="flex flex-col gap-4 border-b border-base-300/70 pb-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs uppercase tracking-[0.28em] text-base-content/45">Reaksi pembaca</div>
                        <h2 class="mt-1 text-xl font-semibold">Bagaimana komik ini menurutmu?</h2>

                    </div>
                </div>
                {{-- total reaction count  --}}
                <span class="text-sm text-base-content/60 mt-2 text-center" data-reader-reaction-total>
                    {{-- otomatis bertambah ketika reaction toggle  --}}
                    {{ $seriesReactionTotal }} reaksi
                </span>

                {{-- reaction buttons --}}
                <div class="flex flex-wrap gap-3 justify-center" data-reader-reactions>
                    @foreach ($seriesFeedbackReactions as $reaction)
                        <form method="POST" action="{{ route('comics.reactions.toggle', ['slug' => $comic['slug']]) }}"
                            data-reader-reaction-form data-reaction-type="{{ $reaction['key'] }}">
                            @csrf
                            <input type="hidden" name="type" value="{{ $reaction['key'] }}">
                            {{-- total --}}
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
                <form method="POST" action="{{ route('comics.comments.store', $comic['slug']) }}"
                    class="rounded-[1.4rem] border border-base-300/70 bg-base-200/35 p-4 shadow-sm"
                    data-captcha-scope="series-feedback" data-reader-comment-form id="series-feedback-form"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="score" value="{{ old('score', 5) }}">

                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-semibold">Tulis komentar</h3>
                        </div>
                        @if ($seriesFeedbackReady)
                            <span class="text-sm text-base-content/55">Singkat dan sopan</span>
                        @else
                            <span class="badge badge-warning">Backend belum aktif</span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-3">
                        @if ($seriesFeedbackReady)
                            @if ($errors->has('score') || $errors->has('body') || $errors->has('captcha_answer') || $errors->has('parent_id'))
                                <div
                                    class="rounded-2xl border border-error/20 bg-error/10 px-4 py-3 text-sm text-error">
                                    {{ $errors->first('score') ?: ($errors->first('body') ?: ($errors->first('captcha_answer') ?: $errors->first('parent_id'))) }}
                                </div>
                            @endif

                            @auth

                                @include('partials.comments.markup-toolbar')

                                <textarea name="body" class="textarea textarea-bordered field-textarea h-28 w-full bg-base-100/80"
                                    placeholder="Tulis pendapatmu tentang comic ini">{{ old('body') }}</textarea>

                                @include('partials.forms.captcha', [
                                    'captchaField' => 'captcha_answer',
                                    'captchaLabel' => 'Verifikasi anti-spam',
                                    'captchaQuestion' => \App\Support\FormCaptcha::question(
                                        request(),
                                        'series-feedback'),
                                    'captchaScope' => 'series-feedback',
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
                                    Login dulu supaya komentar comic terhubung ke akun kamu dan nama otomatis diambil dari
                                    profil.
                                    <div class="mt-4 flex flex-wrap gap-3">
                                        <a href="{{ route('login') }}" class="btn btn-primary rounded-2xl px-5">Login</a>
                                        <a href="{{ route('register') }}"
                                            class="btn btn-ghost rounded-2xl border border-base-300/70 px-5">Register</a>
                                    </div>
                                </div>
                            @endauth
                        @else
                            <div
                                class="rounded-2xl border border-dashed border-base-300/70 bg-base-100/70 p-5 text-sm leading-7 text-base-content/60">
                                Feedback seri akan aktif begitu tabel <span class="font-semibold">comic_comments</span>,
                                <span class="font-semibold">comic_comment_votes</span>, dan <span
                                    class="font-semibold">comic_reactions</span>
                                tersedia di database.
                            </div>
                        @endif
                    </div>
                </form>

                <div>
                    {{-- total comment count --}}
                    <div class="flex items-center justify-between gap-3 mb-3 ">
                        <span
                            class="text-[11px] font-medium text-base-content/60 sm:badge sm:badge-outline sm:badge-sm sm:px-4 sm:text-sm sm:text-base-content"
                            data-reader-comment-count>
                            {{ $seriesFeedbackTotal }} komentar
                        </span>
                        {{-- daftar komentar terbaruh, terlama dan populer --}}
                        {{-- button komentar populer menampilkan komentar populer --}}
                        <div class="flex flex-wrap gap-2 text-sm text-base-content/55">
                            @foreach ($commentSortOptions as $sortKey => $sortLabel)
                                <a href="{{ route('comics.show', ['slug' => $comic['slug'], 'comment_sort' => $sortKey]) }}#series-feedback"
                                    @class([
                                        'btn btn-sm rounded-2xl' => true,
                                        'btn-primary' => $seriesFeedbackSort === $sortKey,
                                        'btn-ghost border border-base-300/70' => $seriesFeedbackSort !== $sortKey,
                                    ])
                                    aria-current="{{ $seriesFeedbackSort === $sortKey ? 'page' : 'false' }}">
                                    {{ $sortLabel }}
                                </a>
                            @endforeach
                        </div>

                    </div>
                    {{-- <div class="divider my-0" data-reader-comments-divider></div> --}}
                    <div class="space-y-3 h-[500px] overflow-y-auto "
                        data-reader-comments-list>
                        @forelse ($seriesFeedback as $comment)
                            @include('partials.comics.comment-card', [
                                'comment' => $comment,
                                'comicSlug' => $comic['slug'],
                                // 'chapterNumber' => $chapter['number'],
                                'commentVotesReady' => $seriesFeedbackVotesReady,
                                'commentRepliesReady' => $seriesFeedbackRepliesReady,
                            ])
                        @empty
                        @endforelse
                        <div @class([
                            'rounded-[1.4rem] border border-dashed border-base-300/70 bg-base-200/25 p-6 text-center text-sm text-base-content/60',
                            'hidden' => count($seriesFeedback) > 0,
                        ]) data-reader-comments-empty>
                            Belum ada komentar. Kamu bisa jadi pembaca pertama yang meninggalkan pendapat.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
