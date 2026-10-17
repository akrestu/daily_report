// Pencarian global (Ctrl K / tombol cari di app bar). Endpoint: GET /search?q= → { groups: [{ label, items: [{ title, meta, url, icon }] }] }.
export default function sgSearch({ url = '' } = {}) {
    return {
        q: '',
        groups: [],
        loading: false,
        searched: false,
        cursor: 0,
        timer: null,
        controller: null,

        get flat() {
            return this.groups.flatMap((g) => g.items);
        },
        index(item) {
            return this.flat.indexOf(item);
        },

        init() {
            window.addEventListener('keydown', (event) => {
                if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                    event.preventDefault();
                    this.show();
                }
            });
            this.$el.addEventListener('shown.bs.modal', () => this.$refs.input.focus());
        },
        show() {
            window.bootstrap.Modal.getOrCreateInstance(this.$el).show();
        },
        input() {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.fetch(), 250);
        },
        async fetch() {
            const q = this.q.trim();
            this.controller?.abort();
            if (q.length < 2) {
                this.groups = [];
                this.searched = false;
                return;
            }
            this.controller = new AbortController();
            this.loading = true;
            try {
                const response = await fetch(`${url}?q=${encodeURIComponent(q)}`, {
                    headers: { Accept: 'application/json' },
                    signal: this.controller.signal,
                });
                const data = await response.json();
                this.groups = data.groups || [];
                this.cursor = 0;
                this.searched = true;
            } catch (e) {
                if (e.name !== 'AbortError') this.groups = [];
            } finally {
                this.loading = false;
            }
        },
        move(step) {
            if (!this.flat.length) return;
            this.cursor = (this.cursor + step + this.flat.length) % this.flat.length;
            this.$nextTick(() => this.$el.querySelector('.sg-search-item.is-active')?.scrollIntoView({ block: 'nearest' }));
        },
        go() {
            const item = this.flat[this.cursor];
            if (item) window.location.href = item.url;
        },
    };
}
