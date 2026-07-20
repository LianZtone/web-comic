export const storageKey = 'velmics-theme';
export const adminDrawerStorageKey = 'velmics-admin-drawer';
const readerStorageScope = document.documentElement.dataset.readerStorageScope || 'guest';
const createScopedStorageKey = (baseKey) => `${baseKey}:${readerStorageScope}`;

export const chapterReadStorageKey = createScopedStorageKey('velmics-read-chapters');
export const chapterListViewStorageKey = createScopedStorageKey('velmics-chapter-list-view');
export const bookmarkStorageKey = createScopedStorageKey('velmics-bookmarks');
export const readlistStorageKey = createScopedStorageKey('velmics-readlist');
export const historyStorageKey = createScopedStorageKey('velmics-history');

export const root = document.documentElement;
export const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
export const adminDrawerQuery = window.matchMedia('(min-width: 1024px)');
export const defaultTheme = root.dataset.defaultTheme || root.dataset.theme || 'light';
export const darkTheme = root.dataset.darkTheme || 'dark';
