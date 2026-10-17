// Antrian kirim laporan saat offline (IndexedDB "sigap-outbox"). Isi form disimpan apa adanya (termasuk File lampiran)
// lalu dikirim ulang dengan fetch saat online. Server menolak duplikat lewat idempotency_key (DailyReportController@storeMultiple).
// Dikirim dari halaman (event "online" + saat halaman dibuka), bukan dari service worker: token CSRF diambil dari halaman yang aktif.
const DB_NAME = 'sigap-outbox';
const STORE = 'reports';

function openDb() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, 1);
        request.onupgradeneeded = () => request.result.createObjectStore(STORE, { keyPath: 'id' });
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });
}

async function run(mode, action) {
    const db = await openDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, mode);
        const request = action(tx.objectStore(STORE));
        tx.oncomplete = () => resolve(request?.result);
        tx.onerror = () => reject(tx.error);
    });
}

const all = () => run('readonly', (store) => store.getAll());
const put = (item) => run('readwrite', (store) => store.put(item));
const remove = (id) => run('readwrite', (store) => store.delete(id));

export function newKey() {
    return window.crypto?.randomUUID?.() ?? `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

export default function outboxStore() {
    return {
        items: [],
        sending: false,
        get count() {
            return this.items.length;
        },
        get failed() {
            return this.items.filter((i) => i.error);
        },

        async refresh() {
            if (!window.indexedDB) return;
            try {
                const user = window.__sgUserId;
                this.items = (await all()).filter((i) => i.user === user).sort((a, b) => a.createdAt - b.createdAt);
            } catch (e) {
                this.items = [];
            }
        },

        // Simpan form ke antrian. Mengembalikan false bila IndexedDB tidak tersedia.
        async queue(form, summary = '') {
            if (!window.indexedDB) return false;
            const data = new FormData(form);
            const fields = [];
            data.forEach((value, name) => {
                if (name === '_token' || (value instanceof File && !value.size)) return;
                fields.push([name, value]);
            });
            await put({
                id: data.get('idempotency_key') || newKey(),
                user: window.__sgUserId,
                url: form.action,
                summary,
                fields,
                createdAt: Date.now(),
                error: null,
            });
            await this.refresh();
            return true;
        },

        async discard(id) {
            await remove(id);
            await this.refresh();
        },

        async flush() {
            if (this.sending || !navigator.onLine) return;
            await this.refresh();
            const pending = this.items.filter((i) => !i.error);
            if (!pending.length) return;

            this.sending = true;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            let sent = 0;
            for (const item of pending) {
                const body = new FormData();
                item.fields.forEach(([name, value]) => body.append(name, value));
                let response;
                try {
                    response = await fetch(item.url, { method: 'POST', body, headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf } });
                } catch (e) {
                    break; // jaringan putus lagi: coba nanti
                }
                if (response.ok) {
                    await remove(item.id);
                    sent++;
                } else if (response.status === 422) {
                    const json = await response.json().catch(() => ({}));
                    const first = Object.values(json.errors || {})[0]?.[0];
                    await put({ ...item, error: first || json.message || 'Data laporan tidak valid.' });
                } else {
                    break; // 419/401 (sesi habis) atau 5xx: biarkan di antrian
                }
            }
            this.sending = false;
            await this.refresh();

            if (sent) window.sgToast?.(`${sent} laporan dari antrian offline terkirim.`);
            if (this.failed.length) window.sgToast?.('Ada laporan offline yang gagal dikirim. Buka Buat Laporan untuk memeriksanya.', { tone: 'danger', delay: 8000 });
        },

        start() {
            this.flush();
            window.addEventListener('online', () => this.flush());
        },
    };
}

// ---------- Draf otomatis (teks saja) di localStorage ----------
const draftKey = () => `sg-report-draft:${window.__sgUserId ?? 'guest'}`;

export function loadDraft() {
    try {
        return JSON.parse(localStorage.getItem(draftKey()) || 'null');
    } catch (e) {
        return null;
    }
}

export function saveDraft(entries) {
    try {
        localStorage.setItem(draftKey(), JSON.stringify({ savedAt: Date.now(), entries }));
    } catch (e) {
        /* penyimpanan penuh / mode privat */
    }
}

export function clearDraft() {
    try {
        localStorage.removeItem(draftKey());
    } catch (e) {
        /* abaikan */
    }
}
