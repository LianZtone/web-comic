<label class="field-control">
    <div class="field-label">
        <span class="field-label-text">Cari judul atau kreator</span>
    </div>
    <input name="q" type="search" value="{{ $filters['q'] }}" placeholder="Contoh: Afterglow Protocol" class="input input-bordered field-shell w-full">
</label>

<fieldset class="field-control">
    <div class="field-label">
        <span class="field-label-text">Genre</span>
    </div>
    <div class="rounded-[1.5rem] border border-base-300/70 bg-base-100/80 p-3">
        <div class="mb-3 flex items-center justify-between text-sm">
            <span class="font-medium">All</span>
            @if ($selectedGenres->isEmpty())
                <span class="text-xs font-medium text-primary">Active</span>
            @endif
        </div>
        <div class="{{ $genreListClass ?? 'max-h-80 space-y-1 overflow-y-auto pr-1' }}">
            @foreach ($genreOptions as $genre)
                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-2xl px-3 py-2 transition hover:bg-base-200/70">
                    <span class="flex items-center gap-3 text-sm">
                        <input type="checkbox" name="genres[]" value="{{ $genre }}" class="checkbox checkbox-sm" @checked($selectedGenres->contains($genre))>
                        <span>{{ $genre }}</span>
                    </span>
                    @if ($selectedGenres->contains($genre))
                        <span class="text-xs font-medium text-primary">Active</span>
                    @endif
                </label>
            @endforeach
        </div>
    </div>
</fieldset>

<label class="field-control">
    <div class="field-label">
        <span class="field-label-text">Status</span>
    </div>
    <select name="status" class="select select-bordered field-select">
        <option value="">All</option>
        @foreach ($statusOptions as $status)
            <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ $status }}</option>
        @endforeach
    </select>
</label>

<label class="field-control{{ ($typeFieldClass ?? '') !== '' ? ' ' . $typeFieldClass : '' }}">
    <div class="field-label">
        <span class="field-label-text">Type</span>
    </div>
    <select name="type" class="select select-bordered field-select w-full">
        <option value="">All</option>
        @foreach ($typeOptions as $type)
            <option value="{{ $type }}" @selected($filters['type'] === $type)>{{ $type }}</option>
        @endforeach
    </select>
</label>

<label class="field-control">
    <div class="field-label">
        <span class="field-label-text">Order by</span>
    </div>
    <select name="order_by" class="select select-bordered field-select">
        @foreach ($orderByOptions as $value => $label)
            <option value="{{ $value }}" @selected($filters['order_by'] === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>
