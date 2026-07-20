import { bookmarkStorageKey, historyStorageKey, readlistStorageKey } from './config';
import { escapeHtml } from './helpers';
import { getStoredItems, hasStoredItem, removeStoredItem, upsertStoredItem } from './storage';

function getComicPayload(shell) {
    if (!shell) {
        return null;
    }

    return {
        slug: shell.dataset.comicSlug,
        title: shell.dataset.comicTitle,
        cover: shell.dataset.comicCover,
        summary: shell.dataset.comicSummary,
        status: shell.dataset.comicStatus,
        latestChapter: shell.dataset.comicLatestChapter,
        comicUrl: shell.dataset.comicShowUrl,
        firstChapterUrl: shell.dataset.comicFirstChapterUrl,
        savedAt: new Date().toISOString(),
    };
}

function getCollectionMeta(type) {
    if (type === 'bookmarks') {
        return {
            key: bookmarkStorageKey,
            label: 'Bookmark',
            activeLabel: 'Tersimpan',
        };
    }

    if (type === 'readlist') {
        return {
            key: readlistStorageKey,
            label: 'Readlist',
            activeLabel: 'Masuk Readlist',
        };
    }

    return null;
}

function applyContinueState(shell) {
    const button = shell?.querySelector('[data-library-continue]');
    const comicSlug = shell?.dataset.comicSlug;

    if (!button || !comicSlug) {
        return;
    }

    const historyItem = getStoredItems(historyStorageKey)
        .find((entry) => entry?.slug === comicSlug && entry?.chapterUrl);
    const fallbackUrl = shell.dataset.comicLatestChapterUrl
        || shell.dataset.comicFirstChapterUrl
        || shell.dataset.comicShowUrl
        || '#';
    const nextUrl = historyItem?.chapterUrl || fallbackUrl;
    const labelNode = button.querySelector('[data-library-continue-label]');

    button.setAttribute('href', nextUrl);
    button.setAttribute('title', historyItem?.chapterLabel
        ? `Lanjut ke ${historyItem.chapterLabel}`
        : 'Lanjut baca');

    if (labelNode) {
        labelNode.textContent = historyItem?.chapterLabel
            ? `Lanjut ${historyItem.chapterLabel}`
            : 'Lanjut Baca';
    }
}

function applyLibraryToggleState(shell) {
    const comicSlug = shell?.dataset.comicSlug;

    if (!comicSlug) {
        return;
    }

    shell.querySelectorAll('[data-library-toggle]').forEach((button) => {
        const meta = getCollectionMeta(button.dataset.libraryToggle);

        if (!meta) {
            return;
        }

        const active = hasStoredItem(meta.key, comicSlug);
        const labelNode = button.querySelector('[data-library-toggle-label]');

        button.classList.toggle('btn-primary', active);
        button.classList.toggle('btn-ghost', !active);
        button.classList.toggle('border-primary/30', active);
        button.classList.toggle('bg-primary/15', active);
        button.classList.toggle('text-primary', active);

        if (labelNode) {
            labelNode.textContent = active ? meta.activeLabel : meta.label;
        }
    });
}

function createLibraryCardMarkup(type, item) {
    const title = escapeHtml(item.title || 'Tanpa judul');
    const cover = escapeHtml(item.cover || '');
    const summary = escapeHtml(item.summary || '');
    const comicUrl = escapeHtml(item.comicUrl || '#');
    const firstChapterUrl = escapeHtml(item.firstChapterUrl || comicUrl);
    const latestChapter = escapeHtml(item.latestChapter || '');
    const chapterLabel = escapeHtml(item.chapterLabel || '');
    const chapterTitle = escapeHtml(item.chapterTitle || '');
    const chapterUrl = escapeHtml(item.chapterUrl || comicUrl);

    if (type === 'history') {
        return `
            <article class="card bg-base-100 shadow-lg">
                <div class="card-body gap-3">
                    <div class="flex items-center gap-3">
                        <img src="${cover}" alt="${title} cover" class="w-14 rounded-lg border border-base-300/70 object-cover" loading="lazy" decoding="async">
                        <div class="min-w-0">
                            <h3 class="truncate text-base font-semibold">${title}</h3>
                            <p class="truncate text-xs text-base-content/60">${chapterLabel} · ${chapterTitle}</p>
                        </div>
                    </div>
                    <div class="text-xs text-base-content/60">${summary}</div>
                    <div class="card-actions justify-between">
                        <a href="${comicUrl}" class="btn btn-ghost btn-xs rounded-xl border border-base-300/70">Detail</a>
                        <a href="${chapterUrl}" class="btn btn-primary btn-xs rounded-xl">Lanjut</a>
                    </div>
                </div>
            </article>
        `;
    }

    return `
        <article class="overflow-hidden rounded-[1.6rem] border border-base-300/70 bg-base-100 shadow-sm">
            <div class="grid min-h-full grid-cols-[96px_minmax(0,1fr)]">
                <a href="${comicUrl}" class="border-r border-base-300/70">
                    <img src="${cover}" alt="${title} cover" class="h-full w-full object-cover" loading="lazy" decoding="async">
                </a>
                <div class="flex flex-col gap-3 p-4">
                    <div class="min-w-0">
                        <a href="${comicUrl}" class="line-clamp-2 text-base font-semibold">${title}</a>
                        <p class="mt-1 line-clamp-2 text-xs text-base-content/60">${summary}</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[11px] text-base-content/55">
                        <span>${latestChapter}</span>
                    </div>
                    <div class="mt-auto flex flex-wrap gap-2">
                        <a href="${comicUrl}" class="btn btn-ghost btn-xs rounded-xl border border-base-300/70">Detail</a>
                        <a href="${firstChapterUrl}" class="btn btn-primary btn-xs rounded-xl">Baca</a>
                        <button type="button" class="btn btn-ghost btn-xs rounded-xl border border-base-300/70" data-library-remove="${type}" data-library-slug="${escapeHtml(item.slug || '')}">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        </article>
    `;
}

function formatSavedAt(savedAt) {
    if (!savedAt) {
        return 'Baru saja dibuka';
    }

    const date = new Date(savedAt);

    if (Number.isNaN(date.getTime())) {
        return 'Baru saja dibuka';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

function createContinueReadingMarkup(item) {
    const title = escapeHtml(item.title || 'Tanpa judul');
    const cover = escapeHtml(item.cover || '');
    const comicUrl = escapeHtml(item.comicUrl || '#');
    const chapterUrl = escapeHtml(item.chapterUrl || comicUrl);
    const chapterLabel = escapeHtml(item.chapterLabel || 'Chapter terakhir');
    const chapterTitle = escapeHtml(item.chapterTitle || '');
    const savedAt = escapeHtml(formatSavedAt(item.savedAt));

    return `
        <article class="card border border-base-300/70 bg-base-100 shadow-lg">
            <div class="card-body gap-3">
                <div class="flex items-center gap-3">
                    <img src="${cover}" alt="${title} cover" class="w-14 rounded-lg border border-base-300/70 object-cover" loading="lazy" decoding="async">
                    <div class="min-w-0">
                        <h3 class="truncate text-base font-semibold">${title}</h3>
                        <p class="truncate text-xs text-base-content/60">${chapterLabel} · ${chapterTitle}</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-base-300/70 bg-base-200/45 px-4 py-3 text-sm text-base-content/65">
                    Terakhir dibuka ${savedAt}
                </div>
                <div class="card-actions justify-between">
                    <a href="${comicUrl}" class="btn btn-ghost btn-xs rounded-xl border border-base-300/70">Detail</a>
                    <a href="${chapterUrl}" class="btn btn-primary btn-xs rounded-xl">Lanjut</a>
                </div>
            </div>
        </article>
    `;
}

function renderLibraryPreview(type, items) {
    document.querySelectorAll(`[data-library-count="${type}"]`).forEach((node) => {
        node.textContent = `${items.length} tersimpan`;
    });

    document.querySelectorAll(`[data-library-preview="${type}"]`).forEach((container) => {
        const previewItems = items.slice(0, type === 'history' ? 4 : 3);
        container.innerHTML = previewItems.map((item) => createLibraryCardMarkup(type, item)).join('');
        container.classList.toggle('hidden', previewItems.length === 0);
    });

    document.querySelectorAll(`[data-library-empty="${type}"]`).forEach((node) => {
        node.classList.toggle('hidden', items.length > 0);
    });
}

function renderLibraryDashboard() {
    renderLibraryPreview('history', getStoredItems(historyStorageKey));
    renderLibraryPreview('bookmarks', getStoredItems(bookmarkStorageKey));
    renderLibraryPreview('readlist', getStoredItems(readlistStorageKey));
}

function renderLibraryPage(type, items) {
    document.querySelectorAll(`[data-library-page-count="${type}"]`).forEach((node) => {
        node.textContent = `${items.length} tersimpan`;
    });

    document.querySelectorAll(`[data-library-page-list="${type}"]`).forEach((container) => {
        container.innerHTML = items.map((item) => createLibraryCardMarkup(type, item)).join('');
        container.classList.toggle('hidden', items.length === 0);
    });

    document.querySelectorAll(`[data-library-page-empty="${type}"]`).forEach((node) => {
        node.classList.toggle('hidden', items.length > 0);
    });
}

function renderAllLibraryPages() {
    renderLibraryPage('history', getStoredItems(historyStorageKey));
    renderLibraryPage('bookmarks', getStoredItems(bookmarkStorageKey));
    renderLibraryPage('readlist', getStoredItems(readlistStorageKey));
}

function renderContinueReadingSection() {
    document.querySelectorAll('[data-library-continue-list]').forEach((container) => {
        const items = getStoredItems(historyStorageKey).slice(0, 3);
        container.innerHTML = items.map((item) => createContinueReadingMarkup(item)).join('');
        container.classList.toggle('hidden', items.length === 0);
    });

    document.querySelectorAll('[data-library-continue-empty]').forEach((node) => {
        node.classList.toggle('hidden', getStoredItems(historyStorageKey).length > 0);
    });
}

function applyLibraryTabs(shell) {
    const activeTab = shell?.dataset.libraryActiveTab || 'history';

    if (!shell) {
        return;
    }

    shell.querySelectorAll('[data-library-tab]').forEach((button) => {
        const active = button.dataset.libraryTab === activeTab;

        button.classList.toggle('btn-primary', active);
        button.classList.toggle('btn-ghost', !active);
        button.classList.toggle('border-base-300/70', !active);
    });

    shell.querySelectorAll('[data-library-panel]').forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.libraryPanel !== activeTab);
    });
}

export function initLibraryCollections() {
    document.querySelectorAll('[data-library-comic]').forEach((shell) => {
        applyLibraryToggleState(shell);
        applyContinueState(shell);
    });

    document.querySelectorAll('[data-library-shell]').forEach((shell) => {
        if (!shell.dataset.libraryActiveTab) {
            shell.dataset.libraryActiveTab = 'history';
        }

        applyLibraryTabs(shell);
    });

    renderLibraryDashboard();
    renderAllLibraryPages();
    renderContinueReadingSection();

    document.addEventListener('click', (event) => {
        const libraryTab = event.target.closest('[data-library-tab]');

        if (libraryTab) {
            const shell = libraryTab.closest('[data-library-shell]');

            if (!shell) {
                return;
            }

            shell.dataset.libraryActiveTab = libraryTab.dataset.libraryTab;
            applyLibraryTabs(shell);
            return;
        }

        const libraryToggle = event.target.closest('[data-library-toggle]');

        if (libraryToggle) {
            const shell = libraryToggle.closest('[data-library-comic]');
            const meta = getCollectionMeta(libraryToggle.dataset.libraryToggle);
            const payload = getComicPayload(shell);

            if (!meta || !payload?.slug) {
                return;
            }

            if (hasStoredItem(meta.key, payload.slug)) {
                removeStoredItem(meta.key, payload.slug);
            } else {
                upsertStoredItem(meta.key, payload);
            }

            applyLibraryToggleState(shell);
            applyContinueState(shell);
            renderLibraryDashboard();
            renderAllLibraryPages();
            renderContinueReadingSection();
            return;
        }

        const removeButton = event.target.closest('[data-library-remove]');

        if (!removeButton) {
            return;
        }

        const type = removeButton.dataset.libraryRemove;
        const slug = removeButton.dataset.librarySlug;
        const meta = getCollectionMeta(type);

        if (!meta || !slug) {
            return;
        }

        removeStoredItem(meta.key, slug);
        document.querySelectorAll(`[data-library-comic][data-comic-slug="${slug}"]`).forEach((shell) => {
            applyLibraryToggleState(shell);
            applyContinueState(shell);
        });
        renderLibraryDashboard();
        renderAllLibraryPages();
        renderContinueReadingSection();
    });
}
