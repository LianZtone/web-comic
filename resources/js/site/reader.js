import { submitJsonForm } from './helpers';

function applyReaderState(shell) {
    const clean = shell.dataset.readerMode === 'clean';
    const modalOpen = Array.from(shell.querySelectorAll('[data-reader-modal]')).some((modal) => !modal.classList.contains('hidden'));
    const chromeProgress = clean ? 1 : Math.max(0, Math.min(1, Number(shell.dataset.readerChromeProgress || 0)));

    shell.querySelectorAll('[data-reader-chrome]').forEach((node) => {
        const translateDistance = node.dataset.readerChromeType === 'top' ? -18 : 18;
        const opacity = clean || modalOpen ? 0 : Math.max(0, 1 - chromeProgress);
        const translateY = clean || modalOpen ? translateDistance : Math.round(translateDistance * chromeProgress);
        const shouldDisablePointer = clean || modalOpen || chromeProgress >= 0.92;

        node.classList.toggle('pointer-events-none', shouldDisablePointer);
        node.style.opacity = String(opacity);
        node.style.transform = `translateY(${translateY}px)`;
    });
}

function syncReaderAutoHide(shell) {
    const scroller = document.scrollingElement || document.documentElement;
    const currentScrollTop = scroller.scrollTop || 0;
    const previousScrollTop = Number(shell.dataset.readerLastScrollTop || currentScrollTop);
    const delta = currentScrollTop - previousScrollTop;
    let chromeProgress = Number(shell.dataset.readerChromeProgress || 0);

    if (currentScrollTop <= 32) {
        chromeProgress = 0;
    } else if (delta > 0) {
        chromeProgress = Math.min(1, chromeProgress + (Math.min(delta, 32) / 120));
    } else if (delta < 0) {
        chromeProgress = Math.max(0, chromeProgress - (Math.min(Math.abs(delta), 32) / 90));
    }

    shell.dataset.readerLastScrollTop = String(currentScrollTop);
    shell.dataset.readerChromeProgress = String(chromeProgress);
    applyReaderState(shell);
}

function updateReaderScrollButtons(shell) {
    const scroller = document.scrollingElement || document.documentElement;
    const viewportHeight = scroller.clientHeight || window.innerHeight || 0;
    const scrollTop = scroller.scrollTop || 0;
    const scrollHeight = scroller.scrollHeight || 0;
    const nearTop = scrollTop <= 24;
    const nearBottom = scrollTop + viewportHeight >= scrollHeight - 24;

    shell.querySelectorAll('[data-reader-scroll-control="top"]').forEach((button) => {
        button.classList.toggle('opacity-0', nearTop);
        button.classList.toggle('pointer-events-none', nearTop);
    });

    shell.querySelectorAll('[data-reader-scroll-control="bottom"]').forEach((button) => {
        button.classList.toggle('opacity-0', nearBottom);
        button.classList.toggle('pointer-events-none', nearBottom);
    });
}

function initReaderShells() {
    document.querySelectorAll('[data-reader-shell]').forEach((shell) => {
        if (!shell.dataset.readerMode) {
            shell.dataset.readerMode = 'default';
        }

        shell.dataset.readerChromeProgress = '0';
        shell.dataset.readerLastScrollTop = String((document.scrollingElement || document.documentElement).scrollTop || 0);

        applyReaderState(shell);
        updateReaderScrollButtons(shell);

        if (shell.dataset.readerFeedbackFocus === 'true') {
            window.setTimeout(() => {
                shell.querySelector('#reader-feedback')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                });
            }, 120);
        }
    });
}

function revealReaderPageImage(image) {
    if (!image) {
        return;
    }

    const card = image.closest('[data-reader-page-card]');
    const skeleton = card?.querySelector('[data-reader-page-skeleton]');

    image.classList.remove('opacity-0');
    image.classList.add('opacity-100');
    card?.classList.remove('min-h-[18rem]', 'sm:min-h-[28rem]');
    skeleton?.classList.add('hidden');
}

function initReaderPageImages() {
    document.querySelectorAll('[data-reader-page-image]').forEach((image) => {
        if (image.complete) {
            revealReaderPageImage(image);
            return;
        }

        image.addEventListener('load', () => {
            revealReaderPageImage(image);
        }, { once: true });

        image.addEventListener('error', () => {
            revealReaderPageImage(image);
        }, { once: true });
    });
}

function applyReaderSearch(modal) {
    const query = (modal.querySelector('[data-reader-search]')?.value || '').trim().toLowerCase();
    let visibleCount = 0;

    modal.querySelectorAll('[data-reader-chapter-item]').forEach((item) => {
        const matches = query === '' || (item.dataset.readerSearchText || '').includes(query);
        item.classList.toggle('hidden', !matches);

        if (matches) {
            visibleCount += 1;
        }
    });

    const emptyState = modal.querySelector('[data-reader-search-empty]');

    if (emptyState) {
        emptyState.classList.toggle('hidden', visibleCount > 0);
    }
}

function getReaderAlertTone(type) {
    if (type === 'success') {
        return 'alert-success';
    }

    if (type === 'warning') {
        return 'alert-warning';
    }

    return 'alert-error';
}

function showReaderFeedback(shell, type, message) {
    const alert = shell?.querySelector('[data-reader-feedback-alert]');
    const messageNode = alert?.querySelector('[data-reader-feedback-message]');

    if (!alert || !messageNode) {
        return;
    }

    alert.classList.remove('hidden', 'alert-success', 'alert-warning', 'alert-error');
    alert.classList.add(getReaderAlertTone(type));
    messageNode.textContent = message || 'Terjadi kesalahan.';
}

function updateReaderCommentCount(shell, count) {
    shell?.querySelectorAll('[data-reader-comment-count]').forEach((node) => {
        node.textContent = `${count} komentar`;
    });
}

function updateCaptchaChallenge(scope, question) {
    if (!scope || !question) {
        return;
    }

    document.querySelectorAll(`[data-captcha-question="${scope}"]`).forEach((node) => {
        node.textContent = question;
    });

    document.querySelectorAll(`[data-captcha-answer="${scope}"]`).forEach((input) => {
        input.value = '';
    });
}

function syncReaderEmptyComments(shell) {
    const list = shell?.querySelector('[data-reader-comments-list]');
    const emptyState = shell?.querySelector('[data-reader-comments-empty]');

    if (!list || !emptyState) {
        return;
    }

    const hasCards = list.querySelector('[data-reader-comment-card]');
    emptyState.classList.toggle('hidden', Boolean(hasCards));
}

function syncRepliesGroup(group) {
    if (!group) {
        return;
    }

    const items = Array.from(group.querySelectorAll(':scope > [data-reader-replies-list] > [data-reader-reply-item]'));
    const toggle = group.querySelector('[data-reader-replies-toggle]');
    const expanded = group.dataset.readerRepliesExpanded === 'true';

    items.forEach((item, index) => {
        item.classList.toggle('hidden', !expanded && index > 0);
    });

    if (!toggle) {
        return;
    }

    const hiddenCount = Math.max(0, items.length - 1);
    toggle.classList.toggle('hidden', hiddenCount === 0);
    toggle.textContent = expanded
        ? (toggle.dataset.readerRepliesLabelExpanded || 'Sembunyikan balasan')
        : (toggle.dataset.readerRepliesLabelCollapsed || `Tampilkan ${hiddenCount} balasan`);
}

function syncAllRepliesGroups(root = document) {
    root.querySelectorAll('[data-reader-replies-shell]').forEach((group) => {
        syncRepliesGroup(group);
    });
}

function prependReaderComment(shell, commentHtml) {
    const list = shell?.querySelector('[data-reader-comments-list]');
    const emptyState = shell?.querySelector('[data-reader-comments-empty]');

    if (!list || !commentHtml) {
        return;
    }

    if (emptyState) {
        emptyState.insertAdjacentHTML('beforebegin', commentHtml);
    } else {
        list.insertAdjacentHTML('afterbegin', commentHtml);
    }

    syncReaderEmptyComments(shell);
    syncAllRepliesGroups(list);
}

function appendReaderReply(shell, rootCommentId, commentHtml) {
    const rootComment = shell?.querySelector(`[data-reader-comment-card="${rootCommentId}"]`);
    const list = rootComment?.querySelector('[data-reader-replies-list]');
    const group = rootComment?.querySelector('[data-reader-replies-shell]');

    if (!list || !commentHtml) {
        return;
    }

    list.insertAdjacentHTML('beforeend', `<div data-reader-reply-item>${commentHtml}</div>`);

    if (group) {
        group.dataset.readerRepliesExpanded = 'true';
        syncRepliesGroup(group);
    }
}

function replaceReaderComment(shell, commentId, commentHtml) {
    const current = shell?.querySelector(`[data-reader-comment-card="${commentId}"]`);

    if (!current || !commentHtml) {
        return;
    }

    const template = document.createElement('template');
    template.innerHTML = commentHtml.trim();

    const next = template.content.firstElementChild;

    if (!next) {
        return;
    }

    const existingRepliesShell = current.querySelector(':scope > [data-reader-replies-shell]');
    const nextRepliesShell = next.querySelector(':scope > [data-reader-replies-shell]');

    if (existingRepliesShell && nextRepliesShell) {
        nextRepliesShell.replaceWith(existingRepliesShell.cloneNode(true));
    }

    current.replaceWith(next);
    syncReaderEmptyComments(shell);
    syncAllRepliesGroups(shell);
}

function appendMentionToken(textarea, mention) {
    if (!textarea || !mention) {
        return;
    }

    const normalizedMention = mention.startsWith('@') ? mention : `@${mention}`;
    const escapedMention = normalizedMention.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const mentionPattern = new RegExp(`(^|\\s)${escapedMention}(?=\\s|$)`, 'i');
    const currentValue = textarea.value.trimEnd();

    if (mentionPattern.test(currentValue)) {
        textarea.focus();
        textarea.setSelectionRange(textarea.value.length, textarea.value.length);
        return;
    }

    textarea.value = currentValue === '' ? `${normalizedMention} ` : `${currentValue} ${normalizedMention} `;
    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
}

function applyCommentMarkup(textarea, tag) {
    if (!textarea || !tag) {
        return;
    }

    const openTag = `[${tag}]`;
    const closeTag = `[/${tag}]`;
    const start = textarea.selectionStart ?? textarea.value.length;
    const end = textarea.selectionEnd ?? textarea.value.length;
    const selectedText = textarea.value.slice(start, end);
    const nextValue = textarea.value.slice(0, start) + openTag + selectedText + closeTag + textarea.value.slice(end);
    const cursorStart = start + openTag.length;
    const cursorEnd = cursorStart + selectedText.length;

    textarea.value = nextValue;
    textarea.focus();

    if (selectedText === '') {
        textarea.setSelectionRange(cursorStart, cursorStart);
    } else {
        textarea.setSelectionRange(cursorStart, cursorEnd);
    }

    textarea.dispatchEvent(new Event('input', { bubbles: true }));
}

function insertCommentToken(textarea, token) {
    if (!textarea || !token) {
        return;
    }

    const start = textarea.selectionStart ?? textarea.value.length;
    const end = textarea.selectionEnd ?? textarea.value.length;
    const before = textarea.value.slice(0, start);
    const after = textarea.value.slice(end);
    const needsLeadingSpace = before !== '' && !/\s$/.test(before);
    const needsTrailingSpace = after !== '' && !/^\s/.test(after);
    const insertion = `${needsLeadingSpace ? ' ' : ''}${token}${needsTrailingSpace ? ' ' : ''}`;
    const nextValue = before + insertion + after;
    const cursor = before.length + insertion.length;

    textarea.value = nextValue;
    textarea.focus();
    textarea.setSelectionRange(cursor, cursor);
    textarea.dispatchEvent(new Event('input', { bubbles: true }));
}

function syncCommentPickerPanels(shell, openPanel = null) {
    shell?.querySelectorAll('[data-comment-picker-panel]').forEach((panel) => {
        panel.classList.toggle('hidden', panel.dataset.commentPickerPanel !== openPanel);
    });
}

function syncCommentImagePreview(input) {
    const shell = input?.closest('[data-comment-markup-shell]');
    const file = input?.files?.[0];

    if (!shell) {
        return;
    }

    const previewShells = shell.querySelectorAll('[data-comment-image-preview-shell]');
    const previousUrl = input.dataset.objectUrl;

    if (previousUrl) {
        URL.revokeObjectURL(previousUrl);
        delete input.dataset.objectUrl;
    }

    if (!file) {
        previewShells.forEach((previewShell) => {
            const previewImage = previewShell.querySelector('[data-comment-image-preview-image]');
            const previewName = previewShell.querySelector('[data-comment-image-preview-name]');

            previewShell.classList.add('hidden');
            previewShell.classList.remove('flex');
            previewImage?.classList.add('hidden');
            previewImage?.removeAttribute('src');

            if (previewName) {
                previewName.textContent = '';
            }
        });
        return;
    }

    const objectUrl = URL.createObjectURL(file);

    input.dataset.objectUrl = objectUrl;

    previewShells.forEach((previewShell) => {
        const previewImage = previewShell.querySelector('[data-comment-image-preview-image]');
        const previewName = previewShell.querySelector('[data-comment-image-preview-name]');

        if (!previewImage || !previewName) {
            return;
        }

        previewImage.src = objectUrl;
        previewImage.classList.remove('hidden');
        previewName.textContent = file.name;
        previewShell.classList.remove('hidden');
        previewShell.classList.add('flex');
    });
}

function resetCommentAttachment(form) {
    if (!form) {
        return;
    }

    form.querySelectorAll('[data-comment-image-modal]').forEach((modal) => {
        modal.classList.add('hidden', 'opacity-0', 'pointer-events-none');
        modal.classList.remove('opacity-100');
    });

    const imageInput = form.querySelector('[data-comment-image-input]');

    if (imageInput) {
        imageInput.value = '';
        syncCommentImagePreview(imageInput);
    }

    syncCommentPickerPanels(form.querySelector('[data-comment-markup-shell]'), null);
    syncDocumentScrollLock();
}

function syncDocumentScrollLock() {
    const hasOpenModal = Boolean(
        document.querySelector('[data-reader-modal]:not(.hidden), [data-comment-image-modal]:not(.hidden)'),
    );

    document.body.classList.toggle('overflow-hidden', hasOpenModal);
}

function openCommentImageModal(modal) {
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
    });

    syncDocumentScrollLock();
    modal.querySelector('[data-comment-image-picker-open], [data-comment-image-modal-close]')?.focus();
}

function closeCommentImageModal(modal) {
    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');

    window.setTimeout(() => {
        if (modal.classList.contains('opacity-0')) {
            modal.classList.add('hidden');
            syncDocumentScrollLock();
        }
    }, 180);
}

function primeReplyForm(formShell, mention) {
    const textarea = formShell?.querySelector('textarea[name="body"]');

    if (!textarea) {
        return;
    }

    if (textarea.value.trim() === '' && mention) {
        textarea.value = `${mention} `;
    }

    textarea.focus();
    textarea.setSelectionRange(textarea.value.length, textarea.value.length);
}

function updateReaderReactions(shell, reactions) {
    if (!Array.isArray(reactions)) {
        return;
    }

    const total = reactions.reduce((sum, reaction) => sum + Number(reaction.count || 0), 0);

    shell?.querySelectorAll('[data-reader-reaction-total]').forEach((node) => {
        node.textContent = `${total} reaksi`;
    });

    reactions.forEach((reaction) => {
        const form = shell?.querySelector(`[data-reader-reaction-form][data-reaction-type="${reaction.key}"]`);
        const button = form?.querySelector('[data-reader-reaction-button]');
        const badge = form?.querySelector('[data-reader-reaction-count]');

        if (!form || !button || !badge) {
            return;
        }

        button.classList.toggle('btn-primary', Boolean(reaction.active));
        button.classList.toggle('border', !reaction.active);
        button.classList.toggle('border-base-300/70', !reaction.active);
        button.classList.toggle('bg-base-200/45', !reaction.active);
        button.classList.toggle('text-base-content', !reaction.active);
        button.classList.toggle('hover:border-primary/30', !reaction.active);
        button.classList.toggle('hover:bg-primary/10', !reaction.active);

        badge.classList.toggle('badge-neutral', Boolean(reaction.active));
        badge.classList.toggle('badge-ghost', !reaction.active);
        badge.textContent = reaction.count;
    });
}

function markReaderBusy(form, busy) {
    const submitButtons = form.querySelectorAll('button[type="submit"]');

    submitButtons.forEach((button) => {
        button.disabled = busy;
        button.classList.toggle('loading', busy);
    });
}

function openReaderModal(modal) {
    const shell = modal.closest('[data-reader-shell]');

    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
    });

    syncDocumentScrollLock();
    if (shell) {
        applyReaderState(shell);
    }

    applyReaderSearch(modal);
    modal.querySelector('[data-reader-search]')?.focus();
}

function closeReaderModal(modal) {
    const shell = modal.closest('[data-reader-shell]');

    modal.classList.add('opacity-0', 'pointer-events-none');
    modal.classList.remove('opacity-100');

    window.setTimeout(() => {
        if (modal.classList.contains('opacity-0')) {
            modal.classList.add('hidden');
            syncDocumentScrollLock();

            if (shell) {
                applyReaderState(shell);
                updateReaderScrollButtons(shell);
            }
        }
    }, 180);
}

function handleReaderClick(event) {
    const readerToggle = event.target.closest('[data-reader-toggle]');

    if (readerToggle) {
        const shell = readerToggle.closest('[data-reader-shell]');

        if (!shell) {
            return;
        }

        shell.dataset.readerMode = shell.dataset.readerMode === 'clean' ? 'default' : 'clean';
        applyReaderState(shell);
        return;
    }

    const replyToggle = event.target.closest('[data-reader-reply-toggle]');

    if (replyToggle) {
        const card = replyToggle.closest('[data-reader-comment-card]');
        const formShell = card?.querySelector('[data-reader-reply-form-shell]');
        const editShell = card?.querySelector('[data-reader-edit-form-shell]');

        if (!formShell) {
            return;
        }

        editShell?.classList.add('hidden');
        formShell.classList.toggle('hidden');

        if (!formShell.classList.contains('hidden')) {
            primeReplyForm(formShell, replyToggle.dataset.readerReplyTarget || '');
        }

        return;
    }

    const editToggle = event.target.closest('[data-reader-edit-toggle]');

    if (editToggle) {
        const card = editToggle.closest('[data-reader-comment-card]');
        const formShell = card?.querySelector('[data-reader-edit-form-shell]');
        const replyShell = card?.querySelector('[data-reader-reply-form-shell]');

        if (!formShell) {
            return;
        }

        replyShell?.classList.add('hidden');
        formShell.classList.toggle('hidden');

        if (!formShell.classList.contains('hidden')) {
            const textarea = formShell.querySelector('textarea[name="body"]');
            textarea?.focus();
            textarea?.setSelectionRange(textarea.value.length, textarea.value.length);
        }

        return;
    }

    const replyCancel = event.target.closest('[data-reader-reply-cancel]');

    if (replyCancel) {
        const formShell = replyCancel.closest('[data-reader-reply-form-shell]');

        if (!formShell) {
            return;
        }

        formShell.classList.add('hidden');
        const textarea = formShell.querySelector('textarea[name="body"]');

        if (textarea) {
            textarea.value = '';
        }
        resetCommentAttachment(formShell);
        return;
    }

    const editCancel = event.target.closest('[data-reader-edit-cancel]');

    if (editCancel) {
        const formShell = editCancel.closest('[data-reader-edit-form-shell]');

        if (!formShell) {
            return;
        }

        formShell.classList.add('hidden');
        return;
    }

    const mentionChip = event.target.closest('[data-reader-mention-chip]');

    if (mentionChip) {
        const formShell = mentionChip.closest('[data-reader-reply-form-shell]');
        const textarea = formShell?.querySelector('textarea[name="body"]');

        appendMentionToken(textarea, mentionChip.dataset.readerMentionValue || '');
        return;
    }

    const markupButton = event.target.closest('[data-comment-markup-tag]');

    if (markupButton) {
        const scope = markupButton.closest('form, [data-reader-reply-form-shell], [data-reader-edit-form-shell]');
        const textarea = scope?.querySelector('textarea[name="body"]');

        applyCommentMarkup(textarea, markupButton.dataset.commentMarkupTag || '');
        return;
    }

    const pickerToggle = event.target.closest('[data-comment-picker-toggle]');

    if (pickerToggle) {
        const shell = pickerToggle.closest('[data-comment-markup-shell]');
        const panelName = pickerToggle.dataset.commentPickerToggle || '';
        const targetPanel = shell?.querySelector(`[data-comment-picker-panel="${panelName}"]`);
        const shouldOpen = targetPanel?.classList.contains('hidden') ?? false;

        syncCommentPickerPanels(shell, shouldOpen ? panelName : null);
        return;
    }

    const tokenButton = event.target.closest('[data-comment-insert-token]');

    if (tokenButton) {
        const shell = tokenButton.closest('[data-comment-markup-shell]');
        const scope = tokenButton.closest('form, [data-reader-reply-form-shell], [data-reader-edit-form-shell]');
        const textarea = scope?.querySelector('textarea[name="body"]');

        insertCommentToken(textarea, tokenButton.dataset.commentInsertToken || '');
        shell?.querySelectorAll('[data-comment-picker-panel]').forEach((panel) => {
            panel.classList.add('hidden');
        });
        return;
    }

    const imageTrigger = event.target.closest('[data-comment-image-trigger]');

    if (imageTrigger) {
        const shell = imageTrigger.closest('[data-comment-markup-shell]');

        syncCommentPickerPanels(shell, null);

        const modal = shell?.querySelector('[data-comment-image-modal]');

        if (modal) {
            openCommentImageModal(modal);
            return;
        }

        shell?.querySelector('[data-comment-image-input]')?.click();
        return;
    }

    const imagePickerOpen = event.target.closest('[data-comment-image-picker-open]');

    if (imagePickerOpen) {
        const shell = imagePickerOpen.closest('[data-comment-markup-shell]');
        shell?.querySelector('[data-comment-image-input]')?.click();
        return;
    }

    const imageModalClose = event.target.closest('[data-comment-image-modal-close]');

    if (imageModalClose) {
        const modal = imageModalClose.closest('[data-comment-image-modal]');

        if (modal) {
            closeCommentImageModal(modal);
        }
        return;
    }

    const imageClear = event.target.closest('[data-comment-image-clear]');

    if (imageClear) {
        const shell = imageClear.closest('[data-comment-markup-shell]');
        const input = shell?.querySelector('[data-comment-image-input]');

        if (input) {
            input.value = '';
            syncCommentImagePreview(input);
        }
        return;
    }

    const commentImageModal = event.target.closest('[data-comment-image-modal]');

    if (commentImageModal && event.target === commentImageModal) {
        closeCommentImageModal(commentImageModal);
        return;
    }

    const repliesToggle = event.target.closest('[data-reader-replies-toggle]');

    if (repliesToggle) {
        const group = repliesToggle.closest('[data-reader-replies-shell]');

        if (!group) {
            return;
        }

        group.dataset.readerRepliesExpanded = group.dataset.readerRepliesExpanded === 'true' ? 'false' : 'true';
        syncRepliesGroup(group);
        return;
    }

    const openModalButton = event.target.closest('[data-reader-modal-open]');

    if (openModalButton) {
        const shell = openModalButton.closest('[data-reader-shell]');
        const modal = shell?.querySelector('[data-reader-modal]');

        if (!modal) {
            return;
        }

        openReaderModal(modal);
        return;
    }

    const closeModalButton = event.target.closest('[data-reader-modal-close]');

    if (closeModalButton) {
        const modal = closeModalButton.closest('[data-reader-modal]');

        if (!modal) {
            return;
        }

        closeReaderModal(modal);
        return;
    }

    const modal = event.target.closest('[data-reader-modal]');

    if (modal && event.target === modal) {
        closeReaderModal(modal);
        return;
    }

    const scrollButton = event.target.closest('[data-reader-scroll]');

    if (scrollButton) {
        const shell = scrollButton.closest('[data-reader-shell]');
        const direction = scrollButton.dataset.readerScroll;
        const target = shell?.querySelector(`[data-reader-scroll-target="${direction}"]`);

        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: direction === 'top' ? 'start' : 'end',
            });
            return;
        }

        const scroller = document.scrollingElement || document.documentElement;
        const top = direction === 'top' ? 0 : Math.max(0, scroller.scrollHeight - scroller.clientHeight);

        window.scrollTo({
            top,
            behavior: 'smooth',
        });
        return;
    }

    const readerShell = event.target.closest('[data-reader-shell]');

    if (!readerShell) {
        return;
    }

    if (event.target.closest('a, button, input, textarea, select, label, form, [data-reader-chrome], #reader-feedback')) {
        return;
    }

    const readerSurface = event.target.closest('[data-reader-surface]');

    if (readerSurface) {
        if (readerShell.dataset.readerMode === 'clean') {
            readerShell.dataset.readerMode = 'default';
            readerShell.dataset.readerChromeProgress = '0';
            applyReaderState(readerShell);
        }

        return;
    }

    readerShell.dataset.readerMode = 'clean';
    applyReaderState(readerShell);
}

async function handleReaderSubmit(event) {
    const reactionForm = event.target.closest('[data-reader-reaction-form]');
    const commentForm = event.target.closest('[data-reader-comment-form]');
    const commentVoteForm = event.target.closest('[data-reader-comment-vote-form]');
    const form = reactionForm || commentForm || commentVoteForm;

    if (!form) {
        return;
    }

    const shell = form.closest('[data-reader-shell], [data-feedback-shell]');

    if (!shell) {
        return;
    }

    event.preventDefault();
    markReaderBusy(form, true);

    try {
        const payload = await submitJsonForm(form);

        if (reactionForm) {
            updateReaderReactions(shell, payload.reactions);
            showReaderFeedback(shell, 'success', payload.message);
        }

        if (commentForm) {
            updateCaptchaChallenge(form.dataset.captchaScope, payload.captcha_question);
            const parentId = form.querySelector('input[name="parent_id"]')?.value;

            if (parentId) {
                appendReaderReply(shell, payload.comment?.root_id || parentId, payload.comment_html);
                form.closest('[data-reader-reply-form-shell]')?.classList.add('hidden');
            } else {
                prependReaderComment(shell, payload.comment_html);
            }

            updateReaderCommentCount(shell, payload.comment_count || 0);
            form.querySelector('textarea[name="body"]').value = '';
            resetCommentAttachment(form);
            showReaderFeedback(shell, 'success', payload.message);
        }

        if (commentVoteForm) {
            replaceReaderComment(shell, payload.comment?.id, payload.comment_html);
            showReaderFeedback(shell, 'success', payload.message);
        }

        if (shell.dataset.readerFeedbackUrl) {
            window.history.replaceState({}, '', shell.dataset.readerFeedbackUrl);
        }
    } catch (error) {
        updateCaptchaChallenge(form.dataset.captchaScope, error.payload?.captcha_question);
        showReaderFeedback(shell, 'error', error.message || 'Terjadi kesalahan.');
    } finally {
        markReaderBusy(form, false);
    }
}

function handleReaderInput(event) {
    const search = event.target.closest('[data-reader-search]');

    if (!search) {
        return;
    }

    const modal = search.closest('[data-reader-modal]');

    if (!modal) {
        return;
    }

    applyReaderSearch(modal);
}

function handleReaderChange(event) {
    const imageInput = event.target.closest('[data-comment-image-input]');

    if (!imageInput) {
        return;
    }

    syncCommentImagePreview(imageInput);
}

function handleReaderScroll() {
    document.querySelectorAll('[data-reader-shell]').forEach((shell) => {
        syncReaderAutoHide(shell);
        updateReaderScrollButtons(shell);
    });
}

export function initReader() {
    initReaderShells();
    initReaderPageImages();
    syncAllRepliesGroups();

    document.addEventListener('click', handleReaderClick);
    document.addEventListener('change', handleReaderChange);
    document.addEventListener('submit', handleReaderSubmit);
    document.addEventListener('input', handleReaderInput);

    window.addEventListener('scroll', handleReaderScroll, { passive: true });
}
