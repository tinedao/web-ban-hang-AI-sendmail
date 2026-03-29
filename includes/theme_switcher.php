<?php
$currentThemeSlug = $THEME['slug'] ?? 'default';
$currentThemeName = $THEME['name'] ?? 'Luxury';
$currentThemeSource = $THEME_SOURCE ?? 'auto';
?>
<div class="theme-switcher" id="themeSwitcher">
    <button type="button" class="theme-switcher__btn" id="themeSwitcherBtn" title="Chon giao dien">
        Theme: <?= htmlspecialchars($currentThemeName) ?>
    </button>
    <div class="theme-switcher__menu" id="themeSwitcherMenu">
        <button type="button" data-theme="auto">Auto (theo ngay)</button>
        <button type="button" data-theme="default">Luxury</button>
        <button type="button" data-theme="tet">Tet</button>
        <button type="button" data-theme="gpmnam">30/4</button>
        <button type="button" data-theme="quockhanh">2/9</button>
        <button type="button" data-theme="noel">Noel</button>
    </div>
</div>

<style>
.theme-switcher {
    position: fixed;
    right: 12px;
    bottom: 12px;
    z-index: 1066;
}
.theme-switcher__btn {
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(17, 17, 17, 0.9);
    color: #fff;
    font-size: 12px;
    padding: 8px 10px;
    border-radius: 10px;
    min-width: 150px;
    text-align: left;
}
.theme-switcher__menu {
    margin-top: 6px;
    background: #111;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    padding: 6px;
    display: none;
    min-width: 150px;
}
.theme-switcher.open .theme-switcher__menu {
    display: block;
}
.theme-switcher__menu button {
    width: 100%;
    display: block;
    border: 0;
    background: transparent;
    color: #fff;
    text-align: left;
    padding: 6px 8px;
    border-radius: 6px;
    font-size: 12px;
}
.theme-switcher__menu button:hover {
    background: rgba(255, 255, 255, 0.12);
}
.theme-switcher__meta {
    color: #cfcfcf;
    font-size: 11px;
    padding: 6px 8px 2px;
}
</style>

<script>
(() => {
    const root = document.getElementById('themeSwitcher');
    if (!root) return;

    const btn = document.getElementById('themeSwitcherBtn');
    const menu = document.getElementById('themeSwitcherMenu');

    const meta = document.createElement('div');
    meta.className = 'theme-switcher__meta';
    meta.textContent = 'Current: <?= htmlspecialchars($currentThemeSlug) ?> | source: <?= htmlspecialchars($currentThemeSource) ?>';
    menu.appendChild(meta);

    btn.addEventListener('click', () => {
        root.classList.toggle('open');
    });

    document.addEventListener('click', (e) => {
        if (!root.contains(e.target)) root.classList.remove('open');
    });

    menu.querySelectorAll('button[data-theme]').forEach((item) => {
        item.addEventListener('click', () => {
            const url = new URL(window.location.href);
            const theme = item.getAttribute('data-theme');
            url.searchParams.set('theme', theme || 'auto');
            window.location.href = url.toString();
        });
    });
})();
</script>
