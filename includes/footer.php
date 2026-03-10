</div>

<footer class="bg-dark text-white pt-5 pb-4 mt-auto" style="border-top: 1px solid #222;">
    <div class="container">
        <div class="row g-4 justify-content-between">
            <div class="col-lg-4 col-md-6">
                <h5 class="font-heading text-white mb-4" style="letter-spacing: 2px;"><?= $_ENV['APP_NAME'] ?></h5>
                <p class="text-white-50 small mb-4" style="line-height: 1.8; max-width: 320px;">
                    Chuyên cung cấp đồ sự kiện quanh năm: Tết, 30/4, 2/9, Noel và các dịp lễ đặc biệt.
                    Sản phẩm gồm áo, mũ, giày, phụ kiện và quà tặng lưu niệm.
                </p>
                <div class="d-flex gap-3 social-links">
                    <a href="#" class="social-btn"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="social-btn"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="social-btn"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="#" class="social-btn"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="text-uppercase text-accent mb-4 small letter-spacing-2">Mua theo sự kiện</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="category.php?search=tet">Bộ sưu tập Tết</a></li>
                    <li><a href="category.php?search=30%2F4">Bộ sưu tập 30/4</a></li>
                    <li><a href="category.php?search=2%2F9">Bộ sưu tập 2/9</a></li>
                    <li><a href="category.php?search=noel">Bộ sưu tập Noel</a></li>
                    <li><a href="category.php">Tất cả sản phẩm</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="text-uppercase text-accent mb-4 small letter-spacing-2">Dịch vụ</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="#">Đặt combo sự kiện</a></li>
                    <li><a href="#">In logo theo yêu cầu</a></li>
                    <li><a href="#">Đơn doanh nghiệp</a></li>
                    <li><a href="#">Vận chuyển & đổi trả</a></li>
                    <li><a href="contact.php">Liên hệ tư vấn</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="text-uppercase text-accent mb-4 small letter-spacing-2">Nhận ưu đãi theo mùa</h6>
                <p class="text-white-50 small mb-3">Cập nhật bộ sưu tập sự kiện mới và mã giảm giá độc quyền.</p>
                <form action="#" class="newsletter-form mb-4">
                    <div class="input-group">
                        <input type="email" class="form-control bg-transparent border-secondary text-white shadow-none" placeholder="Email của bạn" style="font-size: 0.85rem; border-right: none;">
                        <button class="btn btn-outline-secondary text-accent border-start-0" type="button"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </form>
                <div>
                    <p class="text-white-50 small mb-2"><i class="fa-solid fa-location-dot me-2 text-accent" style="width: 20px;"></i> <?= $_ENV['ADDRESS_ADMIN'] ?? 'Quận 1, TP. Hồ Chí Minh' ?></p>
                    <p class="text-white-50 small mb-2"><i class="fa-solid fa-phone me-2 text-accent" style="width: 20px;"></i> <?= $_ENV['PHONE_ADMIN'] ?? '0912345678' ?></p>
                    <p class="text-white-50 small mb-0"><i class="fa-solid fa-envelope me-2 text-accent" style="width: 20px;"></i> <?= $_ENV['EMAIL_ADMIN'] ?? 'support@example.com' ?></p>
                </div>
            </div>
        </div>

        <hr class="border-secondary opacity-25 my-5">

        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="small text-white-50 mb-0">&copy; <?= date('Y') ?> <strong><?= $_ENV['APP_NAME'] ?></strong>. Cửa hàng đồ sự kiện & lưu niệm.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="payment-icons opacity-50">
                    <i class="fa-brands fa-cc-visa fa-lg mx-2"></i>
                    <i class="fa-brands fa-cc-mastercard fa-lg mx-2"></i>
                    <i class="fa-brands fa-cc-paypal fa-lg mx-2"></i>
                    <i class="fa-brands fa-apple-pay fa-lg mx-2"></i>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php include __DIR__ . '/chat_widget.php'; ?>
<?php include __DIR__ . '/theme_switcher.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Global Toast Notification Function
    window.showToast = function(type, message) {
        let container = document.getElementById('alert-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'alert-container';
            document.body.appendChild(container);
        }
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} d-flex align-items-center`;
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        alertDiv.innerHTML = `<i class="fa-solid ${icon} me-2"></i><div>${message}</div>`;
        container.appendChild(alertDiv);
        setTimeout(() => { alertDiv.style.opacity = '0'; setTimeout(() => alertDiv.remove(), 500); }, 3000);
    };

    // Global Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.querySelectorAll('#alert-container .alert').forEach(function(alertEl) {
                if (window.bootstrap) {
                    new bootstrap.Alert(alertEl).close();
                } else {
                    alertEl.remove();
                }
            });
        }, 5000);
    });

    // AJAX Add to Cart
    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-add-cart');
            if (btn) {
                e.preventDefault();
                const productId = btn.getAttribute('data-id');
                const quantity = 1;

                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                btn.style.pointerEvents = 'none';

                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('product_id', productId);
                formData.append('quantity', quantity);

                fetch('api/cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        btn.innerHTML = '<i class="fa-solid fa-check me-2"></i>Added';
                        window.showToast('success', data.message);

                        const cartBadge = document.getElementById('cart-count');
                        if (cartBadge) {
                            cartBadge.innerText = data.cartCount;
                            cartBadge.style.display = 'inline-block';
                        }

                        const cartIcon = document.querySelector('a[href="cart.php"].nav-icon');
                        if (cartIcon) {
                            cartIcon.classList.add('cart-bump');
                            setTimeout(() => cartIcon.classList.remove('cart-bump'), 500);
                        }

                        setTimeout(() => {
                            btn.innerHTML = originalContent;
                            btn.style.pointerEvents = 'auto';
                        }, 1500);
                    } else if (data.status === 'auth_required') {
                        window.location.href = 'login.php?msg=cart';
                    } else {
                        window.showToast('danger', data.message || 'Error adding to cart');
                        btn.innerHTML = originalContent;
                        btn.style.pointerEvents = 'auto';
                    }
                })
                .catch(err => {
                    console.error(err);
                    window.showToast('danger', 'Something went wrong');
                    btn.innerHTML = originalContent;
                    btn.style.pointerEvents = 'auto';
                });
            }
        });
    });
</script>
</body>
</html>
