{{-- Ставит тёмную тему до отрисовки страницы (чтобы не мигало). Выбор хранится в браузере. --}}
<script>
    (function () {
        try {
            var t = localStorage.getItem('theme');
            if (!t) { t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'; }
            document.documentElement.classList.toggle('dark', t === 'dark');
        } catch (e) {}
    })();
</script>
