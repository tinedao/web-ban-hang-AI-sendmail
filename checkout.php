<?php
require 'config/database.php';
$pageTitle = "Checkout";

// Authentication Guard
if (!isLogin()) {
    redirect('login.php?msg=checkout');
}

$isBuyNow = isset($_GET['buy_now']) && $_GET['buy_now'] == 1 && !empty($_GET['product_id']);

// Cart Guard
if (!$isBuyNow && empty($_SESSION['cart'])) {
    redirect('category.php');
}

$userId = $_SESSION['user_id'];
$user = getData('users', ['where' => ['id' => $userId], 'limit' => 1])[0] ?? [];

// Calculate Cart Totals
$cartItems = [];
$totalPrice = 0;

if ($isBuyNow) {
    $pId = (int)$_GET['product_id'];
    if (hasProductEventColumn()) {
        $eventSlug = getActiveSaleEventSlug();
        if (empty($eventSlug)) {
            redirect('category.php');
        }
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND event_slug = ?");
        $stmt->execute([$pId, $eventSlug]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$pId]);
    }
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        $product['qty'] = 1;
        $product['subtotal'] = $product['price'];
        $totalPrice += $product['subtotal'];
        $cartItems[] = $product;
    } else {
        redirect('category.php');
    }
} else {
    $cartSnapshot = getCartSnapshot();
    $cartItems = $cartSnapshot['items'];
    $totalPrice = $cartSnapshot['total'];
}

if (empty($cartItems)) {
    redirect('category.php');
}

// Generate Random QR Data for Online Payment
$randomTransId = 'TRX' . rand(100000, 999999); // Random Transaction Code
$qrContent = "PAY|$randomTransId|$totalPrice"; // Simple QR Content
$qrImage = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrContent);

// Handle Order Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? 'cod';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    
    // Validation
    $errors = [];
    if (empty($name) || empty($phone) || empty($address)) {
        $errors[] = "Please fill in all shipping details.";
    }
    
    if (empty($errors)) {
        // Determine Status
        $status = ($paymentMethod === 'online') ? 'paid' : 'pending';
        
        // Prepare Order Data
        $orderData = [
            'user_id' => $userId,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'payment_method' => $paymentMethod,
            'total' => $totalPrice,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Insert Order
        if (insertData('orders', $orderData)) {
            $orderId = getLastId();

            // Insert Order Items
            foreach ($cartItems as $item) {
                insertData('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'product_name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['qty']
                ]);
            }

            // --- GỬI EMAIL HÓA ĐƠN ---
            require_once 'sendmail.php';
            $emailTo = !empty($_POST['invoice_email']) ? $_POST['invoice_email'] : $user['email'];
            $shippingInfo = [
                'phone' => $phone,
                'address' => $address,
                'payment_method' => $paymentMethod,
                'total' => $totalPrice
            ];
            sendInvoiceEmail($emailTo, $name, $orderId, $shippingInfo, $cartItems);
            // --------------------------

            // Clear Cart
            if (!$isBuyNow) {
                unset($_SESSION['cart']);
            }

            // Redirect
            redirect("order_success.php?id=$orderId");
        } else {
            $error = "Failed to create order. Please try again.";
        }
    } else {
        $error = implode("<br>", $errors);
    }
}

include 'includes/header.php';
?>

<div class="container py-5 fade-in-page">
    <div class="text-center mb-5">
        <h1 class="font-heading display-5">Thanh toán</h1>
        <p class="text-muted">Hoàn tất giao dịch mua hàng của bạn</p>
    </div>

    <div class="row g-5">
        <!-- Left Column: Payment & Shipping -->
        <div class="col-lg-7">
            <?php include 'includes/alert.php'; ?>

            <form id="checkoutForm" method="POST" action="">
                
                <!-- Payment Method Selection -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-4 border-bottom-0">
                        <h5 class="font-heading mb-0">1. Phương thức thanh toán</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="payment_method" id="pm_cod" value="cod" checked>
                            <label class="btn btn-outline-dark py-3" for="pm_cod">
                                <i class="fa-solid fa-truck me-2"></i> Thanh toán khi nhận hàng
                            </label>

                            <input type="radio" class="btn-check" name="payment_method" id="pm_online" value="online">
                            <label class="btn btn-outline-dark py-3" for="pm_online">
                                <i class="fa-regular fa-credit-card me-2"></i> Thanh toán trực tuyến
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Shipping Info (Required for both COD and Online) -->
                <div id="section-shipping" class="card border-0 shadow-sm mb-4 transition-section">
                    <div class="card-header bg-white p-4 border-bottom-0">
                        <h5 class="font-heading mb-0">2. Chi tiết vận chuyển</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-luxury">Họ tên</label>
                                <input type="text" class="form-control form-control-luxury" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label-luxury">Số điện thoại</label>
                                <input type="text" class="form-control form-control-luxury" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label-luxury">Địa chỉ giao hàng</label>
                                <textarea class="form-control form-control-luxury" name="address" rows="3" placeholder="Street address, Apt, City..." required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label-luxury">Email hóa đơn (Tùy chọn)</label>
                                <input type="email" class="form-control form-control-luxury" name="invoice_email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" placeholder="Leave empty to skip invoice email">
                                <div class="form-text">Chúng tôi sẽ gửi hóa đơn đến địa chỉ email này. Hãy xóa địa chỉ này nếu bạn không muốn nhận hóa đơn.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Online Payment Section: QR Code -->
                <div id="section-online" class="card border-0 shadow-sm mb-4 transition-section d-none">
                    <div class="card-header bg-white p-4 border-bottom-0">
                        <h5 class="font-heading mb-0">2. Quét để thanh toán</h5>
                    </div>
                    <div class="card-body p-4 pt-0 text-center">
                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis mb-3">
                            <small><i class="fa-solid fa-circle-info me-2"></i>Vui lòng quét mã QR bên dưới bằng ứng dụng ngân hàng của bạn.</small>
                        </div>
                        <div class="qr-wrapper mb-3 p-3 bg-white border d-inline-block">
                            <!-- Placeholder for QR Code -->
                            <img src="<?= $qrImage ?>" alt="Payment QR Code" class="img-fluid" style="max-width: 200px;">
                        </div>
                        <p class="text-muted small mb-0">Mã giao dịch: <strong><?= $randomTransId ?></strong> | Số lượng: <strong class="text-dark"><?= formatVND($totalPrice) ?></strong></p>
                        <div id="payment-timer" class="mt-3 text-accent d-none">
                            <i class="fa-solid fa-spinner fa-spin me-2"></i> Xác minh thanh toán...
                        </div>
                    </div>
                </div>

                <button type="submit" id="btn-submit" class="btn btn-luxury w-100 py-3 mt-2">Đặt hàng</button>
            </form>
        </div>

        <!-- Right Column: Order Summary -->
        <div class="col-lg-5">
            <div class="card border-0 bg-light p-4 sticky-top" style="top: 100px; z-index: 1;">
                <h5 class="font-heading mb-4">Tóm tắt đơn hàng</h5>
                
                <div class="cart-summary-list mb-4" style="max-height: 300px; overflow-y: auto;">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0" style="width: 50px; height: 50px;">
                                <img src="assets/images/<?= !empty($item['image']) ? htmlspecialchars($item['image']) : 'placeholder.jpg' ?>" 
                                     class="w-100 h-100 object-fit-cover rounded" alt="Product">
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0 small font-heading"><?= htmlspecialchars($item['name']) ?></h6>
                                <small class="text-muted">x<?= $item['qty'] ?></small>
                            </div>
                            <div class="text-end">
                                <small class="fw-medium"><?= formatVND($item['subtotal']) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <hr class="opacity-25">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tổng phụ</span>
                    <span><?= formatVND($totalPrice) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-4">
                    <span class="text-muted">Vận chuyển</span>
                    <span class="text-success">Free</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="h5 mb-0">Tổng</span>
                    <span class="h5 mb-0 text-accent"><?= formatVND($totalPrice) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('checkoutForm');
    const btnSubmit = document.getElementById('btn-submit');
    const sectionOnline = document.getElementById('section-online');
    const paymentRadios = document.getElementsByName('payment_method');
    const paymentTimer = document.getElementById('payment-timer');
    let isQrShown = false;

    // Toggle Sections
    const togglePayment = () => {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        
        // Reset QR state khi đổi phương thức
        sectionOnline.classList.add('d-none');
        isQrShown = false;

        if (method === 'cod') {
            btnSubmit.textContent = 'Đặt hàng';
        } else {
            btnSubmit.textContent = 'Tiến hành thanh toán';
        }
    };

    paymentRadios.forEach(radio => radio.addEventListener('change', togglePayment));

    // Handle Submit
    form.addEventListener('submit', function(e) {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (method === 'online') {
            e.preventDefault(); // Stop immediate submit
            
            if (!isQrShown) {
                // Bước 1: Kiểm tra thông tin, nếu đúng thì hiện QR
                if (form.checkValidity()) {
                    sectionOnline.classList.remove('d-none');
                    isQrShown = true;
                    btnSubmit.textContent = 'Xác nhận thanh toán và đơn hàng';
                    // Cuộn xuống phần QR để khách thấy
                    sectionOnline.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } else {
                    form.reportValidity(); // Hiển thị lỗi nếu thiếu thông tin
                }
            } else {
                // Bước 2: Xử lý thanh toán sau khi đã hiện QR
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fa-solid fa-lock me-2"></i>Processing...';
                paymentTimer.classList.remove('d-none');

                // Simulate 5s delay
                setTimeout(() => {
                    form.submit();
                }, 5000);
            }
        }
        // If COD, let form submit naturally
    });

    // Init
    togglePayment();
});
</script>
