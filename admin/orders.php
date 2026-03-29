<?php include 'header.php'; ?>

<?php
// Cập nhật trạng thái
if (isset($_POST['update_status'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = (string)($_POST['status'] ?? 'pending');
    $allowedStatuses = ['pending', 'paid', 'shipped', 'cancelled'];
    if (!in_array($status, $allowedStatuses, true)) {
        $status = 'pending';
    }
    if ($orderId > 0) {
        updateData('orders', ['status' => $status], ['id' => $orderId]);
    }
    echo "<script>window.location.href='orders.php';</script>";
    exit;
}

$sql = "SELECT o.*, u.name AS user_name, u.phone AS user_phone
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="mb-4">Quản lý Đơn hàng</h2>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td>#<?= $o['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($o['name'] ?: ($o['user_name'] ?? 'Khách vãng lai')) ?>
                        <div class="text-muted small">UID: <?= (int)($o['user_id'] ?? 0) ?></div>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td class="fw-bold"><?= formatVND($o['total']) ?></td>
                    <td>
                        <?php if ($o['status'] == 'paid'): ?>
                            <span class="badge bg-success">Đã thanh toán</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Chờ xử lý</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" class="d-flex gap-2">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="status" class="form-select form-select-sm" style="width: 120px;">
                                <option value="pending" <?= $o['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= $o['status'] == 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="shipped" <?= $o['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                <option value="cancelled" <?= $o['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-primary">Lưu</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
