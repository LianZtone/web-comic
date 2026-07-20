<section class="rounded-box border border-base-300/70 bg-base-100 p-6 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Dashboard</div>
            <h1 class="mt-2 text-3xl font-semibold sm:text-4xl">Kelola katalog komik</h1>
            <p class="mt-2 text-sm text-base-content/65">Tambah seri, atur chapter, dan pantau komentar pembaca dari satu panel kerja.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.comics.curation') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Explore Curation</a>
            <a href="{{ route('admin.chapters.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Kelola Chapter</a>
            <a href="{{ route('admin.comments.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Komentar</a>
            <a href="{{ route('admin.comics.create') }}" class="btn btn-primary rounded-2xl">Tambah Komik</a>
        </div>
    </div>
</section>
