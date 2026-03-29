<?php
require 'config/database.php';
$pageTitle = "Order Confirmed";

// Validate Order ID
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId > 0 && isLogin()) {
    // Verify order belongs to user
    $orders = getData('orders', ['where' => ['id' => $orderId, 'user_id' => $_SESSION['user_id']]]);
    if (empty($orders)) {
        redirect('index.php');
    }
    $order = $orders[0];
} else {
    redirect('index.php');
}

include 'includes/header.php';
?>

<div class="container py-5 fade-in-page">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-check fs-2"></i>
                </div>
            </div>
            
            <h1 class="font-heading mb-3">Cảm ơn!</h1>
            <p class="lead text-muted mb-4">Đơn hàng của bạn đã được đặt thành công.</p>
            
            <div class="card border-0 bg-light p-4 mb-4">
                <p class="mb-2">Mã số đơn hàng: <strong class="text-dark">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></p>
                <p class="mb-2">Tổng số tiền: <strong><?= formatVND($order['total']) ?></strong></p>
                <p class="mb-0">Trạng thái thanh toán: 
                    <span class="badge <?= $order['status'] === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= strtoupper($order['status']) ?>
                    </span>
                </p>
            </div>

            <div class="d-flex justify-content-center gap-3">
                <a href="profile.php?tab=orders" class="btn btn-outline-dark" style="display: inline-flex; align-items: center; justify-content: center; text-align: center;">Xem lịch sử đơn hàng</a>
                <a href="category.php" class="btn btn-luxury">Tiếp tục mua sắm</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
