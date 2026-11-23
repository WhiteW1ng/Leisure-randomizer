{{-- Кнопка светлая/тёмная тема. Всегда в правом нижнем углу, на вёрстку не влияет. --}}
<button type="button" id="theme-toggle" aria-label="Сменить тему оформления" title="Светлая / тёмная тема"
        style="position:fixed;right:16px;bottom:16px;z-index:60;width:40px;height:40px;border-radius:9999px;cursor:pointer;font-size:18px;line-height:1;background:rgb(var(--c-card));color:rgb(var(--c-ink));border:1px solid rgb(var(--c-line));box-shadow:0 4px 16px rgba(0,0,0,.12);">
    ◐
</button>
<script nonce="{{ request()->attributes->get('csp_nonce') }}">
    document.getElementById('theme-toggle').addEventListener('click', function () {
        var dark = document.documentElement.classList.toggle('dark');
        try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}
    });
</script>
