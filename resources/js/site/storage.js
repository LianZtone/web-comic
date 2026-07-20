import { chapterReadStorageKey } from './config';

export function getStoredItems(key) {
    try {
        const parsed = JSON.parse(localStorage.getItem(key) || '[]');
        return Array.isArray(parsed) ? parsed : [];
    } catch {
        return [];
    }
}

function setStoredItems(key, items) {
    localStorage.setItem(key, JSON.stringify(items));
}

export function upsertStoredItem(key, item, identity = 'slug') {
    if (!item?.[identity]) {
        return [];
    }

    const items = getStoredItems(key).filter((entry) => entry?.[identity] !== item[identity]);
    const nextItems = [{ ...item, savedAt: item.savedAt || new Date().toISOString() }, ...items].slice(0, 24);
    setStoredItems(key, nextItems);

    return nextItems;
}

export function removeStoredItem(key, value, identity = 'slug') {
    const items = getStoredItems(key).filter((entry) => entry?.[identity] !== value);
    setStoredItems(key, items);

    return items;
}

export function hasStoredItem(key, value, identity = 'slug') {
    return getStoredItems(key).some((entry) => entry?.[identity] === value);
}

function getReadChapters() {
    try {
        const parsed = JSON.parse(localStorage.getItem(chapterReadStorageKey) || '{}');
        return parsed && typeof parsed === 'object' ? parsed : {};
    } catch {
        return {};
    }
}

export function hasReadChapter(comicSlug, chapterNumber) {
    const readChapters = getReadChapters();
    return Boolean(readChapters?.[comicSlug]?.[String(chapterNumber)]);
}

export function markChapterAsRead(comicSlug, chapterNumber) {
    if (!comicSlug || !chapterNumber) {
        return;
    }

    const readChapters = getReadChapters();
    readChapters[comicSlug] = {
        ...(readChapters[comicSlug] || {}),
        [String(chapterNumber)]: true,
    };

    localStorage.setItem(chapterReadStorageKey, JSON.stringify(readChapters));
}
