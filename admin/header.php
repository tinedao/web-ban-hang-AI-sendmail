<?php
require '../config/database.php';

// Check Admin Auth
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin/login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo_black.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body>

<div class="sidebar d-flex flex-column">
    <div class="sidebar-brand">
        <a href="index.php" class="text-white text-decoration-none">
            <h4>ADMIN Crowné</h4>
            <?php if (isset($_SESSION['admin_name'])): ?>
                <small class="d-block text-white-50 mt-1" style="font-size: 0.75rem;">Hello, <?= htmlspecialchars($_SESSION['admin_name']) ?></small>
            <?php endif; ?>
        </a>
    </div>
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge"></i> <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="products.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-gem"></i> <span>Sản phẩm</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="categories.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-list"></i> <span>Danh mục</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="orders.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-cart-shopping"></i> <span>Đơn hàng</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="chat.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'chat.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-comments"></i> <span>Chat Khách</span>
            </a>
        </li>
    </ul>
    <div class="p-3 logout-link">
        <a href="../logout.php?type=admin" class="nav-link text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Đăng xuất</a>
    </div>
</div>

<div class="main-content">
