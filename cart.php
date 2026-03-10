<?php
require 'config/database.php';
$pageTitle = "Shopping Cart";

// Auth Guard
if (!isLogin()) {
    redirect('login.php?msg=cart');
}

// Fetch Cart Items
$cartSnapshot = getCartSnapshot();
$cartItems = $cartSnapshot['items'];
$totalPrice = $cartSnapshot['total'];

include 'includes/header.php';
?>

<div class="container py-5 fade-in-page">
    <div class="text-center mb-5">
        <h1 class="font-heading display-5">Your Shopping Bag</h1>
        <p class="text-muted">Review your selected items</p>
    </div>

    <?php include 'includes/alert.php'; ?>

    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-bag-shopping fs-1 text-muted opacity-25 mb-3"></i>
            <h4 class="text-muted">Your bag is currently empty.</h4>
            <a href="category.php" class="btn btn-luxury mt-4">Continue Shopping</a>
        </div>
    <?php else: ?>
        <div class="row g-5">
            <!-- Cart Items List -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="d-flex align-items-center p-4 border-bottom position-relative">
                                <!-- Image -->
                                <div class="flex-shrink-0" style="width: 80px; height: 80px;">
                                    <img src="assets/images/<?= !empty($item['image']) ? htmlspecialchars($item['image']) : 'placeholder.jpg' ?>" 
                                         class="img-fluid w-100 h-100 object-fit-contain" alt="<?= htmlspecialchars($item['name']) ?>">
                                </div>
                                
                                <!-- Info -->
                                <div class="flex-grow-1 ms-4">
                                    <h6 class="mb-1 font-heading"><a href="product.php?id=<?= $item['id'] ?>" class="text-decoration-none text-dark"><?= htmlspecialchars($item['name']) ?></a></h6>
                                    <p class="text-muted small mb-0"><?= formatVND($item['price']) ?></p>
                                </div>

                                <!-- Quantity -->
                                <div class="d-flex align-items-center mx-4">
                                    <input type="number" min="1" value="<?= $item['qty'] ?>" 
                                           class="form-control form-control-sm text-center cart-qty-input" 
                                           style="width: 60px;" data-id="<?= $item['id'] ?>">
                                </div>

                                <!-- Subtotal -->
                                <div class="text-end" style="min-width: 80px;">
                                    <span class="fw-medium"><?= formatVND($item['subtotal']) ?></span>
                                </div>

                                <!-- Remove -->
                                <button class="btn btn-link text-danger btn-sm ms-3 btn-remove-cart" data-id="<?= $item['id'] ?>">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="col-lg-4">
                <div class="card border-0 bg-light p-4">
                    <h5 class="font-heading mb-4">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span><?= formatVND($totalPrice) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-muted">Shipping</span>
                        <span class="text-success">Free</span>
                    </div>
                    <hr class="opacity-25">
                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5 mb-0">Total</span>
                        <span class="h5 mb-0 text-accent"><?= formatVND($totalPrice) ?></span>
                    </div>
                    <a href="checkout.php" class="btn btn-luxury w-100 py-3">Proceed to Checkout</a>
                    <div class="text-center mt-3">
                        <a href="category.php" class="text-muted small text-decoration-none">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const updateCart = (id, qty, action = 'update') => {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('product_id', id);
        formData.append('quantity', qty);

        fetch('api/cart.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    location.reload(); // Reload to update totals
                }
            });
    };

    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('change', function() {
            if (this.value > 0) updateCart(this.dataset.id, this.value);
        });
    });

    document.querySelectorAll('.btn-remove-cart').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Remove this item?')) {
                updateCart(this.dataset.id, 0, 'remove');
            }
        });
    });
});
</script>
