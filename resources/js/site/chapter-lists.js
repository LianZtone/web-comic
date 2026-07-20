export function applyChapterListState(shell) {
    if (!shell) {
        return;
    }

    const order = shell.dataset.chapterOrder || 'desc';
    const pageSize = Number(shell.dataset.chapterPageSize || 15);
    const visibleCount = Number(shell.dataset.chapterVisibleCount || pageSize);
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

export function initChapterLists() {
    document.querySelectorAll('[data-chapter-list-shell]').forEach((shell) => {
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
}
