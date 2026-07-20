@csrf
@if ($comic->exists)
    @method('PUT')
@endif

@php
    $coverPreview = old('cover_url', $comic->cover_url);
    $bannerPreview = old('banner_url', $comic->banner_url);
@endphp

<div class="space-y-5">
    <section class="rounded-box border border-base-300/70 bg-base-100 p-5">
        <div class="mb-5">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Informasi utama</div>
            <h2 class="mt-2 text-xl font-semibold">Detail komik</h2>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Judul</span></div>
                <input type="text" name="title" value="{{ old('title', $comic->title) }}" class="input input-bordered field-shell w-full" required>
                @error('title')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Slug</span></div>
                <input type="text" name="slug" value="{{ old('slug', $comic->slug) }}" class="input input-bordered field-shell w-full" placeholder="Kosongkan untuk auto-generate">
                @error('slug')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Subtitle</span></div>
                <input type="text" name="subtitle" value="{{ old('subtitle', $comic->subtitle) }}" class="input input-bordered field-shell w-full">
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Tagline</span></div>
                <input type="text" name="tagline" value="{{ old('tagline', $comic->tagline) }}" class="input input-bordered field-shell w-full">
            </label>

            <label class="field-control lg:col-span-2">
                <div class="field-label"><span class="field-label-text">Summary</span></div>
                <textarea name="summary" rows="5" class="textarea textarea-bordered field-textarea w-full" required>{{ old('summary', $comic->summary) }}</textarea>
                @error('summary')<span class="field-error">{{ $message }}</span>@enderror
            </label>
        </div>
    </section>

    <section class="rounded-box border border-base-300/70 bg-base-100 p-5">
        <div class="mb-5">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Metadata</div>
            <h2 class="mt-2 text-xl font-semibold">Publikasi dan urutan tampil</h2>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Author</span></div>
                <input type="text" name="author" value="{{ old('author', $comic->author) }}" class="input input-bordered field-shell w-full" required>
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Artist</span></div>
                <input type="text" name="artist" value="{{ old('artist', $comic->artist) }}" class="input input-bordered field-shell w-full">
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Status</span></div>
                <select name="status" class="select select-bordered field-select w-full">
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" @selected(old('status', $comic->status) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Tipe komik</span></div>
                <select name="comic_type" class="select select-bordered field-select w-full">
                    @foreach ($formatOptions as $format)
                        <option value="{{ $format }}" @selected(old('comic_type', $comic->comic_type) === $format)>{{ $format }}</option>
                    @endforeach
                </select>
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Sumber</span></div>
                <select name="source_type" class="select select-bordered field-select w-full">
                    <option value="" @selected(empty(old('source_type', $comic->source_type)))>Tidak diketahui</option>
                    @foreach ($sourceOptions as $source)
                        <option value="{{ $source }}" @selected(old('source_type', $comic->source_type) === $source)>{{ $source }}</option>
                    @endforeach
                </select>
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Jadwal</span></div>
                <input type="text" name="schedule" value="{{ old('schedule', $comic->schedule) }}" class="input input-bordered field-shell w-full" placeholder="Contoh: Jumat malam">
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Tahun</span></div>
                <input type="text" name="year" value="{{ old('year', $comic->year) }}" class="input input-bordered field-shell w-full">
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Urutan tampil</span></div>
                <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $comic->sort_order ?? 0) }}" class="input input-bordered field-shell w-full">
            </label>

            <div class="rounded-box border border-base-300/70 bg-base-200/35 p-4 lg:col-span-2">
                <div class="text-sm font-semibold">Metode metrik publik</div>
                <p class="mt-1 text-sm text-base-content/65">
                    Rating, views, dan bookmarks sekarang dihitung dari aktivitas user. Admin tidak perlu mengisi angka manual lagi.
                </p>
            </div>

            <fieldset class="field-control lg:col-span-2">
                <div class="field-label"><span class="field-label-text">Genre</span></div>
                @php
                    $selectedGenres = collect(old('genres', $comic->genres ?? []))->map(fn ($genre) => (string) $genre)->all();
                @endphp
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($genreOptions as $genre)
                        <label class="label cursor-pointer justify-start gap-3 rounded-xl border border-base-300/70 bg-base-100 px-4 py-3">
                            <input type="checkbox" name="genres[]" value="{{ $genre }}" class="checkbox checkbox-primary checkbox-sm" @checked(in_array($genre, $selectedGenres, true))>
                            <span class="label-text">{{ $genre }}</span>
                        </label>
                    @endforeach
                </div>
                @error('genres')<span class="field-error">{{ $message }}</span>@enderror
                @error('genres.*')<span class="field-error">{{ $message }}</span>@enderror
            </fieldset>

            <label class="field-control lg:col-span-2">
                <div class="field-label"><span class="field-label-text">Fitur / highlight</span></div>
                <textarea name="features" rows="4" class="textarea textarea-bordered field-textarea w-full" placeholder="Satu highlight per baris">{{ old('features', implode("\n", $comic->features ?? [])) }}</textarea>
            </label>

            <label class="label cursor-pointer justify-start gap-3 lg:col-span-2">
                <input type="checkbox" name="is_featured" value="1" class="checkbox checkbox-primary" @checked(old('is_featured', $comic->is_featured))>
                <span class="label-text">Tandai sebagai seri unggulan</span>
            </label>
        </div>
    </section>

    <section class="rounded-box border border-base-300/70 bg-base-100 p-5">
        <div class="mb-5">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Media</div>
            <h2 class="mt-2 text-xl font-semibold">Cover dan banner</h2>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <div class="space-y-4">
                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Upload cover</span></div>
                    <input type="file" name="cover_image" accept="image/*" class="file-input file-input-bordered field-file w-full">
                    @error('cover_image')<span class="field-error">{{ $message }}</span>@enderror
                </label>

                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Cover path / URL manual</span></div>
                    <input type="text" name="cover_url" value="{{ old('cover_url', $comic->cover_url) }}" class="input input-bordered field-shell w-full">
                    @error('cover_url')<span class="field-error">{{ $message }}</span>@enderror
                </label>

                @if ($coverPreview)
                    <div class="rounded-box border border-base-300/70 bg-base-100 p-4">
                        <div class="text-sm font-semibold">Preview cover saat ini</div>
                        <img src="{{ \App\Support\ComicMedia::resolveMediaPath($coverPreview) }}" alt="Preview cover" class="mt-3 h-44 w-32 rounded-xl border border-base-300/70 object-cover">
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Upload banner</span></div>
                    <input type="file" name="banner_image" accept="image/*" class="file-input file-input-bordered field-file w-full">
                    @error('banner_image')<span class="field-error">{{ $message }}</span>@enderror
                </label>

                <label class="field-control">
                    <div class="field-label"><span class="field-label-text">Banner path / URL manual</span></div>
                    <input type="text" name="banner_url" value="{{ old('banner_url', $comic->banner_url) }}" class="input input-bordered field-shell w-full">
                    @error('banner_url')<span class="field-error">{{ $message }}</span>@enderror
                </label>

                @if ($bannerPreview)
                    <div class="rounded-box border border-base-300/70 bg-base-100 p-4">
                        <div class="text-sm font-semibold">Preview banner saat ini</div>
                        <img src="{{ \App\Support\ComicMedia::resolveMediaPath($bannerPreview) }}" alt="Preview banner" class="mt-3 aspect-[16/7] w-full rounded-xl border border-base-300/70 object-cover">
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn btn-primary rounded-2xl">{{ $comic->exists ? 'Simpan Perubahan' : 'Simpan Komik' }}</button>
    <a href="{{ route('admin.comics.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Kembali</a>
</div>
