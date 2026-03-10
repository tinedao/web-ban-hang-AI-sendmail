<?php
include '../config/database.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    // Ho tro ca key cu va key moi trong .env
    $envUser = trim((string)($_ENV['ADMIN_USERNAME'] ?? $_ENV['Ad_username'] ?? 'admin'));
    $envPass = (string)($_ENV['ADMIN_PASSWORD'] ?? $_ENV['Ad_password'] ?? 'admin');

    if ($username === $envUser && $password === $envPass) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = $envUser;
        header("Location: index.php");
        exit;
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng.";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/admin.css" rel="stylesheet">
</head>
<body class="admin-login-body d-flex align-items-center justify-content-center vh-100">
    <div class="card login-card p-4 shadow-lg" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold">ADMIN CP</h3>
            <p class="text-muted small text-uppercase ls-1">Management System</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small text-uppercase">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small text-uppercase">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-2">Đăng nhập</button>
        </form>
        <div class="text-center mt-3">
            <a href="../index.php" class="text-decoration-none text-muted small hover-accent">&larr; Về trang chủ</a>
        </div>
    </div>
</body>
</html>
