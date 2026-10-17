// Alpine data antrian persetujuan (daily-reports/pending).
// Satu-per-satu: form POST ke /daily-reports/{id}/approval. Massal: batch-approve / batch-reject (selected_reports[]).
// Mobile: geser kanan = setujui (request ditunda 5 dtk, bisa diurungkan), geser kiri = sheet tolak,
// tekan lama = masuk mode pilih banyak.
const SWIPE_TRIGGER = 96; // px
const UNDO_DELAY = 5000; // ms
const LONG_PRESS = 500; // ms
const idleDrag = () => ({ id: null, x: 0, y: 0, dx: 0, axis: null, press: null, longPressed: false });

export default function sgQueue({ reports = [], approvalUrl = '' } = {}) {
    return {
        reports,
        selected: [],
        selecting: false,
        active: reports[0]?.id ?? null,
        filter: 'all',
        rejectIds: [],
        hidden: [], // id yang menunggu dikirim (geser setujui)
        timers: {},
        drag: idleDrag(),
        suppressClick: false,

        get visible() {
            return this.reports.filter((r) => {
                if (this.hidden.includes(r.id)) return false;
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
            if (this.suppressClick) {
                this.suppressClick = false;
                return;
            }
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

        // ---------- Geser & tekan lama (sentuh, <992px) ----------
        offset(id) {
            return this.drag.id === id && this.drag.axis === 'x' ? this.drag.dx : 0;
        },
        cardStyle(id) {
            const dx = this.offset(id);
            return dx ? `transform: translateX(${dx}px); transition: none` : '';
        },
        swipeHint(id) {
            const dx = this.offset(id);
            if (dx > 24) return dx >= SWIPE_TRIGGER ? 'approve is-ready' : 'approve';
            if (dx < -24) return dx <= -SWIPE_TRIGGER ? 'reject is-ready' : 'reject';
            return '';
        },
        pointerDown(event, report) {
            if (window.innerWidth >= 992 || event.pointerType === 'mouse' || event.target.closest('a, input, .sg-queue-card__actions')) return;
            clearTimeout(this.drag.press);
            this.drag = { ...idleDrag(), id: report.id, x: event.clientX, y: event.clientY };
            this.drag.press = setTimeout(() => {
                if (this.drag.axis) return;
                this.drag.longPressed = true;
                this.suppressClick = true;
                this.selecting = true;
                if (!this.isSelected(report.id)) this.toggle(report.id);
                navigator.vibrate?.(15);
            }, LONG_PRESS);
        },
        pointerMove(event) {
            if (this.drag.id === null || this.drag.longPressed) return;
            const dx = event.clientX - this.drag.x;
            const dy = event.clientY - this.drag.y;
            if (!this.drag.axis && (Math.abs(dx) > 8 || Math.abs(dy) > 8)) {
                clearTimeout(this.drag.press);
                this.drag.axis = Math.abs(dx) > Math.abs(dy) && !this.selecting ? 'x' : 'y';
            }
            if (this.drag.axis === 'x') this.drag.dx = Math.max(-160, Math.min(160, dx));
        },
        pointerUp() {
            clearTimeout(this.drag.press);
            const { id, dx, axis } = this.drag;
            this.drag = idleDrag();
            if (axis !== 'x') return;
            this.suppressClick = true;
            setTimeout(() => (this.suppressClick = false), 50);
            if (dx >= SWIPE_TRIGGER) this.approveLater(id);
            else if (dx <= -SWIPE_TRIGGER) this.askReject([id]);
        },

        // Kartu disembunyikan, request dikirim setelah 5 dtk kecuali diurungkan (tidak ada rollback server)
        approveLater(id) {
            const report = this.reports.find((r) => r.id === id);
            this.hidden = [...this.hidden, id];
            this.timers[id] = setTimeout(() => this.send(id), UNDO_DELAY);
            window.sgToast(`Disetujui: ${report?.title ?? 'laporan'}`, { delay: UNDO_DELAY, undo: () => this.undo(id) });
        },
        undo(id) {
            clearTimeout(this.timers[id]);
            delete this.timers[id];
            this.hidden = this.hidden.filter((x) => x !== id);
        },
        async send(id, keepalive = false) {
            delete this.timers[id];
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            try {
                const response = await fetch(approvalUrl.replace('__ID__', id), {
                    method: 'POST',
                    keepalive,
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ status: 'approved' }),
                });
                if (!response.ok) throw new Error(String(response.status));
                this.reports = this.reports.filter((r) => r.id !== id);
                this.selected = this.selected.filter((x) => x !== id);
                this.hidden = this.hidden.filter((x) => x !== id);
            } catch (e) {
                this.hidden = this.hidden.filter((x) => x !== id);
                window.sgToast('Gagal menyetujui laporan. Coba lagi.', { tone: 'danger' });
            }
        },
        // Halaman ditinggal sebelum 5 dtk: kirim sekarang supaya persetujuan tidak hilang
        flush() {
            Object.keys(this.timers).forEach((id) => {
                clearTimeout(this.timers[id]);
                this.send(Number(id), true);
            });
        },

        // Navigasi keyboard desktop: J/K pindah, X pilih, A setujui, R tolak
        key(event) {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName) || event.ctrlKey || event.metaKey || document.querySelector('.modal.show')) return;
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
