@extends('layouts.app', [
    'title' => 'Comic Not Found | Velmics',
    'description' => 'The comic you\'re looking for may have been deleted, is no longer available, or the link is invalid.',
])

@section('content')
    <div class="flex min-h-[70vh] flex-col items-center justify-center px-4 text-center">
        {{-- Illustration --}}
        <div class="mb-8 text-8xl opacity-60">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-48 w-48 text-base-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="0.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
        </div>

        {{-- Title --}}
        <h1 class="mb-4 text-5xl font-bold tracking-tight text-base-content">
            Comic Not Found
        </h1>

        {{-- Description --}}
        <p class="mx-auto mb-10 max-w-lg text-xl leading-relaxed text-base-content/70">
            The comic you're looking for may have been deleted, is no longer available, or the link is invalid.
        </p>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ route('comics.index') }}"
               class="btn btn-primary btn-lg gap-2 rounded-full px-8 font-semibold shadow-lg shadow-primary/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                Browse Comics
            </a>
            <a href="{{ route('home') }}"
               class="btn btn-outline btn-lg gap-2 rounded-full border-base-300/70 px-8 font-semibold hover:bg-base-300/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                Back to Home
            </a>
        </div>
    </div>
@endsection

