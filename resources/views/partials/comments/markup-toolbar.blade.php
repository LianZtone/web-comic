@php
    $includeImage = $includeImage ?? true;
    $emojiOptions = \App\Support\CommentDecorations::emojis();
@endphp

<div class="space-y-2" data-comment-markup-shell>
    <div class="flex flex-wrap items-center gap-2 rounded-2xl border border-base-300/60 bg-base-100/70 px-3 py-2">
        <button type="button"
            class="btn btn-ghost btn-xs rounded-full border border-base-300/70 bg-base-100/80"
            data-comment-markup-tag="b" title="Tebal">
            <span class="font-bold">B</span>
        </button>
        <button type="button"
            class="btn btn-ghost btn-xs rounded-full border border-base-300/70 bg-base-100/80 italic"
            data-comment-markup-tag="i" title="Miring">
            <span>I</span>
        </button>
        <button type="button"
            class="btn btn-ghost btn-xs gap-2 rounded-full border border-warning/30 bg-warning/10 text-warning"
            data-comment-markup-tag="spoiler" title="Spoiler">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.615 3.892 1.674 18.25A1.5 1.5 0 0 0 2.958 20.5h18.084a1.5 1.5 0 0 0 1.284-2.25L13.385 3.892a1.5 1.5 0 0 0-2.77 0Z" />
            </svg>
            <span>Spoiler</span>
        </button>
        <button type="button"
            class="btn btn-ghost btn-xs gap-2 rounded-full border border-base-300/70 bg-base-100/80"
            data-comment-picker-toggle="emoji" title="Emoji">
            <span class="text-base leading-none">😊</span>
            <span>Emoji</span>
        </button>
        @if ($includeImage)
            <button type="button"
                class="btn btn-ghost btn-xs gap-2 rounded-full border border-base-300/70 bg-base-100/80"
                data-comment-image-trigger title="Upload gambar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l.75.75 2.659-2.659a2.25 2.25 0 0 1 3.182 0l4.568 4.568M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Zm4.5-9.75h.008v.008H8.25V9.75Z" />
                </svg>
                <span>Image</span>
            </button>
            <input type="file" name="comment_image" accept="image/png,image/jpeg,image/webp,image/gif" class="hidden"
                data-comment-image-input>
        @endif
    </div>

    <div class="hidden rounded-2xl border border-base-300/60 bg-base-100/70 p-3"
        data-comment-picker-panel="emoji">
        <div class="mb-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-base-content/45">Emoji</div>
        <div class="flex flex-wrap gap-2">
            @foreach ($emojiOptions as $key => $emoji)
                <button type="button"
                    class="btn btn-ghost btn-sm gap-2 rounded-2xl border border-base-300/70 bg-base-100/80"
                    data-comment-insert-token="[emoji]{{ $key }}[/emoji]">
                    <span class="text-lg leading-none">{{ $emoji['char'] }}</span>
                    <span>{{ $emoji['label'] }}</span>
                </button>
            @endforeach
        </div>
    </div>

    @if ($includeImage)
        <div class="hidden items-center justify-between gap-3 rounded-2xl border border-base-300/60 bg-base-100/70 px-3 py-2"
            data-comment-image-preview-shell>
            <div class="flex min-w-0 items-center gap-3">
                <img src="" alt="Preview gambar komentar" class="hidden h-14 w-14 rounded-xl object-cover shadow-sm"
                    data-comment-image-preview-image>
                <div class="min-w-0">
                    <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-base-content/45">Image</div>
                    <div class="truncate text-sm text-base-content/70" data-comment-image-preview-name></div>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-xs rounded-full border border-base-300/70"
                data-comment-image-clear>
                Hapus
            </button>
        </div>

        <div class="pointer-events-none fixed inset-0 z-[90] hidden flex items-center justify-center bg-base-content/55 p-4 opacity-0 transition-opacity duration-200"
            data-comment-image-modal>
            <div class="w-full max-w-sm rounded-[1.75rem] border border-base-300/60 bg-base-100 p-5 shadow-2xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-[0.26em] text-base-content/40">Image</div>
                        <h3 class="mt-2 text-lg font-semibold text-base-content">Upload gambar komentar</h3>
                    </div>
                    <button type="button" class="btn btn-ghost btn-sm btn-circle border border-base-300/70"
                        data-comment-image-modal-close aria-label="Tutup modal image">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <div
                        class="rounded-[1.4rem] border border-dashed border-base-300/70 bg-base-200/25 px-4 py-5 text-center">
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-base-300/70 bg-base-100/80 text-base-content/65">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l.75.75 2.659-2.659a2.25 2.25 0 0 1 3.182 0l4.568 4.568M3.75 19.5h16.5A1.5 1.5 0 0 0 21.75 18V6A1.5 1.5 0 0 0 20.25 4.5H3.75A1.5 1.5 0 0 0 2.25 6v12A1.5 1.5 0 0 0 3.75 19.5Zm4.5-9.75h.008v.008H8.25V9.75Z" />
                            </svg>
                        </div>
                        <div class="mt-4 text-sm font-medium text-base-content">Pilih gambar untuk komentar</div>
                        <p class="mt-1 text-xs leading-6 text-base-content/55">PNG, JPG, WEBP, atau GIF sampai 5MB.</p>
                        <button type="button" class="btn btn-primary btn-sm mt-4 rounded-full px-5"
                            data-comment-image-picker-open>
                            Pilih gambar
                        </button>
                    </div>

                    <div class="hidden items-center gap-3 rounded-[1.3rem] border border-base-300/60 bg-base-200/25 p-3"
                        data-comment-image-preview-shell>
                        <img src="" alt="Preview gambar komentar"
                            class="hidden h-20 w-20 rounded-2xl object-cover shadow-sm"
                            data-comment-image-preview-image>
                        <div class="min-w-0 flex-1">
                            <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-base-content/45">Preview
                            </div>
                            <div class="mt-1 truncate text-sm text-base-content/75" data-comment-image-preview-name>
                            </div>
                        </div>
                        <button type="button" class="btn btn-ghost btn-xs rounded-full border border-base-300/70"
                            data-comment-image-clear>
                            Hapus
                        </button>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" class="btn btn-ghost btn-sm rounded-full border border-base-300/70 px-5"
                            data-comment-image-modal-close>
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
