// Grafik Chart.js yang mengikuti token tema. window.sgChart(canvas, (c) => config):
// config dibangun ulang dari warna token setiap kali tema berganti (data-bs-theme berubah).
export function chartColors() {
    const css = getComputedStyle(document.documentElement);
    const v = (name) => css.getPropertyValue(name).trim();
    return {
        primary: v('--sg-primary'),
        accent: v('--sg-accent'),
        text: v('--sg-text'),
        muted: v('--sg-muted'),
        border: v('--sg-border'),
        success: v('--sg-success-dot'),
        danger: v('--sg-danger-dot'),
        progress: v('--sg-progress-dot'),
        pending: v('--sg-pending-dot'),
        closed: v('--sg-closed-dot'),
        priority: [v('--sg-p1-fg'), v('--sg-p2-fg'), v('--sg-p3-fg'), v('--sg-p4-fg')],
        get palette() {
            return [this.primary, this.accent, this.progress, this.success, this.danger, this.pending, this.closed, this.muted];
        },
        // rgba transparan dari warna hex token
        alpha(hex, a) {
            const h = hex.replace('#', '');
            const n = parseInt(h.length === 3 ? h.split('').map((x) => x + x).join('') : h, 16);
            return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${a})`;
        },
    };
}

export function themedChart(canvas, build) {
    if (!canvas || !window.Chart) return null;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let chart = null;
    const render = () => {
        chart?.destroy();
        const c = chartColors();
        const config = build(c);
        config.options = {
            maintainAspectRatio: false,
            animation: reduce ? false : { duration: 200 },
            ...config.options,
        };
        window.Chart.defaults.color = c.muted;
        window.Chart.defaults.borderColor = c.border;
        chart = new window.Chart(canvas, config);
    };
    render();
    new MutationObserver(render).observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme'] });
    return chart;
}
