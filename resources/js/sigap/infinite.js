// Infinite scroll daftar (mobile <992px): kartu di [data-sg-infinite] + pagination di [data-sg-pager].
// Halaman berikutnya diambil dari link rel="next" pagination biasa (filter & query string ikut), lalu kartunya ditempel.
// Desktop tetap memakai pagination. Tanpa JS pun pagination tetap jalan.
export function infiniteLists() {
    const list = document.querySelector('[data-sg-infinite]');
    const pager = document.querySelector('[data-sg-pager]');
    if (!list || !pager || !('IntersectionObserver' in window)) return;

    const mobile = window.matchMedia('(max-width: 991.98px)');
    const status = document.createElement('div');
    status.className = 'sg-infinite-status d-lg-none';
    status.setAttribute('aria-live', 'polite');
    pager.before(status);

    const nextUrl = () => pager.querySelector('a[rel="next"]')?.href;
    let loading = false;

    const render = () => {
        pager.classList.toggle('d-none', mobile.matches);
        status.textContent = nextUrl() ? '' : list.children.length > 5 ? 'Semua laporan sudah ditampilkan.' : '';
    };

    const load = async () => {
        const url = nextUrl();
        if (loading || !url || !mobile.matches) return;
        loading = true;
        status.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Memuat…';
        try {
            const response = await fetch(url, { headers: { Accept: 'text/html' } });
            const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
            const nextList = doc.querySelector('[data-sg-infinite]');
            const nextPager = doc.querySelector('[data-sg-pager]');
            if (nextList) list.append(...nextList.children);
            pager.innerHTML = nextPager ? nextPager.innerHTML : '';
        } catch (e) {
            status.textContent = 'Gagal memuat. Gulir lagi untuk mencoba.';
            loading = false;
            return;
        }
        loading = false;
        render();
    };

    const sentinel = document.createElement('div');
    sentinel.setAttribute('aria-hidden', 'true');
    status.after(sentinel);
    new IntersectionObserver((entries) => entries[0].isIntersecting && load(), { rootMargin: '600px 0px' }).observe(sentinel);
    mobile.addEventListener?.('change', render);
    render();
}

// Tarik untuk muat ulang: hanya di PWA terpasang (browser biasa sudah punya bawaan).
export function pullToRefresh() {
    const standalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    if (!standalone) return;

    const indicator = document.createElement('div');
    indicator.className = 'sg-ptr';
    indicator.innerHTML = '<i class="bi bi-arrow-down" aria-hidden="true"></i>';
    document.body.append(indicator);

    const TRIGGER = 72;
    let startY = null;
    let distance = 0;

    window.addEventListener('touchstart', (event) => {
        startY = window.scrollY <= 0 && !document.querySelector('.modal.show') ? event.touches[0].clientY : null;
        distance = 0;
    }, { passive: true });

    window.addEventListener('touchmove', (event) => {
        if (startY === null) return;
        distance = Math.max(0, Math.min(120, event.touches[0].clientY - startY));
        indicator.style.transform = `translate(-50%, ${distance - 48}px) rotate(${distance * 2.5}deg)`;
        indicator.classList.toggle('is-ready', distance >= TRIGGER);
    }, { passive: true });

    window.addEventListener('touchend', () => {
        if (startY === null) return;
        startY = null;
        if (distance >= TRIGGER) {
            indicator.classList.add('is-loading');
            window.location.reload();
            return;
        }
        indicator.style.transform = '';
        indicator.classList.remove('is-ready');
    });
}
