@extends('layouts.admin', [
    'title' => 'Edit Chapter | Velmics',
    'description' => 'Perbarui chapter di backend Velmics.',
])

@section('admin_content')
    <section class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="rounded-box border border-base-300/70 bg-base-100 p-6 shadow-sm">
            <div class="mb-6">
                <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Admin</div>
                <h1 class="mt-2 text-3xl font-semibold">Edit {{ $chapter->title }}</h1>
                <p class="mt-2 text-base-content/65">{{ $comic->title }} · Chapter {{ str_pad((string) $chapter->number, 2, '0', STR_PAD_LEFT) }}</p>
            </div>

            <form action="{{ route('admin.chapters.update', [$comic, $chapter]) }}" method="POST" enctype="multipart/form-data">
                @include('admin.chapters._form')
            </form>
        </div>

        <aside class="space-y-4">
            <div class="rounded-box border border-base-300/70 bg-base-100 p-5 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Ringkasan</div>
                <h2 class="mt-2 text-xl font-semibold">Chapter {{ str_pad((string) $chapter->number, 2, '0', STR_PAD_LEFT) }}</h2>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="badge {{ ($chapter->is_published ?? true) ? 'badge-success' : 'badge-warning' }}">
                        {{ ($chapter->is_published ?? true) ? 'Published' : 'Draft' }}
                    </span>
                    <span class="badge badge-outline">{{ collect($chapter->pages ?? [])->count() }} halaman</span>
                </div>
            </div>
        </aside>
    </section>
@endsection
