<?php
require 'config/database.php';

// Validate Product ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;

if ($id > 0) {
    $hasCategories = hasTable('categories');
    $hasEvents = hasTable('events');
    $hasEventColumn = hasProductEventColumn();

    $selectCategory = $hasCategories ? 'c.name AS category_name' : "NULL AS category_name";
    $selectEvent = ($hasEvents && $hasEventColumn) ? 'e.name AS event_name' : "NULL AS event_name";
    $joinCategory = $hasCategories ? 'LEFT JOIN categories c ON c.id = p.category_id' : '';
    $joinEvent = ($hasEvents && $hasEventColumn) ? 'LEFT JOIN events e ON e.slug = p.event_slug' : '';

    $whereParts = ['p.id = :id'];
    $params = [':id' => $id];

    // Keep behavior aligned with catalog/cart: only show product in active event context.
    if ($hasEventColumn) {
        $activeEventSlug = getActiveSaleEventSlug();
        if (empty($activeEventSlug)) {
            $whereParts[] = '1 = 0';
        } else {
            $whereParts[] = 'p.event_slug = :event_slug';
            $params[':event_slug'] = $activeEventSlug;
        }
    }

    $sql = "SELECT p.*, $selectCategory, $selectEvent
            FROM products p
            $joinCategory
            $joinEvent
            WHERE " . implode(' AND ', $whereParts) . "
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $product = $row;
    }
}

// Redirect if product not found
if (!$product) {
    redirect('category.php');
}

$eventNameMap = [
    'tet' => 'Tết',
    'gpmnam' => '30/4',
    'quockhanh' => '2/9',
    'noel' => 'Noel',
    'default' => 'Thường ngày',
];

$stock = (int)($product['stock'] ?? 0);
$stockLabel = $stock > 0 ? 'Còn hàng' : 'Hết hàng';
$stockBadgeClass = $stock > 0
    ? 'bg-success-subtle text-success border-success-subtle'
    : 'bg-danger-subtle text-danger border-danger-subtle';

$categoryLabel = trim((string)($product['category_name'] ?? ''));
if ($categoryLabel === '') {
    $categoryLabel = 'Chưa phân loại';
}

$eventLabel = trim((string)($product['event_name'] ?? ''));
if ($eventLabel === '') {
    $eventSlug = strtolower(trim((string)($product['event_slug'] ?? 'default')));
    $eventLabel = $eventNameMap[$eventSlug] ?? $eventSlug;
}

$productCode = 'SP-' . str_pad((string)$product['id'], 4, '0', STR_PAD_LEFT);

$pageTitle = $product['name'];
include 'includes/header.php';
?>

<div class="container py-5 fade-in-page">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb breadcrumb-luxury">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="category.php">Collections</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-5 align-items-center">
        <!-- Left Column: Product Image -->
        <div class="col-lg-6">
            <div class="detail-image-wrapper">
                <img src="assets/images/<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'placeholder.jpg' ?>"
                     class="detail-image"
                     alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
        </div>

        <!-- Right Column: Product Info -->
        <div class="col-lg-6">
            <div class="ps-lg-4">
                <h6 class="text-uppercase text-muted letter-spacing-2 mb-2">Bộ sưu tập sự kiện</h6>
                <h1 class="display-5 mb-3 font-heading"><?= htmlspecialchars($product['name']) ?></h1>

                <div class="d-flex align-items-center mb-4">
                    <span class="h3 mb-0 fw-bold" style="color: #8a5a00;"><?= formatVND($product['price']) ?></span>
                    <span class="badge ms-3 rounded-0 border text-uppercase <?= $stockBadgeClass ?>" style="letter-spacing: 1px;"><?= $stockLabel ?></span>
                </div>

                <p class="text-muted mb-4" style="line-height: 1.8;">
                    <?= !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'Thiết kế phù hợp cho các dịp lễ trong năm, dễ phối đồ và thích hợp làm quà lưu niệm.' ?>
                </p>

                <!-- Product Specs -->
                <div class="row mb-4 g-3">
                    <div class="col-6 col-sm-4">
                        <small class="text-uppercase text-muted d-block" style="font-size: 0.7rem; letter-spacing: 1px;">Danh mục</small>
                        <span class="fw-medium"><?= htmlspecialchars($categoryLabel) ?></span>
                    </div>
                    <div class="col-6 col-sm-4">
                        <small class="text-uppercase text-muted d-block" style="font-size: 0.7rem; letter-spacing: 1px;">Sự kiện</small>
                        <span class="fw-medium"><?= htmlspecialchars($eventLabel) ?></span>
                    </div>
                    <div class="col-6 col-sm-4">
                        <small class="text-uppercase text-muted d-block" style="font-size: 0.7rem; letter-spacing: 1px;">Mã / Tồn kho</small>
                        <span class="fw-medium"><?= htmlspecialchars($productCode) ?> / <?= $stock ?></span>
                    </div>
                </div>

                <hr class="my-4 opacity-25">

                <!-- Actions -->
                <div class="d-grid gap-3 d-md-flex">
                    <?php if (isLogin()): ?>
                        <button class="btn btn-luxury flex-grow-1 py-3" onclick="addToCart(<?= $product['id'] ?>)">
                            <i class="fa-solid fa-bag-shopping me-2"></i> Add to Cart
                        </button>
                        <a href="checkout.php?buy_now=1&product_id=<?= $product['id'] ?>" class="btn btn-outline-dark rounded-0 flex-grow-1 py-3 text-uppercase d-flex align-items-center justify-content-center" style="letter-spacing: 1px; text-decoration: none;">
                            Buy Now
                        </a>
                    <?php else: ?>
                        <a href="login.php?msg=cart" class="btn btn-luxury flex-grow-1 py-3">
                            <i class="fa-solid fa-lock me-2"></i> Login to Purchase
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Trust Indicators -->
                <div class="mt-4 pt-2 d-flex gap-4 text-muted small">
                    <span><i class="fa-solid fa-check me-2"></i>Authenticity Guaranteed</span>
                    <span><i class="fa-solid fa-truck-fast me-2"></i>Free Shipping</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function addToCart(productId) {
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', 1);

    fetch('api/cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            window.showToast('success', data.message);

            // Update Cart Count Badge
            const cartBadge = document.getElementById('cart-count');
            if (cartBadge) {
                cartBadge.innerText = data.cartCount;
                cartBadge.style.display = 'inline-block';
            }

            // Animate Cart Icon
            const cartIcon = document.querySelector('a[href="cart.php"].nav-icon');
            if (cartIcon) {
                cartIcon.classList.add('cart-bump');
                setTimeout(() => cartIcon.classList.remove('cart-bump'), 500);
            }
        } else if (data.status === 'auth_required') {
            window.location.href = 'login.php?msg=cart';
        } else {
            window.showToast('danger', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.showToast('danger', 'Có lỗi xảy ra, vui lòng thử lại.');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
