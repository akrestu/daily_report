// Alpine data form "Buat Laporan" (multi-entri). Dipakai resources/views/daily-reports/create.blade.php.
// Tiap entri memakai kunci tetap (reports[key][...]) — menghapus entri tidak menggeser lampiran entri lain.
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

export default function sgReportForm({ entries = [], errors = {}, pics = [], today = '', defaultPic = '' } = {}) {
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

    return {
        entries: initial,
        errors,
        pics,
        open: errorKey ?? initial[0].key,
        picFor: null,
        picQuery: '',
        busy: false,

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
