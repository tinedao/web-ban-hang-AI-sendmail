<?php
require_once __DIR__ . '/theme.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $_ENV['APP_NAME'] ?><?= isset($pageTitle) ? ' - ' . $pageTitle : '' ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo_black.png">
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <?php if (!empty($THEME['css'])): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($THEME['css']) ?>">
    <?php endif; ?>
</head>
<body class="<?= 'theme-' . htmlspecialchars($THEME['slug']) ?>">

<?php include __DIR__ . '/navbar.php'; ?>
<?php
$vnNow = new DateTime('now', new DateTimeZone('Asia/Ho_Chi_Minh'));
$themeSource = $THEME_SOURCE ?? 'auto';
?>
<div class="theme-debug-corner" title="Theme debug">
    <div><strong>VN:</strong> <?= $vnNow->format('d/m/Y H:i') ?></div>
    <div><strong>Theme:</strong> <?= htmlspecialchars($THEME['name']) ?> (<?= htmlspecialchars($THEME['slug']) ?>)</div>
    <div><strong>Source:</strong> <?= htmlspecialchars($themeSource) ?></div>
</div>
<?php if (!empty($THEME['particles'])): ?>
    <div id="theme-fall-layer" class="theme-fall-layer" aria-hidden="true"></div>
    <script>
        (() => {
            const layer = document.getElementById('theme-fall-layer');
            if (!layer) return;
            const symbols = <?= json_encode($THEME['particles'], JSON_UNESCAPED_UNICODE) ?>;
            const count = <?= (int)($THEME['particle_count'] ?? 0) ?>;
            const mode = <?= json_encode($THEME['particle_mode'] ?? 'emoji') ?>;
            for (let i = 0; i < count; i++) {
                const item = document.createElement('span');
                item.className = mode === 'snow' ? 'theme-particle theme-particle--snow' : 'theme-particle';
                if (mode !== 'snow') {
                    item.textContent = symbols[Math.floor(Math.random() * symbols.length)] || '*';
                    item.style.fontSize = (16 + Math.random() * 16).toFixed(0) + 'px';
                } else {
                    const size = 6 + Math.random() * 14;
                    item.style.width = size.toFixed(1) + 'px';
                    item.style.height = size.toFixed(1) + 'px';
                    item.style.opacity = (0.65 + Math.random() * 0.35).toFixed(2);
                }
                item.style.left = Math.random() * 100 + 'vw';
                item.style.animationDuration = (mode === 'snow' ? (9 + Math.random() * 9) : (7 + Math.random() * 8)).toFixed(2) + 's';
                item.style.animationDelay = (Math.random() * 5).toFixed(2) + 's';
                layer.appendChild(item);
            }
        })();
    </script>
<?php endif; ?>

<!-- Main Content Wrapper -->
<div id="app-content">

