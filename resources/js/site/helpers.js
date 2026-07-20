export function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

export function extractRequestMessage(payload, fallback = 'Terjadi kesalahan.') {
    if (typeof payload?.message === 'string' && payload.message.trim() !== '') {
        return payload.message;
    }

    const firstError = payload?.errors && Object.values(payload.errors)[0];

    if (Array.isArray(firstError) && typeof firstError[0] === 'string') {
        return firstError[0];
    }

    return fallback;
}

export async function submitJsonForm(form) {
    const response = await fetch(form.action, {
        method: (form.method || 'POST').toUpperCase(),
        body: new FormData(form),
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    let payload = {};

    try {
        payload = await response.json();
    } catch {
        payload = {};
    }

    if (!response.ok) {
        const error = new Error(extractRequestMessage(payload));
        error.payload = payload;
        error.status = response.status;
        throw error;
    }

    return payload;
}
