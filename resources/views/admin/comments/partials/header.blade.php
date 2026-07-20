<section class="rounded-box border border-base-300/70 bg-base-100 p-6 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="text-xs font-semibold uppercase tracking-[0.28em] text-base-content/45">Moderasi</div>
            <h1 class="mt-2 text-3xl font-semibold sm:text-4xl">Kelola komentar pembaca</h1>
            <p class="mt-2 text-sm text-base-content/65">Filter komentar, sembunyikan dari publik, atau hapus spam yang masuk.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.comic-comments.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Komentar Seri</a>
            <a href="{{ route('admin.comics.index') }}" class="btn btn-ghost rounded-2xl border border-base-300/70">Kembali ke Dashboard</a>
        </div>
    </div>
</section>
