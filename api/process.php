<?php
error_reporting(0);
ini_set('display_errors', 0);
ob_start();

require '../config/database.php';
require_once '../vendor/autoload.php';
require_once '../includes/theme.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? 0;
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$action = $_REQUEST['action'] ?? '';

if ($action === 'send') {
    if (!$userId && !$isAdmin) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập.']);
        exit;
    }

    $message = trim($_POST['message'] ?? '');
    if ($message !== '') {
        $data = [
            'user_id' => $userId,
            'message' => $message,
            'is_admin' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (insertData('messages', $data)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Không thể lưu tin nhắn vào hệ thống.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Tin nhắn đang trống.']);
    }

} elseif ($action === 'fetch') {
    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập.']);
        exit;
    }

    $sql = 'SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'messages' => $messages]);

} elseif ($action === 'chat_ai') {
    set_time_limit(0);

    $userMsg = trim($_POST['message'] ?? '');
    if (!$userMsg) {
        echo json_encode(['status' => 'error']);
        exit;
    }

    $apiKey = $_ENV['OPENROUTER_API_KEY'] ?? '';
    $apiUrl = $_ENV['OPENROUTER_API_URL'] ?? '';
    $model  = $_ENV['OPENROUTER_MODEL'] ?? '';

    $activeTheme = $THEME['slug'] ?? 'default';
    $themeContexts = [
        'tet' => [
            'name' => 'Tết',
            'focus' => 'đồ tông đỏ-vàng, quà tặng, set du xuân, áo, mũ, phụ kiện lễ hội',
        ],
        'gpmnam' => [
            'name' => '30/4',
            'focus' => 'đồ sự kiện, áo, mũ, giày, phụ kiện cho hoạt động ngoài trời và du lịch',
        ],
        'quockhanh' => [
            'name' => '2/9',
            'focus' => 'trang phục sự kiện, giày, phụ kiện, quà tặng Quốc khánh',
        ],
        'noel' => [
            'name' => 'Noel',
            'focus' => 'đồ mùa lễ hội cuối năm, hoodie, áo ấm, phụ kiện Giáng sinh, quà tặng',
        ],
        'default' => [
            'name' => 'Thường ngày',
            'focus' => 'quần áo, mũ, giày, vòng cổ, phụ kiện, đồ lưu niệm theo nhu cầu thường ngày',
        ],
    ];
    $themeContext = $themeContexts[$activeTheme] ?? $themeContexts['default'];

    $messages = [];
    $chatbotClosing = 'Dạ vậy anh/chị còn cần gì thêm không ạ?';

    $wrapChatbotResponse = function(array $payload): array {
        return [
            'choices' => [[
                'message' => [
                    'content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ],
            ]],
        ];
    };

    $normalizeIntentText = function(string $text): string {
        $text = mb_strtolower(trim($text), 'UTF-8');
        if ($text === '') {
            return '';
        }

        $text = strtr($text, [
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
            'đ' => 'd',
        ]);

        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if (is_string($ascii) && $ascii !== '') {
                $text = $ascii;
            }
        }

        $text = preg_replace('/[^a-z0-9\s#]+/i', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    };

    $extractOrderIdFromText = function(string $text) use ($normalizeIntentText): ?int {
        $text = $normalizeIntentText($text);
        if ($text === '') {
            return null;
        }

        if (preg_match('/\b(?:ma|madon|don|donhang|hoa\s*don|hoadon|invoice|order)\D*0*(\d{1,10})\b/ui', $text, $matches)) {
            return max(1, (int)$matches[1]);
        }

        return null;
    };

    $looksLikeOrderLookup = function(string $text) use ($extractOrderIdFromText, $normalizeIntentText): bool {
        $normalized = $normalizeIntentText($text);
        if ($normalized === '') {
            return false;
        }

        if ($extractOrderIdFromText($normalized) === null) {
            return false;
        }

        return (bool)preg_match(
            '/\b(?:ma|madon|don|donhang|hoa\s*don|hoadon|invoice|order|tra\s*cuu|kiem\s*tra)\b/ui',
            $normalized
        );
    };

    $getPaymentMethodLabel = function(?string $paymentMethod): string {
        $paymentMethod = strtolower(trim((string)$paymentMethod));
        $map = [
            'cod' => 'Thanh toán khi nhận hàng',
            'online' => 'Thanh toán online',
            'bank' => 'Chuyển khoản ngân hàng',
            'banking' => 'Chuyển khoản ngân hàng',
            'bank_transfer' => 'Chuyển khoản ngân hàng',
            'cash' => 'Tiền mặt',
            'momo' => 'Ví MoMo',
            'vnpay' => 'VNPay',
            'paypal' => 'PayPal',
            'card' => 'Thẻ ngân hàng',
        ];

        return $map[$paymentMethod] ?? strtoupper((string)$paymentMethod ?: 'COD');
    };

    $getOrderStatusMeta = function(?string $status): array {
        $status = strtolower(trim((string)$status));
        $map = [
            'paid' => ['label' => 'Đã thanh toán', 'tone' => 'success'],
            'pending' => ['label' => 'Chờ xử lý', 'tone' => 'warning'],
            'shipped' => ['label' => 'Đang giao', 'tone' => 'info'],
            'cancelled' => ['label' => 'Đã hủy', 'tone' => 'danger'],
        ];

        return $map[$status] ?? ['label' => ucfirst($status ?: 'Không xác định'), 'tone' => 'secondary'];
    };

    $buildInvoiceLookupPayload = function(int $orderId) use ($userId, $chatbotClosing, $getOrderStatusMeta, $getPaymentMethodLabel): array {
        $orderCode = '#' . str_pad((string)$orderId, 6, '0', STR_PAD_LEFT);

        if (!$userId) {
            return [
                'reply' => "Để tra cứu hóa đơn {$orderCode}, anh/chị vui lòng đăng nhập trước giúp em.\n\n{$chatbotClosing}",
                'url' => 'login.php?msg=auth',
                'products' => [],
                'invoice' => null,
            ];
        }

        $orders = getData('orders', [
            'where' => ['id' => $orderId, 'user_id' => $userId],
            'limit' => 1,
        ]);

        if (empty($orders)) {
            return [
                'reply' => "Em chưa tìm thấy hóa đơn {$orderCode} trong tài khoản của anh/chị.\n\nAnh/chị vui lòng kiểm tra lại mã đơn hàng giúp em nhé.",
                'url' => '',
                'products' => [],
                'invoice' => null,
            ];
        }

        $order = $orders[0];
        $statusMeta = $getOrderStatusMeta($order['status'] ?? 'pending');
        $items = getData('order_items', [
            'where' => ['order_id' => $orderId],
            'order_by' => 'id ASC',
        ]);

        $invoiceItems = [];
        $totalQuantity = 0;
        foreach ($items as $item) {
            $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
            $price = (float)($item['price'] ?? 0);
            $subtotal = $price * $qty;
            $totalQuantity += $qty;
            $invoiceItems[] = [
                'name' => (string)($item['product_name'] ?? $item['name'] ?? 'Sản phẩm'),
                'qty' => $qty,
                'price_formatted' => formatVND($price),
                'subtotal_formatted' => formatVND($subtotal),
            ];
        }

        return [
            'reply' => "Em đã tìm thấy hóa đơn {$orderCode}. Em gửi anh/chị bản tóm tắt nhỏ ngay bên dưới để theo dõi nhanh nhé.",
            'url' => '',
            'products' => [],
            'invoice' => [
                'order_id' => $orderId,
                'order_code' => $orderCode,
                'created_at_label' => !empty($order['created_at']) ? date('d/m/Y H:i', strtotime((string)$order['created_at'])) : date('d/m/Y H:i'),
                'status_label' => $statusMeta['label'],
                'status_tone' => $statusMeta['tone'],
                'payment_method_label' => $getPaymentMethodLabel($order['payment_method'] ?? 'cod'),
                'total_formatted' => formatVND($order['total'] ?? 0),
                'customer_name' => trim((string)($order['name'] ?? $_SESSION['name'] ?? 'Khách hàng')),
                'customer_phone' => trim((string)($order['phone'] ?? '')),
                'customer_address' => trim((string)($order['address'] ?? '')),
                'items' => array_slice($invoiceItems, 0, 4),
                'item_count' => count($items),
                'total_quantity' => $totalQuantity,
                'detail_url' => 'order_detail.php?id=' . $orderId,
            ],
        ];
    };

    $isGreetingOnly = function(string $text) use ($normalizeIntentText): bool {
        $normalized = $normalizeIntentText($text);
        if ($normalized === '') {
            return false;
        }

        $compact = preg_replace('/\s+/u', '', $normalized) ?? $normalized;

        return (bool)preg_match(
            '/^(?:xinchao|xinchaoshop|chao|chaoshop|hello|helloshop|hi|hishop|hey|heyshop|alo|aloshop)$/u',
            $compact
        );
    };

    $shouldUseProductTool = function(string $text) use ($normalizeIntentText, $isGreetingOnly): bool {
        $normalized = $normalizeIntentText($text);
        if ($normalized === '') {
            return false;
        }

        if ($isGreetingOnly($normalized)) {
            return false;
        }

        return (bool)preg_match(
            '/\b(tim|kiem|mua|xem|goi y|tu van|san pham|ao|quan|mu|non|giay|hoodie|phu kien|qua tang|luu niem)\b/ui',
            $normalized
        );
    };

    $looksLikeProductLookup = function(string $text) use ($normalizeIntentText): bool {
        $text = $normalizeIntentText($text);
        if ($text === '') {
            return false;
        }

        return (bool)preg_match(
            '/\b(tim|kiem|mua|xem|goi y|tu van|tư vấn|san pham|ao|áo|quan|quần|mu|mũ|non|nón|giay|giày|hoodie|phu kien|phụ kiện|qua tang|quà tặng|luu niem|lưu niệm)\b/ui',
            $text
        );
    };

    $getQualityNote = function(array $product): string {
        $description = trim((string)($product['description'] ?? ''));
        if ($description !== '') {
            return 'Mô tả nổi bật: ' . $description;
        }

        return 'Hiện shop chưa có mô tả chi tiết thêm về chất liệu của mẫu này.';
    };

    $buildProductAdvicePayload = function(array $result) use ($chatbotClosing, $getQualityNote): array {
        $products = is_array($result['products'] ?? null) ? array_slice($result['products'], 0, 3) : [];
        $search = trim((string)($result['applied_filters']['search'] ?? ''));

        if (empty($products)) {
            return [
                'reply' => "Dạ, bên em hiện không có mẫu đó.\n\n{$chatbotClosing}",
                'url' => '',
                'products' => [],
            ];
        }

        usort($products, static fn(array $a, array $b): int => ((float)($a['price'] ?? 0)) <=> ((float)($b['price'] ?? 0)));

        $cheapest = $products[0];
        $expensive = $products[count($products) - 1];
        $intro = $search !== ''
            ? "Dạ, em đã chọn ra " . count($products) . " mẫu gần với \"{$search}\" ở ngay bên dưới."
            : "Dạ, em đã chọn ra " . count($products) . " mẫu phù hợp ở ngay bên dưới.";

        $priceAdvice = 'Mẫu giá mềm nhất là ' . trim((string)($cheapest['name'] ?? ''))
            . ' (' . trim((string)($cheapest['price_formatted'] ?? '')) . ').';

        if (count($products) > 1) {
            $priceAdvice .= ' Mẫu giá cao hơn là ' . trim((string)($expensive['name'] ?? ''))
                . ' (' . trim((string)($expensive['price_formatted'] ?? '')) . ').';
        }

        $qualityAdvice = $getQualityNote($expensive);

        return [
            'reply' => $intro . ' ' . $priceAdvice . ' ' . $qualityAdvice . "\n\n" . $chatbotClosing,
            'url' => '',
            'products' => $products,
        ];
    };

    $orderLookupId = $extractOrderIdFromText($userMsg);
    if ($orderLookupId !== null && $looksLikeOrderLookup($userMsg)) {
        ob_clean();
        echo json_encode($wrapChatbotResponse($buildInvoiceLookupPayload($orderLookupId)));
        die();
    }

    if ($shouldUseProductTool($userMsg)) {
        $productResult = getChatbotProductSuggestions([
            'search' => $userMsg,
            'limit' => 3,
            'sort' => 'relevance',
            'only_in_stock' => true,
            'event_slug' => 'auto',
            'match_all_keywords' => false,
        ]);

        ob_clean();
        echo json_encode($wrapChatbotResponse($buildProductAdvicePayload($productResult)));
        die();
    }

    $appName = $_ENV['APP_NAME'] ?? 'Cửa hàng';
    $systemPrompt = "Bạn là trợ lý tư vấn bán hàng {$appName}. Chỉ trả lời tiếng Việt, ngắn gọn, thân thiện. Theo sự kiện {$themeContext['name']}. Không bịa. Không dùng markdown. JSON: {\"reply\":\"...\",\"url\":\"\",\"products\":[]}";

    $messages[] = ['role' => 'system', 'content' => $systemPrompt];

    $messages[] = ['role' => 'user', 'content' => $userMsg];

    $callAiApi = function(array $msgs, bool $forceJson = false) use ($apiKey, $apiUrl, $model): array {
        if (!function_exists('curl_init')) {
            return ['error' => ['message' => 'Hosting hiện không hỗ trợ CURL.']];
        }

        if ($apiKey === '' || $apiUrl === '' || $model === '') {
            return ['error' => ['message' => 'Thiếu cấu hình AI.']];
        }

        $payload = [
            'model' => $model,
            'messages' => $msgs,
        ];
        if ($forceJson) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . ($_ENV['BASE_URL'] ?? 'http://localhost'),
            'X-Title: ' . ($_ENV['APP_NAME'] ?? 'Crowné'),
            'User-Agent: CrowneShop/1.0'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $res = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['error' => ['message' => 'Lỗi kết nối máy chủ: ' . $err]];
        }
        curl_close($ch);

        $decoded = json_decode((string)$res, true);
        return is_array($decoded) ? $decoded : ['error' => ['message' => 'Phản hồi AI không hợp lệ.']];
    };

    $cleanAiJsonResponse = function(array $res) use ($wrapChatbotResponse): array {
        if (!is_array($res) || isset($res['error']) || !isset($res['choices'][0]['message']['content'])) {
            return $wrapChatbotResponse([
                'reply' => 'Dạ, hệ thống tư vấn đang bận một chút. Anh/chị vui lòng thử lại sau giúp em nhé.',
                'url' => '',
                'products' => [],
            ]);
        }

        $content = $res['choices'][0]['message']['content'];
        $cleaned = preg_replace('/^```json\s*|\s*```$/s', '', (string)$content);
        $cleaned = preg_replace('/^```\s*|\s*```$/s', '', (string)$cleaned);

        $start = strpos($cleaned, '{');
        $end = strrpos($cleaned, '}');
        if ($start !== false && $end !== false && $end >= $start) {
            $cleaned = substr($cleaned, $start, $end - $start + 1);
        }

        $res['choices'][0]['message']['content'] = trim((string)$cleaned);
        return $res;
    };

    $response = $callAiApi($messages, true);
    $response = $cleanAiJsonResponse($response);
    ob_clean();
    echo json_encode($response);
    die();

} elseif ($action === 'admin_get_users') {
    if (!$isAdmin) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền truy cập.']);
        exit;
    }

    $sql = "SELECT u.id, u.name, u.phone,
            (SELECT message FROM messages WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as last_message,
            (SELECT created_at FROM messages WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as last_time,
            (SELECT COUNT(*) FROM messages WHERE user_id = u.id AND is_read = 0 AND is_admin = 0) as unread_count
            FROM users u
            JOIN messages m ON u.id = m.user_id
            GROUP BY u.id
            ORDER BY last_time DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'users' => $users]);

} elseif ($action === 'admin_get_conversation') {
    if (!$isAdmin) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền truy cập.']);
        exit;
    }

    $targetUserId = $_GET['user_id'] ?? 0;
    if (!$targetUserId) {
        echo json_encode(['status' => 'error']);
        exit;
    }

    $updateSql = 'UPDATE messages SET is_read = 1 WHERE user_id = ? AND is_admin = 0';
    $stmt = $conn->prepare($updateSql);
    $stmt->execute([$targetUserId]);

    $sql = 'SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC';
    $stmt = $conn->prepare($sql);
    $stmt->execute([$targetUserId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'messages' => $messages]);

} elseif ($action === 'admin_send') {
    if (!$isAdmin) {
        echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền truy cập.']);
        exit;
    }

    $targetUserId = $_POST['user_id'] ?? 0;
    $msg = trim($_POST['message'] ?? '');

    if ($targetUserId && $msg) {
        $data = [
            'user_id' => $targetUserId,
            'message' => $msg,
            'is_admin' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        if (insertData('messages', $data)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error']);
        }
    }
}
?>
