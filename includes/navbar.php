<?php
$current_page = basename($_SERVER['PHP_SELF']);
$themeSlug = $THEME['slug'] ?? 'default';

// Event themes use dark navbar, so use light logo. Default luxury uses black logo.
$preferredLogo = ($themeSlug === 'default') ? 'logo_black.png' : 'logo_light.png';
$logoPath = 'assets/images/' . $preferredLogo;
$absoluteLogoPath = __DIR__ . '/../assets/images/' . $preferredLogo;

if (!is_file($absoluteLogoPath)) {
    $logoPath = 'assets/images/logo.png';
}
?>
<nav class="navbar navbar-expand-lg sticky-top bg-white">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img src="<?= htmlspecialchars($logoPath) ?>" alt="<?= $_ENV['APP_NAME'] ?>">
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#luxuryNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="luxuryNav">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?= $current_page == 'index.php' ? 'active' : '' ?>" href="index.php">Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link <?= $current_page == 'category.php' ? 'active' : '' ?>" href="category.php">Sản phẩm</a></li>
                <li class="nav-item"><a class="nav-link <?= $current_page == 'about.php' ? 'active' : '' ?>" href="about.php">Giới thiệu</a></li>
                <li class="nav-item"><a class="nav-link <?= $current_page == 'contact.php' ? 'active' : '' ?>" href="contact.php">Liên hệ</a></li>
            </ul>

            <div class="d-flex align-items-center justify-content-end">
                <form action="category.php" method="GET" class="d-flex align-items-center">
                    <div class="search-input-wrapper" id="searchWrapper">
                        <input type="text" name="search" class="search-input" placeholder="Tìm sản phẩm...">
                    </div>
                    <a href="#" class="nav-icon" id="searchToggle"><i class="fa-solid fa-magnifying-glass"></i></a>
                </form>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a href="#" class="nav-icon dropdown-toggle user-name-display" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= htmlspecialchars($_SESSION['name'] ?? 'Client') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Tài khoản</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Đăng xuất</a></li>
                        </ul>
                    </div>
                    <a href="cart.php" class="nav-icon position-relative">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span id="cart-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem; <?= getCartCount() > 0 ? '' : 'display: none;' ?>"><?= getCartCount() ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="nav-icon" title="Đăng nhập"><i class="fa-regular fa-user"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<script>
    document.getElementById('searchToggle').addEventListener('click', function(e) {
        e.preventDefault();
        const wrapper = document.getElementById('searchWrapper');
        const input = wrapper.querySelector('input');
        wrapper.classList.toggle('active');
        if (wrapper.classList.contains('active')) {
            input.focus();
        }
    });
</script>
