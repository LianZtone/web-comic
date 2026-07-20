@if ($filters['q'] !== '' || $selectedGenres->isNotEmpty() || $filters['status'] !== '' || $filters['type'] !== '' || $filters['order_by'] !== '')
    <div class="{{ $class ?? 'flex flex-wrap gap-2 text-xs' }}">
        @if ($filters['q'] !== '')
            <span class="badge badge-outline border-base-300/70">Cari: {{ $filters['q'] }}</span>
        @endif
        @foreach ($selectedGenres as $genre)
            <span class="badge badge-outline border-base-300/70">{{ $genre }}</span>
        @endforeach
        @if ($filters['status'] !== '')
            <span class="badge badge-outline border-base-300/70">{{ $filters['status'] }}</span>
        @endif
        @if ($filters['type'] !== '')
            <span class="badge badge-outline border-base-300/70">{{ $filters['type'] }}</span>
        @endif
        @if ($filters['order_by'] !== '')
            <span class="badge badge-outline border-base-300/70">{{ $orderByOptions[$filters['order_by']] ?? 'Default' }}</span>
        @endif
    </div>
@endif
