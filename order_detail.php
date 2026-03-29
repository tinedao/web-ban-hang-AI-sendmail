<?php
require 'config/database.php';
require_once 'includes/invoice_template.php';

// Auth Guard
if (!isLogin()) {
    redirect('login.php');
}

$pageTitle = "Chi tiết đơn hàng";
include 'includes/header.php';

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

echo "<div class='container py-5 fade-in-page'>";

if ($orderId > 0) {
    // Lấy đơn hàng và kiểm tra quyền sở hữu (chỉ xem được đơn của chính mình)
    $orders = getData('orders', ['where' => ['id' => $orderId, 'user_id' => $_SESSION['user_id']]]);
    
    if (!empty($orders)) {
        $order = $orders[0];
        
        // Xử lý fallback cho các đơn hàng cũ (chưa có thông tin lưu trong bảng orders)
        if (empty($order['name'])) {
             $users = getData('users', ['where' => ['id' => $order['user_id']], 'limit' => 1]);
             $user = $users[0] ?? [];
             $order['name'] = $user['name'] ?? 'Khách hàng thân mến';
             $order['phone'] = $user['phone'] ?? 'Chưa có';
             $order['address'] = 'Chưa lưu địa chỉ';
        }

        // Chuẩn bị dữ liệu cho Template
        $orderData = [
            'name' => $order['name'],
            'phone' => $order['phone'],
            'address' => $order['address'],
            'created_at' => $order['created_at'],
            'payment_method' => $order['payment_method'] ?? 'COD',
            'total' => $order['total']
        ];
        
        // Lấy danh sách sản phẩm
        $items = getData('order_items', ['where' => ['order_id' => $orderId]]);
        
        // Hiển thị hóa đơn
        // Tham số thứ 4 là false để chỉ lấy nội dung div, không lấy full html
        echo getLuxuryInvoiceHTML($orderId, $orderData, $items, false);

        // Thêm nút hành động
        echo "
        <div class='text-center mt-4 d-print-none'>
            <button onclick='window.print()' class='btn btn-luxury me-2'><i class='fa-solid fa-print me-2'></i>In hóa đơn</button>
            <a href='profile.php?tab=orders' class='btn btn-outline-dark'>Quay lại lịch sử đơn hàng</a>
        </div>";
        
    } else {
        echo "<div class='text-center py-5'>
                <i class='fa-solid fa-circle-exclamation fs-1 text-muted mb-3'></i>
                <h2>Không tìm thấy đơn hàng</h2>
                <p class='text-muted'>Bạn không có quyền xem đơn hàng này.</p>
                <a href='index.php' class='btn btn-luxury mt-3'>Quay về trang chủ</a>
              </div>";
    }
} else {
    redirect('index.php');
}

echo "</div>";
include 'includes/footer.php';
?>
