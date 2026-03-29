<?php
/**
 * Tạo HTML Hóa đơn Luxury
 * Dùng cho: Email, Xem chi tiết đơn hàng (Client/Admin)
 * 
 * @param int|string $orderId Mã đơn hàng
 * @param array $orderData Thông tin đơn: ['name', 'phone', 'address', 'created_at', 'payment_method', 'total']
 * @param array $items Danh sách sản phẩm: [['name', 'qty', 'price', 'subtotal'], ...]
 * @param bool $isEmail True: Trả về full HTML cho email. False: Trả về div content cho web.
 * @return string HTML content
 */
function getLuxuryInvoiceHTML($orderId, $orderData, $items, $isEmail = true) {
    // Màu sắc chủ đạo (Luxury Theme)
    $c_primary = "#111111";
    $c_accent  = "#c5a059"; // Gold
    $c_bg      = "#f8f9fa";
    $c_text    = "#333333";
    $c_gray    = "#666666";

    // Xử lý dữ liệu đầu vào (Fallback nếu thiếu)
    $date = isset($orderData['created_at']) ? date('d/m/Y', strtotime($orderData['created_at'])) : date('d/m/Y');
    $customerName = $orderData['name'] ?? 'Khách hàng thân mến';
    $phone = $orderData['phone'] ?? 'Chưa có';
    $address = $orderData['address'] ?? 'Chưa có';
    $paymentMethodRaw = strtolower((string)($orderData['payment_method'] ?? 'cod'));
    $paymentMethodMap = [
        'cod' => 'Thanh toán khi nhận hàng',
        'cash' => 'Tiền mặt',
        'bank' => 'Chuyển khoản ngân hàng',
        'banking' => 'Chuyển khoản ngân hàng',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'momo' => 'Ví MoMo',
        'vnpay' => 'VNPay',
        'paypal' => 'PayPal',
        'card' => 'Thẻ ngân hàng'
    ];
    $paymentMethod = $paymentMethodMap[$paymentMethodRaw] ?? strtoupper((string)($orderData['payment_method'] ?? 'COD'));
    $total = $orderData['total'] ?? 0;

    // Tạo các dòng sản phẩm
    $itemsHtml = '';
    foreach ($items as $item) {
        // Hỗ trợ cả key từ DB (product_name, quantity) và key từ Session Cart (name, qty)
        $pName = htmlspecialchars($item['name'] ?? $item['product_name'] ?? 'Sản phẩm');
        $pQty = $item['qty'] ?? $item['quantity'] ?? 1;
        $pPrice = $item['price'] ?? 0;
        // Tính subtotal nếu chưa có
        $pSubtotal = $item['subtotal'] ?? ($pPrice * $pQty);

        $itemsHtml .= "
        <tr>
            <td style='padding: 15px 10px; border-bottom: 1px solid #eee; color: $c_text; vertical-align: middle;'>
                <div style='font-weight: 600; font-size: 14px;'>$pName</div>
            </td>
            <td style='padding: 15px 10px; border-bottom: 1px solid #eee; text-align: center; color: $c_text; vertical-align: middle;'>x$pQty</td>
            <td style='padding: 15px 10px; border-bottom: 1px solid #eee; text-align: right; color: $c_text; vertical-align: middle;'>" . (function_exists('formatVND') ? formatVND($pPrice) : number_format((float)$pPrice, 0, ',', '.') . " VNĐ") . "</td>
            <td style='padding: 15px 10px; border-bottom: 1px solid #eee; text-align: right; font-weight: bold; color: $c_text; vertical-align: middle;'>" . (function_exists('formatVND') ? formatVND($pSubtotal) : number_format((float)$pSubtotal, 0, ',', '.') . " VNĐ") . "</td>
        </tr>";
    }

    // Nội dung chính của hóa đơn
    $invoiceContent = "
        <div style='max-width: 700px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid #eee;'>
            
            <!-- Header Luxury -->
            <div style='background-color: $c_primary; padding: 40px; text-align: center; border-bottom: 4px solid $c_accent;'>
                <h1 style='color: $c_accent; margin: 0; font-family: serif; letter-spacing: 3px; font-size: 32px; text-transform: uppercase;'>{$_ENV['APP_NAME']}</h1>
                <p style='color: #fff; margin: 10px 0 0; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.7;'>Thời trang và quà lưu niệm sự kiện</p>
            </div>

            <!-- Invoice Info -->
            <div style='padding: 40px;'>
                <!-- Info Grid -->
                <table style='width: 100%; margin-bottom: 40px;'>
                    <tr>
                        <td style='vertical-align: top;'>
                            <h4 style='margin: 0 0 5px; color: $c_gray; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;'>Thông tin người nhận</h4>
                            <h3 style='margin: 0; color: $c_primary; font-size: 18px;'>$customerName</h3>
                            <p style='margin: 5px 0 0; color: $c_gray; font-size: 14px; line-height: 1.5;'>$phone<br>$address</p>
                        </td>
                        <td style='vertical-align: top; text-align: right;'>
                            <h4 style='margin: 0 0 5px; color: $c_gray; font-size: 11px; text-transform: uppercase; letter-spacing: 1px;'>Thông tin hóa đơn</h4>
                            <h3 style='margin: 0; color: $c_primary; font-size: 18px;'>#$orderId</h3>
                            <p style='margin: 5px 0 0; color: $c_gray; font-size: 14px; line-height: 1.5;'>Ngày: $date<br>Phương thức: <span style='color: $c_primary; font-weight: bold;'>$paymentMethod</span></p>
                        </td>
                    </tr>
                </table>

                <!-- Items Table -->
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 14px;'>
                    <thead>
                        <tr style='background-color: $c_bg;'>
                            <th style='padding: 12px 10px; text-align: left; font-size: 11px; text-transform: uppercase; color: $c_gray; letter-spacing: 1px;'>Sản phẩm</th>
                            <th style='padding: 12px 10px; text-align: center; font-size: 11px; text-transform: uppercase; color: $c_gray; letter-spacing: 1px;'>SL</th>
                            <th style='padding: 12px 10px; text-align: right; font-size: 11px; text-transform: uppercase; color: $c_gray; letter-spacing: 1px;'>Đơn giá</th>
                            <th style='padding: 12px 10px; text-align: right; font-size: 11px; text-transform: uppercase; color: $c_gray; letter-spacing: 1px;'>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        $itemsHtml
                    </tbody>
                </table>

                <!-- Totals -->
                <div style='border-top: 2px solid $c_primary; padding-top: 20px;'>
                    <table style='width: 100%; max-width: 280px; margin-left: auto;'>
                        <tr>
                            <td style='padding: 5px 0; color: $c_gray; font-size: 14px;'>Tạm tính:</td>
                            <td style='padding: 5px 0; text-align: right; font-weight: bold; color: $c_text; font-size: 14px;'>" . (function_exists('formatVND') ? formatVND($total) : number_format((float)$total, 0, ',', '.') . " VNĐ") . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 5px 0; color: $c_gray; font-size: 14px;'>Phí vận chuyển:</td>
                            <td style='padding: 5px 0; text-align: right; color: #198754; font-size: 14px;'>Miễn phí</td>
                        </tr>
                        <tr>
                            <td style='padding: 15px 0; font-size: 16px; font-weight: bold; color: $c_primary;'>Tổng cộng:</td>
                            <td style='padding: 15px 0; text-align: right; font-size: 24px; font-weight: bold; color: $c_accent;'>" . (function_exists('formatVND') ? formatVND($total) : number_format((float)$total, 0, ',', '.') . " VNĐ") . "</td>
                        </tr>
                    </table>
                </div>

                <!-- Footer -->
                <div style='margin-top: 50px; text-align: center; color: $c_gray; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px;'>
                    <p style='margin-bottom: 8px;'>Cảm ơn bạn đã chọn <strong>{$_ENV['APP_NAME']}</strong>.</p>
                    <p style='margin: 0;'>Cần hỗ trợ? Liên hệ <a href='mailto:{$_ENV['EMAIL_ADMIN']}' style='color: $c_accent; text-decoration: none;'>{$_ENV['EMAIL_ADMIN']}</a> hoặc {$_ENV['PHONE_ADMIN']}</p>
                </div>
            </div>
        </div>
    ";

    // Nếu là Email, bọc trong cấu trúc HTML đầy đủ
    if ($isEmail) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Hóa đơn #$orderId</title>
        </head>
        <body style='font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='padding: 20px;'>$invoiceContent</div>
        </body>
        </html>";
    }

    // Nếu là Web, chỉ trả về nội dung
    return $invoiceContent;
}
?>
