@php
    $recommendationType = strtolower($comic['comic_type'] ?? 'comic');
@endphp

@if ($featured ?? false)
    <article class="group md:col-span-2 xl:col-span-2" data-recommendation-item
        data-recommendation-type="{{ $recommendationType }}">
        <a href="{{ route('comics.show', $comic['slug']) }}" class="block overflow-hidden rounded-[1.75rem] border border-base-300/70 bg-base-100/60 shadow-lg transition hover:-translate-y-1">
            <div class="relative md:hidden">
                <img src="{{ $comic['cover'] }}" alt="{{ $comic['title'] }} cover" class="aspect-[4/5] w-full object-cover" loading="lazy" decoding="async">
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent p-3">
                    <div class="flex flex-wrap gap-2 text-[11px]">
                        <span class="badge badge-primary badge-sm">{{ $comic['status'] }}</span>
                        <span class="badge badge-outline badge-sm border-white/20 text-white">{{ $comic['latest_chapter']['label'] }}</span>
                    </div>
                    <h3 class="mt-2 line-clamp-2 text-base font-semibold text-white">{{ $comic['title'] }}</h3>
                </div>
            </div>

            <div class="hidden h-full md:grid md:grid-cols-[220px_minmax(0,1fr)]">
                <img src="{{ $comic['cover'] }}" alt="{{ $comic['title'] }} cover" class="h-full w-full object-cover" loading="lazy" decoding="async">
                <div class="flex flex-col justify-between gap-4 p-5">
                    <div>
                        <div class="mb-2 flex flex-wrap gap-2">
                            <span class="badge badge-primary">{{ $comic['status'] }}</span>
                            <span class="badge badge-outline">{{ $comic['latest_chapter']['label'] }}</span>
                        </div>
                        <h3 class="text-xl font-semibold sm:text-2xl">{{ $comic['title'] }}</h3>
                        <p class="mt-3 line-clamp-4 text-sm text-base-content/65">{{ $comic['tagline'] }}</p>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm text-base-content/55">
                        <span>{{ $comic['views_label'] }} views</span>
                        <span class="font-medium text-primary">Baca sekarang</span>
                    </div>
                </div>
            </div>
        </a>
    </article>
@else
    <article class="group md:col-span-2 xl:col-span-2" data-recommendation-item
        data-recommendation-type="{{ $recommendationType }}">
        <a href="{{ route('comics.show', $comic['slug']) }}" class="block overflow-hidden rounded-[1.75rem] border border-base-300/70 bg-base-100/60 shadow-lg transition hover:-translate-y-1">
            <div class="relative md:hidden">
                <img src="{{ $comic['cover'] }}" alt="{{ $comic['title'] }} cover" class="aspect-[4/5] w-full object-cover" loading="lazy" decoding="async">
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent p-3">
                    <div class="flex flex-wrap gap-2 text-[11px]">
                        <span class="badge badge-primary badge-sm">{{ $comic['status'] }}</span>
                        <span class="badge badge-outline badge-sm border-white/20 text-white">{{ $comic['latest_chapter']['label'] }}</span>
                    </div>
                    <h3 class="mt-2 line-clamp-2 text-base font-semibold text-white">{{ $comic['title'] }}</h3>
                </div>
            </div>

            <div class="hidden h-full md:grid md:grid-cols-[220px_minmax(0,1fr)]">
                <img src="{{ $comic['cover'] }}" alt="{{ $comic['title'] }} cover" class="h-full w-full object-cover" loading="lazy" decoding="async">
                <div class="flex flex-col justify-between gap-4 p-5">
                    <div>
                        <div class="mb-2 flex flex-wrap gap-2">
                            <span class="badge badge-primary">{{ $comic['status'] }}</span>
                            <span class="badge badge-outline">{{ $comic['latest_chapter']['label'] }}</span>
                        </div>
                        <h3 class="text-xl font-semibold sm:text-2xl">{{ $comic['title'] }}</h3>
                        <p class="mt-3 line-clamp-4 text-sm text-base-content/65">{{ $comic['tagline'] }}</p>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm text-base-content/55">
                        <span>{{ $comic['views_label'] }} views</span>
                        <span class="font-medium text-primary ">Baca sekarang</span>
                    </div>
                </div>
            </div>
        </a>
    </article>
@endif
