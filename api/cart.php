<?php
require '../config/database.php';

header('Content-Type: application/json');

if (!isLogin()) {
    echo json_encode(['status' => 'auth_required', 'message' => 'Please login to continue.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($productId > 0) {
        if ($action === 'add') {
            $product = getData('products', [
                'where' => ['id' => $productId],
                'limit' => 1
            ]);
            if (empty($product)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'San pham khong thuoc su kien hien tai hoac khong ton tai.'
                ]);
                exit;
            }
            addToCart($productId, $quantity);
            echo json_encode([
                'status' => 'success',
                'message' => 'Item added to cart.',
                'cartCount' => getCartCount()
            ]);
            exit;
        } elseif ($action === 'update') {
            updateCart($productId, $quantity);
            echo json_encode([
                'status' => 'success',
                'message' => 'Cart updated.',
                'cartCount' => getCartCount()
            ]);
            exit;
        } elseif ($action === 'remove') {
            removeFromCart($productId);
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.'];
            echo json_encode(['status' => 'success', 'message' => 'Item removed.', 'cartCount' => getCartCount()]);
            exit;
        }
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
