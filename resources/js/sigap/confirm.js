// Konfirmasi sesuai design system (bottom sheet di mobile, modal di desktop) — pengganti window.confirm.
// Pemakaian deklaratif: <button data-confirm="Hapus data ini?" data-confirm-ok="Hapus" data-confirm-tone="danger">
// atau <form data-confirm="…">. Programatik: await sgConfirm('Teks?', { ok: 'Lanjut', tone: 'danger' }).

let sheet = null;

function build() {
    const el = document.createElement('div');
    el.className = 'modal fade sg-sheet-modal';
    el.id = 'sg-confirm';
    el.tabIndex = -1;
    el.setAttribute('aria-labelledby', 'sg-confirm-title');
    el.setAttribute('aria-hidden', 'true');
    el.innerHTML = `
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="sg-sheet-handle d-lg-none" aria-hidden="true"></div>
                <div class="modal-header">
                    <h2 class="modal-title" id="sg-confirm-title">Konfirmasi</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body"><p class="mb-0" data-message></p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn" data-ok></button>
                </div>
            </div>
        </div>`;
    document.body.appendChild(el);
    return el;
}

export function confirmSheet(message, { title = 'Konfirmasi', ok = 'Lanjutkan', tone = 'primary' } = {}) {
    sheet ??= build();
    sheet.querySelector('#sg-confirm-title').textContent = title;
    sheet.querySelector('[data-message]').textContent = message;
    const okBtn = sheet.querySelector('[data-ok]');
    okBtn.textContent = ok;
    okBtn.className = `btn ${tone === 'danger' ? 'btn-reject' : 'btn-primary'}`;

    const modal = window.bootstrap.Modal.getOrCreateInstance(sheet);
    return new Promise((resolve) => {
        let confirmed = false;
        const onOk = () => { confirmed = true; modal.hide(); };
        okBtn.addEventListener('click', onOk, { once: true });
        sheet.addEventListener('hidden.bs.modal', () => {
            okBtn.removeEventListener('click', onOk);
            resolve(confirmed);
        }, { once: true });
        modal.show();
    });
}

function optionsFrom(el) {
    return {
        title: el.dataset.confirmTitle || 'Konfirmasi',
        ok: el.dataset.confirmOk || 'Lanjutkan',
        tone: el.dataset.confirmTone || 'primary',
    };
}

/** Delegasi global: klik [data-confirm] (bukan form) dan submit form[data-confirm] */
export function registerConfirm() {
    window.sgConfirm = confirmSheet;

    document.addEventListener('click', async (event) => {
        const el = event.target.closest('[data-confirm]:not(form)');
        if (!el || el.dataset.confirmed) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        if (!(await confirmSheet(el.dataset.confirm || 'Lanjutkan?', optionsFrom(el)))) return;
        el.dataset.confirmed = '1';
        el.click();
        delete el.dataset.confirmed;
    }, true);

    document.addEventListener('submit', async (event) => {
        const form = event.target;
        if (!form.matches('form[data-confirm]') || form.dataset.confirmed) return;
        event.preventDefault();
        if (!(await confirmSheet(form.dataset.confirm || 'Lanjutkan?', optionsFrom(form)))) return;
        form.dataset.confirmed = '1';
        form.requestSubmit(event.submitter ?? undefined);
        delete form.dataset.confirmed;
    }, true);
}
