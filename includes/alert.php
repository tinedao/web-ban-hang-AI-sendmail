<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Xử lý Session Flash Messages (Dùng cho Redirect: Thêm/Sửa/Xóa xong chuyển trang)
if (isset($_SESSION['flash_message'])) {
    $msg = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
    
    $type = $msg['type'] ?? 'info';
    $text = $msg['message'] ?? '';
    
    // Map type sang class Bootstrap
    $alertClass = match($type) {
        'error' => 'danger',
        'success' => 'success',
        'warning' => 'warning',
        default => 'info'
    };
    
    $icon = match($type) {
        'success' => 'fa-check-circle',
        'error' => 'fa-circle-exclamation',
        'warning' => 'fa-triangle-exclamation',
        default => 'fa-circle-info'
    };

    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.showToast) window.showToast('{$alertClass}', " . json_encode($text) . ");
            });
          </script>";
}

// 2. Xử lý Biến cục bộ (Dùng cho Login/Register khi submit form tại chỗ)
$localAlerts = [
    'danger' => $error ?? null,
    'success' => $success ?? null,
    'info' => $infoMessage ?? null
];

foreach ($localAlerts as $type => $message) {
    if (!empty($message)) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (window.showToast) window.showToast('{$type}', " . json_encode($message) . ");
                });
              </script>";
    }
}
?>