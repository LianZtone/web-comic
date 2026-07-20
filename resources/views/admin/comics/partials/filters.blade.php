<section class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
    <form action="{{ route('admin.comics.index') }}" method="GET" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Cari komik</span></div>
            <input type="search" name="q" value="{{ $filters['q'] }}" class="input input-bordered field-shell w-full" placeholder="Judul, slug, author, artist">
        </label>

        <label class="field-control">
            <div class="field-label"><span class="field-label-text">Status</span></div>
            <select name="status" class="select select-bordered field-select">
                <option value="">Semua status</option>
                @foreach ($statusOptions as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </label>

        <div class="flex items-end gap-3">
            <button type="submit" class="btn btn-primary rounded-2xl">Terapkan</button>
            @if ($filters['q'] !== '' || $filters['status'] !== '')
                <a href="{{ route('admin.comics.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Reset</a>
            @endif
        </div>
    </form>

    <div class="mt-4 flex flex-wrap gap-3 text-sm text-base-content/60">
        <span>Menampilkan {{ $comics->count() }} item di halaman ini.</span>
        @if (method_exists($comics, 'total'))
            <span>Total hasil: {{ $comics->total() }}</span>
        @endif
    </div>
</section>
