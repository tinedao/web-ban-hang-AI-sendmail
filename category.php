<?php
require 'config/database.php';
$pageTitle = "Collections";
include 'includes/header.php';

// 1. Get Filter Parameters
$categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$sort = $_GET['sort'] ?? 'newest';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Pagination Logic
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
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
$categories = getData('categories');
?>

<div class="container py-5">
    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-5">Our Collections</h1>
        <p class="text-muted">Khám phá quần áo, phụ kiện và quà lưu niệm theo từng sự kiện trong năm.</p>
    </div>

    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-5">
            <div class="sidebar-wrapper sticky-top" style="top: 100px; z-index: 1;">
                <form id="filterForm">
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    
                    <!-- Sort By -->
                    <div class="filter-section">
                        <h5 class="filter-title">Sort By</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-input" type="radio" name="sort" id="sortNew" value="newest" <?= $sort == 'newest' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sortNew">Newest Arrivals</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-input" type="radio" name="sort" id="sortPriceLow" value="price_asc" <?= $sort == 'price_asc' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sortPriceLow">Price: Low to High</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-input" type="radio" name="sort" id="sortPriceHigh" value="price_desc" <?= $sort == 'price_desc' ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sortPriceHigh">Price: High to Low</label>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="filter-section">
                        <h5 class="filter-title">Category</h5>
                        <div class="form-check mb-2">
                            <input class="form-check-input filter-input" type="radio" name="category" id="catAll" value="" <?= !$categoryId ? 'checked' : '' ?>>
                            <label class="form-check-label" for="catAll">All Products</label>
                        </div>
                        <?php foreach ($categories as $cat): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input filter-input" type="radio" name="category" id="cat<?= $cat['id'] ?>" value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="cat<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Reset Button -->
                    <div class="mt-4">
                        <button type="button" class="btn btn-outline-dark btn-sm w-100 rounded-0 text-uppercase" onclick="resetFilters()" style="letter-spacing: 1px; font-size: 0.75rem;">Reset Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="col-lg-9">
            <div class="row g-4" id="product-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 col-sm-6">
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
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <h4 class="text-muted">No products found in this collection.</h4>
                        <button type="button" class="btn btn-luxury mt-3" onclick="resetFilters()">View All Products</button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-5" id="pagination-container">
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-luxury">
                        <!-- Prev -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $page - 1 ?>">Prev</a>
                        </li>

                        <!-- Numbers -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="#" data-page="<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next -->
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="#" data-page="<?= $page + 1 ?>">Next</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
function fetchProducts(page = 1) {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    params.append('page', page);

    // Update URL without reload
    const newUrl = window.location.pathname + '?' + params.toString();
    window.history.pushState({path: newUrl}, '', newUrl);

    // Show loading state (optional)
    document.getElementById('product-grid').style.opacity = '0.5';

    fetch('api/filter_products.php?' + params.toString())
        .then(response => response.json())
        .then(data => {
            document.getElementById('product-grid').innerHTML = data.grid;
            document.getElementById('pagination-container').innerHTML = data.pagination;
            document.getElementById('product-grid').style.opacity = '1';
            
            // Scroll to top of page smoothly
            window.scrollTo({ top: 0, behavior: 'smooth' });
        })
        .catch(error => console.error('Error:', error));
}

function resetFilters() {
    document.getElementById('catAll').checked = true;
    document.getElementById('sortNew').checked = true;
    fetchProducts(1);
}

document.addEventListener('DOMContentLoaded', function() {
    // Filter Inputs Change
    const inputs = document.querySelectorAll('.filter-input');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            fetchProducts(1); // Reset to page 1 on filter change
        });
    });

    // Pagination Click (Event Delegation)
    document.getElementById('pagination-container').addEventListener('click', function(e) {
        if (e.target.classList.contains('page-link')) {
            e.preventDefault();
            const page = e.target.getAttribute('data-page');
            if (page) {
                fetchProducts(page);
            }
        }
    });
});
</script>
