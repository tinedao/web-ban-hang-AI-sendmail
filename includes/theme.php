<?php
// includes/theme.php
date_default_timezone_set('Asia/Ho_Chi_Minh');

function getThemeBySlug(string $slug): array {
    $themes = [
        'tet' => [
            'slug' => 'tet',
            'name' => 'Tet Am Lich',
            'css' => 'assets/css/themes/tet.css',
            'hero' => 'assets/img/events/tet/hero.png',
            'badge' => 'TET',
            'particles' => ['🌸', '🧧'],
            'particle_count' => 20,
        ],
        'gpmnam' => [
            'slug' => 'gpmnam',
            'name' => '30/4',
            'css' => 'assets/css/themes/gpmnam.css',
            'hero' => 'assets/img/events/gpmnam/hero.png',
            'badge' => '30/4',
            'particles' => ['🇻🇳'],
            'particle_count' => 16,
        ],
        'quockhanh' => [
            'slug' => 'quockhanh',
            'name' => 'Quoc Khanh 2/9',
            'css' => 'assets/css/themes/quockhanh.css',
            'hero' => 'assets/img/events/quockhanh/hero.png',
            'badge' => '2/9',
            'particles' => ['🇻🇳'],
            'particle_count' => 18,
        ],
        'noel' => [
            'slug' => 'noel',
            'name' => 'Noel',
            'css' => 'assets/css/themes/noel.css',
            'hero' => 'assets/img/events/noel/hero.png',
            'badge' => 'NOEL',
            'particles' => ['❄', '❄️'],
            'particle_count' => 36,
            'particle_mode' => 'snow',
        ],
        'default' => [
            'slug' => 'default',
            'name' => 'Luxury',
            'css' => null,
            'hero' => null,
            'badge' => null,
            'particles' => [],
            'particle_count' => 0,
            'particle_mode' => 'emoji',
        ],
    ];

    return $themes[$slug] ?? $themes['default'];
}

function getThemeToday(?string $forceTheme = null): array {
    $allowed = ['tet', 'gpmnam', 'quockhanh', 'noel', 'default'];
    if ($forceTheme !== null) {
        $forceTheme = strtolower(trim($forceTheme));
        if (in_array($forceTheme, $allowed, true)) {
            return getThemeBySlug($forceTheme);
        }
    }

    // Auto chia deu theo quy:
    // Thang 01-03: Tet, 04-06: 30/4, 07-09: 2/9, 10-12: Noel.
    $month = (int)date('n');
    if ($month >= 1 && $month <= 3) {
        return getThemeBySlug('tet');
    }
    if ($month >= 4 && $month <= 6) {
        return getThemeBySlug('gpmnam');
    }
    if ($month >= 7 && $month <= 9) {
        return getThemeBySlug('quockhanh');
    }

    return getThemeBySlug('noel');
}

$THEME_SOURCE = 'auto';
$forcedTheme = null;

// UI override via query, then persist to session.
if (isset($_GET['theme'])) {
    $requested = strtolower(trim((string)$_GET['theme']));
    $allowed = ['tet', 'gpmnam', 'quockhanh', 'noel', 'default'];

    if ($requested === 'auto' || $requested === '') {
        unset($_SESSION['theme_selected']);
        $THEME_SOURCE = 'auto';
    } elseif (in_array($requested, $allowed, true)) {
        $_SESSION['theme_selected'] = $requested;
        $forcedTheme = $requested;
        $THEME_SOURCE = 'ui';
    }
} elseif (!empty($_SESSION['theme_selected'])) {
    $forcedTheme = (string)$_SESSION['theme_selected'];
    $THEME_SOURCE = 'ui';
}

if ($forcedTheme !== null) {
    $allowed = ['tet', 'gpmnam', 'quockhanh', 'noel', 'default'];
    if (!in_array($forcedTheme, $allowed, true)) {
        $forcedTheme = null;
        unset($_SESSION['theme_selected']);
        $THEME_SOURCE = 'auto';
    }
}

$THEME = getThemeToday($forcedTheme);
