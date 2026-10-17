// Alpine data komentar laporan (detail laporan). Endpoint lama JobCommentController:
// GET/POST /daily-reports/{id}/comments, DELETE /comments/{id}.
// Isi komentar dirender dengan x-text (bukan innerHTML) agar aman dari XSS.
export default function sgComments({ reportId }) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const request = (url, options = {}) =>
        fetch(url, {
            ...options,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, ...(options.headers || {}) },
        }).then((r) => {
            if (!r.ok) throw new Error(r.status);
            return r.json();
        });

    return {
        items: [],
        loaded: false,
        failed: false,
        text: '',
        sending: false,

        initials(name) {
            return (name || '?').split(' ').filter(Boolean).slice(0, 2).map((w) => w[0].toUpperCase()).join('');
        },

        async load() {
            this.failed = false;
            try {
                const data = await request(`/daily-reports/${reportId}/comments`);
                this.items = data.comments || [];
            } catch (e) {
                this.failed = true;
            } finally {
                this.loaded = true;
            }
        },

        async send() {
            const comment = this.text.trim();
            if (!comment || this.sending) return;
            this.sending = true;
            try {
                const body = new FormData();
                body.append('comment', comment);
                const data = await request(`/daily-reports/${reportId}/comments`, { method: 'POST', body });
                if (data.comment) this.items.unshift(data.comment);
                this.text = '';
            } catch (e) {
                window.sgToast?.('Komentar gagal dikirim. Coba lagi.', { tone: 'danger' });
            } finally {
                this.sending = false;
            }
        },

        async destroy(comment) {
            try {
                const body = new FormData();
                body.append('_method', 'DELETE');
                await request(`/comments/${comment.id}`, { method: 'POST', body });
                this.items = this.items.filter((c) => c.id !== comment.id);
                window.sgToast?.('Komentar dihapus');
            } catch (e) {
                window.sgToast?.('Komentar gagal dihapus.', { tone: 'danger' });
            }
        },
    };
}
