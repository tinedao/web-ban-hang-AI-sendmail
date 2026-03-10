<?php
require '../config/database.php';

$categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$sort = $_GET['sort'] ?? 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$limit = 9;

$catalog = getCatalogProducts([
    'category_id' => $categoryId,
    'sort' => $sort,
    'search' => $search,
    'page' => $page,
    'limit' => $limit
]);
$products = $catalog['products'];
$totalPages = $catalog['total_pages'];
$page = $catalog['page'];

// 1. Render Product Grid HTML
ob_start();
if (!empty($products)):
    foreach ($products as $product):
?>
    <div class="col-md-4 col-sm-6 fade-in-page">
        <div class="card product-card h-100">
            <div class="product-img-wrapper">
                <img src="assets/images/<?= !empty($product['image']) ? htmlspecialchars($product['image']) : 'placeholder.jpg' ?>" 
                     class="product-img" 
                     alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            <div class="card-body product-info">
                <span class="product-specs">Event Collection</span>
                <h5 class="product-title"><?= htmlspecialchars($product['name']) ?></h5>
                <p class="product-price"><?= formatVND($product['price']) ?></p>
                
                <div class="product-actions">
                    <a href="product.php?id=<?= $product['id'] ?>" class="btn-view-details">View Details</a>
                    <?php if (isLogin()): ?>
                        <a href="#" class="btn-view-more btn-add-cart" data-id="<?= $product['id'] ?>">Add to Cart</a>
                    <?php else: ?>
                        <a href="login.php?msg=cart" class="btn-view-more">Add to Cart</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php 
    endforeach;
else: 
?>
    <div class="col-12 text-center py-5 fade-in-page">
        <h4 class="text-muted">No products found in this collection.</h4>
        <button type="button" class="btn btn-luxury mt-3" onclick="resetFilters()">View All Products</button>
    </div>
<?php 
endif;
$gridHtml = ob_get_clean();

// 2. Render Pagination HTML
ob_start();
if ($totalPages > 1):
?>
<nav aria-label="Page navigation">
    <ul class="pagination pagination-luxury">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="#" data-page="<?= $page - 1 ?>">Prev</a>
        </li>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
            <a class="page-link" href="#" data-page="<?= $page + 1 ?>">Next</a>
        </li>
    </ul>
</nav>
<?php
endif;
$paginationHtml = ob_get_clean();

// Return JSON
header('Content-Type: application/json');
echo json_encode([
    'grid' => $gridHtml,
    'pagination' => $paginationHtml
]);
