@csrf
@if ($chapter->exists)
    @method('PUT')
@endif

<div class="space-y-5">
    <section class="rounded-box border border-base-300/70 bg-base-100 p-5">
        <div class="mb-5">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Detail chapter</div>
            <h2 class="mt-2 text-xl font-semibold">Informasi utama</h2>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Nomor chapter</span></div>
                <input type="number" min="1" name="number" value="{{ old('number', $chapter->number) }}" class="input input-bordered field-shell w-full" required>
                @error('number')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Label rilis</span></div>
                <input type="text" name="release_label" value="{{ old('release_label', $chapter->release_label) }}" class="input input-bordered field-shell w-full" placeholder="Contoh: 20 Mar 2026">
            </label>

            <label class="field-control lg:col-span-2">
                <div class="field-label"><span class="field-label-text">Judul chapter</span></div>
                <input type="text" name="title" value="{{ old('title', $chapter->title) }}" class="input input-bordered field-shell w-full" required>
                @error('title')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="field-control lg:col-span-2">
                <div class="field-label"><span class="field-label-text">Ringkasan</span></div>
                <textarea name="summary" rows="4" class="textarea w-full textarea-bordered field-textarea">{{ old('summary', $chapter->summary) }}</textarea>
            </label>
        </div>
    </section>

    <section class="rounded-box border border-base-300/70 bg-base-100 p-5">
        <div class="mb-5">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Halaman</div>
            <h2 class="mt-2 text-xl font-semibold">Upload atau import dari folder</h2>
        </div>

        <div class="grid gap-4">
            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Upload gambar chapter</span></div>
                <input type="file" name="page_images[]" accept="image/*" multiple class="file-input file-input-bordered field-file w-full">
                <span class="field-help">Pilih semua halaman sekaligus. File akan diurutkan dan disimpan sebagai 1, 2, 3, dan seterusnya.</span>
                @error('page_images')<span class="field-error">{{ $message }}</span>@enderror
                @error('page_images.*')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Folder sumber chapter</span></div>
                <input type="text" name="page_source_folder" value="{{ old('page_source_folder') }}" class="input input-bordered field-shell w-full" placeholder="Contoh: assets/komik/manga/Nama Seri/capter1">
                <span class="field-help">Bisa pakai folder di `public/assets/komik/...`, misalnya `assets/komik/manga/Haou ni Natte kara Isekai ni Kite Shimatta!/capter1`.</span>
                @error('page_source_folder')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            <label class="field-control">
                <div class="field-label"><span class="field-label-text">Caption halaman / fallback text</span></div>
                <textarea name="pages" rows="10" class="textarea w-full textarea-bordered field-textarea" placeholder="Satu caption per baris. Jika chapter gambar dipakai, caption ini opsional.">{{ old('pages', collect($chapter->pages ?? [])->map(fn ($page) => \App\Support\ComicMedia::pageCaptionFromValue($page))->implode("\n")) }}</textarea>
                <span class="field-help">Jika tidak upload gambar, isi ini tetap bisa dipakai sebagai halaman teks seperti versi MVP.</span>
                @error('pages')<span class="field-error">{{ $message }}</span>@enderror
            </label>

            @if (! empty($chapter->pages))
                <div class="rounded-box border border-base-300/70 bg-base-100 p-4">
                    <div class="text-sm font-semibold">Preview halaman saat ini</div>
                    <div class="mt-3 flex flex-wrap gap-3">
                        @foreach (collect($chapter->pages)->take(6) as $page)
                            @if (is_array($page) && ! empty($page['image']))
                                <img src="{{ \App\Support\ComicMedia::resolveMediaPath($page['image']) }}" alt="Page preview" class="h-24 w-16 rounded-xl border border-base-300/70 object-cover">
                            @endif
                        @endforeach
                    </div>
                    <div class="mt-3 text-sm text-base-content/55">Total halaman tersimpan: {{ collect($chapter->pages)->count() }}</div>
                </div>
            @endif

            <label class="label cursor-pointer justify-start gap-3">
                <input type="checkbox" name="is_published" value="1" class="checkbox checkbox-primary" @checked(old('is_published', $chapter->is_published ?? true))>
                <span class="label-text">Tampilkan chapter ini di halaman publik</span>
            </label>
        </div>
    </section>
</div>

<div class="mt-6 flex flex-wrap gap-3">
    <button type="submit" class="btn btn-primary rounded-2xl">{{ $chapter->exists ? 'Simpan Chapter' : 'Tambah Chapter' }}</button>
    <a href="{{ route('admin.comics.edit', $comic) }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Kembali</a>
</div>
