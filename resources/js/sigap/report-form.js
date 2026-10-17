// Alpine data form "Buat Laporan" (multi-entri). Dipakai resources/views/daily-reports/create.blade.php.
// Tiap entri memakai kunci tetap (reports[key][...]) — menghapus entri tidak menggeser lampiran entri lain.
// Teks disimpan otomatis sebagai draf (localStorage); saat offline, kirim = simpan ke antrian (lihat outbox.js).
import { loadDraft, saveDraft, clearDraft, newKey } from './outbox';

const BLANK = {
    job_name: '',
    report_date: '',
    due_date: '',
    job_site_id: '',
    section_id: '',
    job_pic: '',
    status: 'pending',
    description: '',
    remark: '',
    job_plan_id: '',
};

const TEXT_FIELDS = Object.keys(BLANK);

export default function sgReportForm({ entries = [], errors = {}, pics = [], today = '', defaultPic = '', idempotencyKey = '' } = {}) {
    let nextKey = 0;
    const make = (data = {}, key = null) => {
        const k = key ?? nextKey;
        nextKey = Math.max(nextKey, Number(k) + 1);
        return { ...BLANK, report_date: today, job_pic: defaultPic, ...data, key: Number(k) };
    };

    const initial = Object.keys(entries).length
        ? Object.entries(entries).map(([k, v]) => make(v, k))
        : [make()];

    // Buka entri pertama yang punya error, kalau tidak ada: entri pertama
    const firstError = Object.keys(errors)[0];
    const errorKey = firstError ? Number(firstError.split('.')[1]) : null;

    const fromServer = Object.keys(entries).length > 0;

    return {
        entries: initial,
        idempotencyKey: idempotencyKey || newKey(),
        draft: fromServer ? null : loadDraft(),
        saveTimer: null,
        errors,
        pics,
        open: errorKey ?? initial[0].key,
        picFor: null,
        picQuery: '',
        busy: false,

        init() {
            this.$watch('entries', () => {
                clearTimeout(this.saveTimer);
                this.saveTimer = setTimeout(() => this.saveDraft(), 800);
            });
        },

        // ---------- Draf ----------
        saveDraft() {
            const filled = this.entries.filter((e) => e.job_name || e.description || e.remark);
            if (!filled.length) return;
            saveDraft(this.entries.map((e) => Object.fromEntries(TEXT_FIELDS.map((f) => [f, e[f]]))));
            this.draft = null; // draf lama sudah tertimpa isi form ini
        },
        get draftLabel() {
            if (!this.draft) return '';
            const time = new Date(this.draft.savedAt).toLocaleString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
            return `${this.draft.entries.length} laporan · disimpan ${time}`;
        },
        restoreDraft() {
            this.entries = this.draft.entries.map((e) => make(e, nextKey));
            this.open = this.entries[0].key;
            this.draft = null;
        },
        discardDraft() {
            clearDraft();
            this.draft = null;
        },

        // ---------- Kirim ----------
        // Kirim lewat fetch; gagal jaringan (offline atau sinyal putus di tengah) → antrian offline.
        // Error validasi / sesi → kirim ulang sebagai form biasa supaya server menampilkan error seperti biasa.
        async submit(event) {
            event.preventDefault();
            const form = this.$root;
            if (!navigator.onLine) return this.queueOffline();

            this.busy = true;
            const body = new FormData(form);
            body.append('_flash', '1');
            let response;
            try {
                response = await fetch(form.action, { method: 'POST', body, headers: { Accept: 'application/json', 'X-CSRF-TOKEN': body.get('_token') } });
            } catch (e) {
                this.busy = false;
                return this.queueOffline();
            }
            if (response.ok) {
                clearDraft();
                const json = await response.json().catch(() => ({}));
                window.location.href = json.redirect || form.dataset.done;
                return;
            }
            HTMLFormElement.prototype.submit.call(form);
        },

        async queueOffline() {
            const summary = this.entries.map((e) => e.job_name || 'Tanpa nama').join(', ');
            const queued = await this.$store.outbox.queue(this.$root, summary);
            if (!queued) {
                window.sgToast?.('Perangkat offline dan browser tidak mendukung antrian. Coba lagi saat online.', { tone: 'danger' });
                return;
            }
            clearDraft();
            this.entries = [make()];
            this.open = this.entries[0].key;
            this.errors = {};
            this.idempotencyKey = newKey();
            window.scrollTo({ top: 0 });
            window.sgToast?.('Anda sedang offline. Laporan disimpan dan akan dikirim otomatis saat online.', { tone: 'info', delay: 7000 });
        },

        get count() {
            return this.entries.length;
        },
        name(entry, field) {
            return `reports[${entry.key}][${field}]`;
        },
        id(entry, field) {
            return `r${entry.key}-${field}`;
        },
        error(entry, field) {
            return (this.errors[`reports.${entry.key}.${field}`] || [])[0] || '';
        },
        hasErrors(entry) {
            return Object.keys(this.errors).some((k) => k.startsWith(`reports.${entry.key}.`));
        },
        number(entry) {
            return this.entries.indexOf(entry) + 1;
        },
        picName(id) {
            return (this.pics.find((p) => String(p.id) === String(id)) || {}).name || '';
        },
        complete(entry) {
            return entry.job_name && entry.report_date && entry.due_date && entry.job_pic && entry.description;
        },
        summary(entry) {
            if (this.hasErrors(entry)) return 'Perlu diperbaiki';
            if (!entry.job_name) return 'Nama pekerjaan belum diisi';
            if (!entry.description) return 'Deskripsi belum diisi';
            if (!entry.job_pic) return 'PIC belum dipilih';
            return 'Lengkap';
        },
        get filteredPics() {
            const q = this.picQuery.trim().toLowerCase();
            return q ? this.pics.filter((p) => p.name.toLowerCase().includes(q)) : this.pics;
        },

        add() {
            const last = this.entries[this.entries.length - 1];
            const entry = make({ job_site_id: last?.job_site_id || '', section_id: last?.section_id || '', job_pic: last?.job_pic || defaultPic });
            this.entries.push(entry);
            this.open = entry.key;
            this.$nextTick(() => document.getElementById(this.id(entry, 'job_name'))?.focus());
        },

        remove(entry) {
            const index = this.entries.indexOf(entry);
            if (index < 0 || this.entries.length === 1) return;
            this.entries.splice(index, 1);
            if (this.open === entry.key) this.open = this.entries[Math.max(0, index - 1)].key;
            window.sgToast?.(`Laporan ${index + 1} dihapus`, {
                tone: 'info',
                undo: () => {
                    this.entries.splice(index, 0, entry);
                    this.open = entry.key;
                },
            });
        },

        toggle(entry) {
            this.open = this.open === entry.key ? null : entry.key;
        },

        choosePic(entry) {
            this.picFor = entry;
            this.picQuery = '';
            window.bootstrap.Modal.getOrCreateInstance(document.getElementById('pic-sheet')).show();
        },

        selectPic(id) {
            if (this.picFor) this.picFor.job_pic = String(id);
            window.bootstrap.Modal.getOrCreateInstance(document.getElementById('pic-sheet')).hide();
        },
    };
}
