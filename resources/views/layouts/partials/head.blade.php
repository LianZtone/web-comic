<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,700&family=Sora:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])


<style>
    :root {
        --velmics-border-soft: rgba(15, 23, 42, 0.10);
        --velmics-border-soft-strong: rgba(15, 23, 42, 0.14);
    }

    [data-theme='dark'] {
        --velmics-border-soft: rgba(255, 255, 255, 0.09);
        --velmics-border-soft-strong: rgba(255, 255, 255, 0.12);
    }

    html,
    body {
        transition: background-color 180ms ease, color 180ms ease;
        scroll-behavior: smooth;
    }

    .border-base-300\/70 {
        border-color: var(--velmics-border-soft) !important;
    }

    .border-base-300,
    .border-base-300\/80 {
        border-color: var(--velmics-border-soft-strong) !important;
    }

    .navbar .input > input,
    .navbar .input > input:focus,
    .navbar .input > input:focus-visible {
        background: transparent;
        border: 0;
        box-shadow: none;
        outline: none;
    }

    [data-theme='light'] body {
        background:
            radial-gradient(circle at top left, rgba(87, 140, 255, 0.10), transparent 26%),
            radial-gradient(circle at top right, rgba(255, 180, 76, 0.10), transparent 22%),
            linear-gradient(180deg, #f5f7fb 0%, #edf1f8 100%);
    }

    [data-theme='dark'] body {
        background:
            radial-gradient(circle at top left, rgba(87, 140, 255, 0.16), transparent 26%),
            radial-gradient(circle at top right, rgba(255, 180, 76, 0.12), transparent 24%),
            linear-gradient(180deg, rgba(17, 18, 23, 0.96), rgba(12, 13, 18, 1));
    }

</style>

<script>
    (function() {
        const root = document.documentElement;
        const key = 'velmics-theme';
        const stored = localStorage.getItem(key);
        const defaultTheme = root.dataset.defaultTheme || root.dataset.theme || 'light';
        const darkTheme = root.dataset.darkTheme || 'dark';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        root.dataset.theme = stored || (prefersDark ? darkTheme : defaultTheme);
        root.style.colorScheme = root.dataset.theme === darkTheme ? 'dark' : 'light';
    }());
</script>
