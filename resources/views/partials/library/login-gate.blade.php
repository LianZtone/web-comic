<div class="rounded-[2rem] border border-base-300/70 bg-base-100/70 p-6 shadow-lg sm:p-8">
    <div class="mx-auto max-w-2xl text-center">
        <div class="badge badge-outline border-base-300/70">Akses terbatas</div>
        <h2 class="mt-4 text-2xl font-semibold sm:text-3xl">Wajib login dulu untuk melihat {{ strtolower($label) }}.</h2>
        <p class="mt-3 text-base-content/65">
            Halaman ini disiapkan untuk koleksi personal pembaca. Setelah login pembaca aktif, data {{ strtolower($label) }} akan tampil penuh di sini.
        </p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('login') }}" class="btn btn-primary rounded-2xl px-6">Login</a>
            <a href="{{ route('register') }}" class="btn btn-ghost rounded-2xl border border-base-300/70 px-6">Register</a>
        </div>
    </div>
</div>
