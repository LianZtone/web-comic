@extends('layouts.app', [
    'title' => $comic['title'] . ' · ' . $chapter['title'] . ' | Velmics',
    'description' => $chapter['summary'],
    'fullWidthLayout' => true,
    'hideNavbar' => true,
    'hideFooter' => true,
    'hideAlerts' => true,
])

@section('content')
    @php
        $readerFeedbackType = $errors->any()
            ? 'error'
            : (session('reader_error')
                ? 'warning'
                : (session('reader_success') ? 'success' : null));
        $readerFeedbackMessage = $errors->any()
            ? $errors->first()
            : (session('reader_error') ?: session('reader_success'));
        $shouldFocusReaderFeedback = session('reader_focus') || $readerFeedbackMessage;
    @endphp

    <section class="space-y-5"
        data-reader-shell
        data-reader-comic-slug="{{ $comic['slug'] }}"
        data-reader-comic-title="{{ $comic['title'] }}"
        data-reader-comic-cover="{{ $comic['cover'] }}"
        data-reader-comic-url="{{ route('comics.show', $comic['slug']) }}"
        data-reader-chapter-number="{{ $chapter['number'] }}"
        data-reader-chapter-label="{{ $chapter['label'] }}"
        data-reader-chapter-title="{{ $chapter['title'] }}"
        data-reader-chapter-url="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $chapter['number']]) }}"
        data-reader-feedback-url="{{ route('chapters.show', ['slug' => $comic['slug'], 'chapter' => $chapter['number'], 'comment_sort' => $readerCommentSort]) }}"
        data-reader-feedback-focus="{{ $shouldFocusReaderFeedback ? 'true' : 'false' }}">
        <div id="reader-top" data-reader-scroll-target="top"></div>

        @include('partials.reader.top-bar', [
            'comic' => $comic,
            'chapter' => $chapter,
        ])

        <div class="w-full sm:mx-auto sm:max-w-4xl sm:px-3">
            <div class="space-y-0 transition duration-200" data-reader-surface>
                @foreach ($chapter['pages'] as $page)
                    @include('partials.reader.page-card', [
                        'page' => $page,
                        'chapterTitle' => $chapter['title'],
                        'isFirst' => $loop->first,
                    ])
                @endforeach
            </div>
        </div>

        @include('partials.reader.bottom-controls', [
            'comic' => $comic,
            'previousChapter' => $previousChapter,
            'nextChapter' => $nextChapter,
        ])
        {{-- next and previous chapter buttons --}}
        @include('partials.reader.button-next-chapter', [
            'comic' => $comic,
            'nextChapter' => $nextChapter,
        ])

        @include('partials.reader.chapter-modal', [
            'comic' => $comic,
            'chapter' => $chapter,
            'chapters' => $chapters,
        ])

        @include('partials.reader.feedback-panel', [
            'comic' => $comic,
            'chapter' => $chapter,
            'readerFeedbackType' => $readerFeedbackType,
            'readerFeedbackMessage' => $readerFeedbackMessage,
            'readerReactions' => $readerReactions,
            'readerComments' => $readerComments,
            'readerCommentTotal' => $readerCommentTotal,
            'readerCommentSort' => $readerCommentSort,
            'commentVotesReady' => $commentVotesReady,
            'commentRepliesReady' => $commentRepliesReady,
        ])

        <div id="reader-bottom" data-reader-scroll-target="bottom"></div>
    </section>
@endsection
