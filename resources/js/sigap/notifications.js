// Alpine store notifikasi (badge app bar/topbar + dropdown desktop).
// Endpoint dari layout: window.__sgRoutes = { notifList, notifRead, notifReadAll, notifClear, pending }
const ICONS = {
    job_approved: ['bi-check-circle', 'approved'],
    job_rejected: ['bi-x-circle', 'rejected'],
    pending_approval: ['bi-hourglass-split', 'pending'],
    new_comment: ['bi-chat-left-text', 'info'],
    welcome: ['bi-person-plus', 'info'],
};

export function timeAgo(value) {
    const seconds = Math.floor((Date.now() - new Date(value).getTime()) / 1000);
    if (seconds < 60) return 'baru saja';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes} menit lalu`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours} jam lalu`;
    const days = Math.floor(hours / 24);
    if (days < 7) return `${days} hari lalu`;
    return new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function targetUrl(n, routes) {
    if (n.daily_report_id) return `/daily-reports/${n.daily_report_id}${n.comment_id ? `#comment-${n.comment_id}` : ''}`;
    if (n.ticket_id) return `/tickets/${n.ticket_id}`;
    if (n.type === 'pending_approval') return routes.pending;
    return null;
}

export default function notificationStore() {
    const routes = window.__sgRoutes || {};
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const post = (url, body = {}) =>
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify(body),
        }).then((r) => r.json());

    return {
        items: [],
        unread: 0,
        loaded: false,

        get badge() {
            return this.unread > 99 ? '99+' : String(this.unread);
        },
        icon(n) {
            return (ICONS[n.type] || ['bi-bell', 'info'])[0];
        },
        tone(n) {
            return (ICONS[n.type] || ['bi-bell', 'info'])[1];
        },
        ago: timeAgo,

        async load() {
            if (!routes.notifList) return;
            try {
                const data = await fetch(routes.notifList, { headers: { Accept: 'application/json' } }).then((r) => r.json());
                this.items = data.notifications || [];
                this.unread = data.unread_count || 0;
            } catch (e) {
                // Jaringan lapangan sering putus; coba lagi di interval berikutnya.
            } finally {
                this.loaded = true;
            }
        },

        start() {
            this.load();
            setInterval(() => document.visibilityState === 'visible' && this.load(), 60000);
        },

        async open(n) {
            const url = targetUrl(n, routes);
            if (!n.is_read) {
                try {
                    const data = await post(routes.notifRead, { notification_id: n.id });
                    n.is_read = true;
                    if (data.unread_count !== undefined) this.unread = data.unread_count;
                } catch (e) {
                    /* tetap navigasi */
                }
            }
            if (url) window.location.href = url;
        },

        // Tandai satu notifikasi dibaca tanpa menahan navigasi (dipakai halaman Notifikasi)
        markRead(id) {
            fetch(routes.notifRead, {
                method: 'POST',
                keepalive: true,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ notification_id: id }),
            }).catch(() => {});
            this.unread = Math.max(0, this.unread - 1);
        },

        async markAll() {
            const data = await post(routes.notifReadAll);
            if (data.success) {
                this.items.forEach((n) => (n.is_read = true));
                this.unread = 0;
            }
        },

        async clearAll() {
            const data = await post(routes.notifClear);
            if (data.success) {
                this.items = [];
                this.unread = 0;
                window.sgToast?.('Semua notifikasi dihapus');
                window.dispatchEvent(new CustomEvent('sg-notifications-cleared'));
            }
        },
    };
}
