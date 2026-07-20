@extends('layouts.admin', [
    'title' => 'Tambah Komik | Velmics',
    'description' => 'Tambahkan seri baru ke backend Velmics.',
])

@section('admin_content')
    <section class="rounded-box border border-base-300/70 bg-base-100 p-6 shadow-sm">
        <div class="mb-6">
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Admin</div>
            <h1 class="mt-2 text-3xl font-semibold">Tambah komik baru</h1>
        </div>

        <form action="{{ route('admin.comics.store') }}" method="POST" enctype="multipart/form-data">
            @include('admin.comics._form')
        </form>
    </section>
@endsection
