import { historyStorageKey } from './config';
import { hasReadChapter, markChapterAsRead, upsertStoredItem } from './storage';

export function applyUpdateReadState() {
    document.querySelectorAll('[data-update-chapter-link]').forEach((link) => {
        const comicSlug = link.dataset.comicSlug;
        const chapterNumber = link.dataset.chapterNumber;
        const isRead = hasReadChapter(comicSlug, chapterNumber);
        const badge = link.querySelector('[data-update-new-badge]');

        link.classList.toggle('bg-base-100', !isRead);
        link.classList.toggle('hover:bg-base-100/80', !isRead);
        link.classList.toggle('bg-success/10', isRead);
        link.classList.toggle('text-base-content/70', isRead);
        link.classList.toggle('ring-1', isRead);
        link.classList.toggle('ring-success/20', isRead);

        if (badge) {
            badge.classList.toggle('hidden', isRead);
        }
    });
}

export function applyComicChapterReadState() {
    document.querySelectorAll('[data-chapter-link]').forEach((link) => {
        const comicSlug = link.dataset.comicSlug;
        const chapterNumber = link.dataset.chapterNumber;
        const isRead = hasReadChapter(comicSlug, chapterNumber);
        const readBadge = link.querySelector('[data-chapter-read-badge]');
        const arrow = link.querySelector('[data-chapter-arrow]');

        link.classList.toggle('bg-base-100/70', !isRead);
        link.classList.toggle('border-base-300/70', !isRead);
        link.classList.toggle('bg-success/10', isRead);
        link.classList.toggle('border-success/25', isRead);
        link.classList.toggle('text-base-content/80', isRead);

        if (readBadge) {
            readBadge.classList.toggle('hidden', !isRead);
        }

        if (arrow) {
            arrow.classList.toggle('border-base-300/70', !isRead);
            arrow.classList.toggle('bg-base-100/80', !isRead);
            arrow.classList.toggle('text-base-content/45', !isRead);
            arrow.classList.toggle('border-success/25', isRead);
            arrow.classList.toggle('bg-success/10', isRead);
            arrow.classList.toggle('text-success', isRead);
        }
    });
}

export function initReaderHistory() {
    document.querySelectorAll('[data-reader-shell]').forEach((shell) => {
        const comicSlug = shell.dataset.readerComicSlug;
        const chapterNumber = shell.dataset.readerChapterNumber;

        if (!comicSlug || !chapterNumber) {
            return;
        }

        markChapterAsRead(comicSlug, chapterNumber);
        upsertStoredItem(historyStorageKey, {
            slug: comicSlug,
            title: shell.dataset.readerComicTitle,
            cover: shell.dataset.readerComicCover,
            chapterNumber,
            chapterLabel: shell.dataset.readerChapterLabel,
            chapterTitle: shell.dataset.readerChapterTitle,
            comicUrl: shell.dataset.readerComicUrl,
            chapterUrl: shell.dataset.readerChapterUrl,
            summary: `Terakhir dibuka ${shell.dataset.readerChapterLabel}`,
            savedAt: new Date().toISOString(),
        });
    });
}
