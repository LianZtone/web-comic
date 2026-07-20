function padCountdownUnit(value) {
    return String(Math.max(0, value)).padStart(2, '0');
}

function setCountdownUnit(node, value) {
    if (!node) {
        return;
    }

    const safeValue = Math.max(0, Math.min(99, value));
    node.style.setProperty('--value', String(safeValue));
    node.textContent = padCountdownUnit(safeValue);
}

function renderCountdown(container) {
    const endTime = container?.dataset.endTime;

    if (!endTime) {
        return;
    }

    const target = new Date(endTime);

    if (Number.isNaN(target.getTime())) {
        return;
    }

    const totalSeconds = Math.max(0, Math.floor((target.getTime() - Date.now()) / 1000));
    const hours = Math.floor(totalSeconds / 3600) % 100;
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    setCountdownUnit(container.querySelector('[data-countdown-hours]'), hours);
    setCountdownUnit(container.querySelector('[data-countdown-minutes]'), minutes);
    setCountdownUnit(container.querySelector('[data-countdown-seconds]'), seconds);
}

export function initCountdowns() {
    const countdowns = Array.from(document.querySelectorAll('[data-countdown]'));

    if (countdowns.length === 0) {
        return;
    }

    countdowns.forEach(renderCountdown);
    window.setInterval(() => {
        countdowns.forEach(renderCountdown);
    }, 1000);
}

export function initLiveClock() {
    const clocks = Array.from(document.querySelectorAll('[data-live-clock]'));

    if (clocks.length === 0) {
        return;
    }

    const render = () => {
        const now = new Date();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const seconds = now.getSeconds();

        clocks.forEach((clock) => {
            setCountdownUnit(clock.querySelector('[data-live-clock-hours]'), hours);
            setCountdownUnit(clock.querySelector('[data-live-clock-minutes]'), minutes);
            setCountdownUnit(clock.querySelector('[data-live-clock-seconds]'), seconds);
        });
    };

    render();
    window.setInterval(render, 1000);
}
