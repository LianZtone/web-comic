@extends('layouts.admin', [
    'title' => 'Tambah Chapter | Velmics',
    'description' => 'Tambahkan chapter baru untuk seri di backend Velmics.',
])

@section('admin_content')
    <section class="rounded-box border border-base-300/70 bg-base-100 p-6 shadow-sm">
        <div class="mb-6">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Admin</div>
            <h1 class="mt-2 text-3xl font-semibold">Tambah chapter untuk {{ $comic->title }}</h1>
        </div>

        <form action="{{ route('admin.chapters.store', $comic) }}" method="POST" enctype="multipart/form-data">
            @include('admin.chapters._form')
        </form>
    </section>
@endsection
