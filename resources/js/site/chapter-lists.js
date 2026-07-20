import { chapterListViewStorageKey } from './config';

function getStoredChapterViewMode() {
    const stored = localStorage.getItem(chapterListViewStorageKey);

    return stored === 'list' ? 'list' : 'grid';
}

function setChapterViewMode(shell, viewMode) {
    const panel = shell.closest('[data-chapter-panel]');
    const viewLabel = panel?.querySelector('[data-chapter-view-label]');
    const viewToggle = panel?.querySelector('[data-chapter-view-toggle]');
    const viewGridIcon = panel?.querySelector('[data-chapter-view-icon-grid]');
    const viewListIcon = panel?.querySelector('[data-chapter-view-icon-list]');
    const loadMoreButton = shell.querySelector('[data-chapter-load-more]');
    const loadMoreRow = loadMoreButton?.parentElement || null;
    const items = [...shell.querySelectorAll('[data-chapter-item]')];
    const isGrid = viewMode === 'grid';

    shell.dataset.chapterViewMode = viewMode;
    shell.dataset.chapterView = viewMode;
    shell.className = isGrid
        ? 'grid gap-3 sm:grid-cols-2 xl:grid-cols-3'
        : 'space-y-3';

    items.forEach((item) => {
        const card = item.querySelector('[data-chapter-link]');
        const arrow = item.querySelector('[data-chapter-arrow]');

        item.className = isGrid ? 'h-full' : '';

        if (!card) {
            return;
        }

        card.className = isGrid
            ? 'group flex h-full flex-col justify-between gap-4 rounded-[1.2rem] border border-base-300/70 bg-base-100/70 px-4 py-4 shadow-sm transition hover:border-primary/40 hover:bg-base-100'
            : 'group flex items-center justify-between gap-4 rounded-[1.2rem] border border-base-300/70 bg-base-100/70 px-4 py-4 shadow-sm transition hover:border-primary/40 hover:bg-base-100';

        if (arrow) {
            arrow.classList.toggle('hidden', isGrid);
        }
    });

    if (loadMoreRow) {
        loadMoreRow.className = isGrid
            ? 'col-span-full flex justify-center pt-2'
            : 'flex justify-center pt-2';
    }

    if (viewLabel) {
        viewLabel.textContent = isGrid ? 'Mode grid' : 'Mode list';
    }

    if (viewGridIcon) {
        viewGridIcon.classList.toggle('hidden', !isGrid);
    }

    if (viewListIcon) {
        viewListIcon.classList.toggle('hidden', isGrid);
    }

    if (viewToggle) {
        viewToggle.setAttribute('aria-pressed', String(isGrid));
        viewToggle.setAttribute('aria-label', isGrid ? 'Ubah ke mode list' : 'Ubah ke mode grid');
        viewToggle.classList.toggle('btn-primary', isGrid);
        viewToggle.classList.toggle('btn-ghost', !isGrid);
        viewToggle.classList.toggle('border-base-300/70', !isGrid);
    }
}

export function applyChapterListState(shell) {
    if (!shell) {
        return;
    }

    const order = shell.dataset.chapterOrder || 'desc';
    const pageSize = Number(shell.dataset.chapterPageSize || 15);
    const visibleCount = Number(shell.dataset.chapterVisibleCount || pageSize);
    const viewMode = shell.dataset.chapterViewMode || shell.dataset.chapterView || 'grid';
    const items = [...shell.querySelectorAll('[data-chapter-item]')];
    const loadMoreButton = shell.querySelector('[data-chapter-load-more]');
    const loadMoreRow = loadMoreButton?.parentElement || null;
    const panel = shell.closest('[data-chapter-panel]');
    const sortLabel = panel?.querySelector('[data-chapter-sort-label]');

    items
        .sort((first, second) => {
            const firstValue = Number(first.dataset.chapterNumber || 0);
            const secondValue = Number(second.dataset.chapterNumber || 0);

            return order === 'asc' ? firstValue - secondValue : secondValue - firstValue;
        })
        .forEach((item) => {
            shell.insertBefore(item, loadMoreRow);
        });

    setChapterViewMode(shell, viewMode);

    items.forEach((item, index) => {
        item.classList.toggle('hidden', index >= visibleCount);
    });

    if (sortLabel) {
        sortLabel.textContent = order === 'asc' ? 'Urutan awal' : 'Urutan terbaru';
    }

    if (loadMoreButton) {
        loadMoreButton.classList.toggle('hidden', visibleCount >= items.length);
        loadMoreButton.textContent = visibleCount >= items.length ? 'Semua chapter tampil' : 'Lihat selanjutnya';
    }
}

export function initChapterViewToggle() {
    document.querySelectorAll('[data-chapter-view-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const panel = toggle.closest('[data-chapter-panel]');
            const shell = panel?.querySelector('[data-chapter-list-shell]');

            if (!shell) {
                return;
            }

            const nextViewMode = (shell.dataset.chapterViewMode || shell.dataset.chapterView || 'grid') === 'grid'
                ? 'list'
                : 'grid';

            localStorage.setItem(chapterListViewStorageKey, nextViewMode);
            setChapterViewMode(shell, nextViewMode);
        });
    });
}

export function initChapterLists() {
    document.querySelectorAll('[data-chapter-list-shell]').forEach((shell) => {
        if (!shell.dataset.chapterViewMode) {
            shell.dataset.chapterViewMode = getStoredChapterViewMode();
        }

        shell.dataset.chapterView = shell.dataset.chapterViewMode;
        applyChapterListState(shell);
    });

    document.addEventListener('click', (event) => {
        const chapterSortToggle = event.target.closest('[data-chapter-sort-toggle]');

        if (chapterSortToggle) {
            const panel = chapterSortToggle.closest('[data-chapter-panel]');
            const shell = panel?.querySelector('[data-chapter-list-shell]');

            if (!shell) {
                return;
            }

            const pageSize = Number(shell.dataset.chapterPageSize || 15);
            shell.dataset.chapterOrder = shell.dataset.chapterOrder === 'asc' ? 'desc' : 'asc';
            shell.dataset.chapterVisibleCount = String(Math.min(
                Number(shell.querySelectorAll('[data-chapter-item]').length),
                pageSize,
            ));
            applyChapterListState(shell);
            return;
        }

        const chapterLoadMore = event.target.closest('[data-chapter-load-more]');

        if (!chapterLoadMore) {
            return;
        }

        const shell = chapterLoadMore.closest('[data-chapter-list-shell]');

        if (!shell) {
            return;
        }

        const pageSize = Number(shell.dataset.chapterPageSize || 15);
        const totalItems = shell.querySelectorAll('[data-chapter-item]').length;
        const visibleCount = Number(shell.dataset.chapterVisibleCount || pageSize);

        shell.dataset.chapterVisibleCount = String(Math.min(totalItems, visibleCount + pageSize));
        applyChapterListState(shell);
    });

    initChapterViewToggle();
}
