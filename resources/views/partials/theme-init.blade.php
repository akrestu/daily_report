{{-- Terapkan tema sebelum render agar tidak berkedip. Preferensi di localStorage('sg-theme'): light | dark | system --}}
<script>
    (function () {
        var pref = 'system';
        try { pref = localStorage.getItem('sg-theme') || 'system'; } catch (e) {}
        var dark = pref === 'dark' || (pref === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        try { if (localStorage.getItem('sg-sidebar') === 'collapsed') document.documentElement.classList.add('sg-collapsed'); } catch (e) {}
    })();
</script>
