// Alpine data antrian persetujuan (daily-reports/pending).
// Satu-per-satu: form POST ke /daily-reports/{id}/approval. Massal: batch-approve / batch-reject (selected_reports[]).
export default function sgQueue({ reports = [], approvalUrl = '' } = {}) {
    return {
        reports,
        selected: [],
        selecting: false,
        active: reports[0]?.id ?? null,
        filter: 'all',
        rejectIds: [],

        get visible() {
            return this.reports.filter((r) => {
                if (this.filter === 'old') return r.hours >= 24;
                if (this.filter.startsWith('L')) return r.level === Number(this.filter.slice(1));
                return true;
            });
        },
        get current() {
            return this.reports.find((r) => r.id === this.active) || null;
        },
        get allSelected() {
            return this.visible.length > 0 && this.visible.every((r) => this.selected.includes(r.id));
        },
        isSelected(id) {
            return this.selected.includes(id);
        },
        toggle(id) {
            this.selected = this.isSelected(id) ? this.selected.filter((x) => x !== id) : [...this.selected, id];
        },
        toggleAll() {
            const ids = this.visible.map((r) => r.id);
            this.selected = this.allSelected ? this.selected.filter((id) => !ids.includes(id)) : [...new Set([...this.selected, ...ids])];
        },
        stopSelecting() {
            this.selecting = false;
            this.selected = [];
        },
        open(report) {
            if (this.selecting) return this.toggle(report.id);
            this.active = report.id;
        },

        // Setujui satu laporan lewat form approval biasa (reload halaman, flash dari server)
        approve(id) {
            const form = this.$refs.single;
            form.action = approvalUrl.replace('__ID__', id);
            form.querySelector('[name=status]').value = 'approved';
            form.submit();
        },

        askReject(ids) {
            this.rejectIds = ids;
            window.bootstrap.Modal.getOrCreateInstance(document.getElementById('queue-reject')).show();
        },

        // Navigasi keyboard desktop: J/K pindah, X pilih, A setujui, R tolak
        key(event) {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName) || document.querySelector('.modal.show')) return;
            const list = this.visible;
            const index = list.findIndex((r) => r.id === this.active);
            const k = event.key.toLowerCase();
            if (k === 'j' && index < list.length - 1) this.active = list[index + 1].id;
            else if (k === 'k' && index > 0) this.active = list[index - 1].id;
            else if (k === 'x' && this.active) this.toggle(this.active);
            else if (k === 'a' && this.active) this.approve(this.active);
            else if (k === 'r' && this.active) this.askReject([this.active]);
            else return;
            event.preventDefault();
        },
    };
}
