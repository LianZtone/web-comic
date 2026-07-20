@extends('layouts.app', [
    'title' => $comic['title'] . ' | Velmics',
    'description' => $comic['summary'],
    'hideNavbar' => true,
])

@section('content')
    @php
        $chapters = collect($comic['chapters'])->sortByDesc('number')->values();
        $chapterPageSize = 15;
        $comicToastType = session('reader_error') ? 'warning' : (session('reader_success') ? 'success' : null);
        $comicToastMessage = session('reader_error') ?: session('reader_success');
    @endphp

    <section class="space-y-6" data-library-comic data-comic-slug="{{ $comic['slug'] }}"
        data-comic-title="{{ $comic['title'] }}" data-comic-cover="{{ $comic['cover'] }}"
        data-comic-summary="{{ $comic['summary'] }}" data-comic-status="{{ $comic['status'] }}"
        data-comic-latest-chapter="{{ $comic['latest_chapter']['label'] }}"
        data-comic-latest-chapter-url="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $comic['latest_chapter']['number']]) }}"
        data-comic-first-chapter-url="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $comic['first_chapter']['number']]) }}"
        data-comic-show-url="{{ route('comics.show', $comic['slug']) }}">
        @include('partials.comics.show-flash-toast')
        @include('partials.comics.show-header')
        @include('partials.comics.show-hero')

        @include('partials.comics.show-chapter-panel')

        @include('partials.comics.feedback-panel', [
            'comic' => $comic,
            'seriesFeedbackReady' => $seriesFeedbackReady,
            'seriesFeedbackVotesReady' => $seriesFeedbackVotesReady,
            'seriesFeedbackRepliesReady' => $seriesFeedbackRepliesReady,
            'seriesFeedbackReactions' => $seriesFeedbackReactions,
            'seriesFeedbackTotal' => $seriesFeedbackTotal,
            'seriesFeedbackStats' => $seriesFeedbackStats,
            'seriesFeedback' => $seriesFeedback,
            'seriesFeedbackSort' => $seriesFeedbackSort,
        ])
    </section>
@endsection
