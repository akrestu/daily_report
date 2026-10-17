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

export function currentTheme() {
    try {
        return localStorage.getItem('sg-theme') || 'system';
    } catch (e) {
        return 'system';
    }
}
