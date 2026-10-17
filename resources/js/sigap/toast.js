// Toast SiGAP: di atas bottom nav (mobile), pojok kanan bawah (desktop). Opsional tombol "Urungkan".
const ICONS = {
    success: 'bi-check-circle-fill',
    danger: 'bi-x-circle-fill',
    info: 'bi-info-circle-fill',
};

function container() {
    let el = document.getElementById('sg-toasts');
    if (!el) {
        el = document.createElement('div');
        el.id = 'sg-toasts';
        el.className = 'sg-toast-container';
        el.setAttribute('aria-live', 'polite');
        document.body.appendChild(el);
    }
    return el;
}

export function toast(message, { tone = 'success', undo = null, undoLabel = 'Urungkan', delay = 5000 } = {}) {
    const el = document.createElement('div');
    el.className = `sg-toast sg-toast--${tone}`;
    el.setAttribute('role', tone === 'danger' ? 'alert' : 'status');

    const icon = document.createElement('i');
    icon.className = `bi ${ICONS[tone] || ICONS.info}`;
    icon.setAttribute('aria-hidden', 'true');

    const text = document.createElement('span');
    text.textContent = message;

    el.append(icon, text);

    const close = () => el.remove();

    if (typeof undo === 'function') {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'sg-toast__undo';
        button.textContent = undoLabel;
        button.addEventListener('click', () => {
            undo();
            close();
        });
        el.append(button);
    }

    const dismiss = document.createElement('button');
    dismiss.type = 'button';
    dismiss.className = 'sg-toast__close' + (undo ? '' : ' ms-auto');
    dismiss.setAttribute('aria-label', 'Tutup');
    dismiss.innerHTML = '<i class="bi bi-x-lg" aria-hidden="true"></i>';
    dismiss.addEventListener('click', close);
    el.append(dismiss);

    container().append(el);
    if (delay > 0) setTimeout(close, delay);

    return close;
}

// Flash session dari server (lihat <x-sg.flash />)
export function showFlashes() {
    (window.__sgFlash || []).forEach(([tone, message]) => toast(message, { tone }));
    window.__sgFlash = [];
}
