// Perilaku shell: ciutkan sidebar desktop & ganti tema. Keduanya disimpan di localStorage
// dan diterapkan sebelum render oleh resources/views/partials/theme-init.blade.php.
function store(key, value) {
    try {
        localStorage.setItem(key, value);
    } catch (e) {
        /* mode privat: abaikan */
    }
}

export function toggleSidebar() {
    const collapsed = document.documentElement.classList.toggle('sg-collapsed');
    store('sg-sidebar', collapsed ? 'collapsed' : 'expanded');
    return collapsed;
}

export function setTheme(mode) {
    store('sg-theme', mode);
    const dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
}

// Tabel lama (bukan .sg-table) tampil sebagai kartu di layar <576px: tiap sel diberi label kolom dari <thead>.
export function stackTables(root = document) {
    root.querySelectorAll('.sg-content .table-responsive > table:not(.sg-table)').forEach((table) => {
        const labels = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
        if (!labels.length) return;
        table.classList.add('sg-stack');
        table.querySelectorAll('tbody tr').forEach((row) => {
            Array.from(row.children).forEach((cell, i) => {
                if (!cell.hasAttribute('data-label')) cell.setAttribute('data-label', labels[i] || '');
            });
        });
    });
}

export function currentTheme() {
    try {
        return localStorage.getItem('sg-theme') || 'system';
    } catch (e) {
        return 'system';
    }
}
