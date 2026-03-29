<?php include 'header.php'; ?>
<?php
$totalProducts = getCount('products', ['skip_event_filter' => true]);
$totalOrders = getCount('orders');
?>

<h2 class="mb-4">Dashboard</h2>

<div class="row g-4">
    <!-- Thống kê Sản phẩm -->
    <div class="col-md-4">
        <div class="card card-luxury-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Sản phẩm</h6>
                        <h2 class="my-2"><?= $totalProducts ?></h2>
                    </div>
                    <i class="fa-solid fa-gem fa-2x opacity-25"></i>
                </div>
                <a href="products.php" class="text-decoration-none small" style="color: inherit; opacity: 0.7;">Xem chi tiết &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Thống kê Đơn hàng -->
    <div class="col-md-4">
        <div class="card card-luxury-gold h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Đơn hàng</h6>
                        <h2 class="my-2"><?= $totalOrders ?></h2>
                    </div>
                    <i class="fa-solid fa-cart-shopping fa-2x opacity-25"></i>
                </div>
                <a href="orders.php" class="text-white text-decoration-none small" style="opacity: 0.8;">Xem chi tiết &rarr;</a>
            </div>
        </div>
    </div>
</div>

</div> <!-- End main-content -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
