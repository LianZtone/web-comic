<section class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
    <form action="{{ route('admin.comic-comments.index') }}" method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Cari komentar seri</span></div>
            <input type="search" name="q" value="{{ $filters['q'] }}" class="input input-bordered field-shell w-full" placeholder="Nama, isi komentar, atau judul komik">
        </label>

        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Status tampil</span></div>
            <select name="visibility" class="select select-bordered field-select">
                <option value="">Semua komentar</option>
                <option value="visible" @selected($filters['visibility'] === 'visible')>Visible</option>
                <option value="hidden" @selected($filters['visibility'] === 'hidden')>Hidden</option>
            </select>
        </label>

        <div class="flex items-end gap-3">
            <button type="submit" class="btn btn-primary rounded-2xl">Terapkan</button>
            @if ($filters['q'] !== '' || $filters['visibility'] !== '')
                <a href="{{ route('admin.comic-comments.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Reset</a>
            @endif
        </div>
    </form>

    <div class="mt-4 text-sm text-base-content/60">
        Menampilkan {{ $comments->count() }} komentar seri di halaman ini. Total hasil: {{ $comments->total() }}.
    </div>
</section>
