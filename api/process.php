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
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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
            echo json_encode(['status' => 'error', 'message' => 'DB Error']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Empty message']);
    }

} elseif ($action === 'fetch') {
    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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
            'name' => 'Tet',
            'focus' => 'do tong do-vang, qua tang, set du xuan, ao, mu, phu kien le hoi',
        ],
        'gpmnam' => [
            'name' => '30/4',
            'focus' => 'do su kien, ao, mu, giay, phu kien cho hoat dong ngoai troi va du lich',
        ],
        'quockhanh' => [
            'name' => '2/9',
            'focus' => 'trang phuc su kien, giay, phu kien, qua tang quoc khanh',
        ],
        'noel' => [
            'name' => 'Noel',
            'focus' => 'do mua le hoi cuoi nam, hoodie, ao am, phu kien giang sinh, qua tang',
        ],
        'default' => [
            'name' => 'Thuong ngay',
            'focus' => 'quan ao, mu, giay, vong co, phu kien, do luu niem theo nhu cau thuong ngay',
        ],
    ];
    $themeContext = $themeContexts[$activeTheme] ?? $themeContexts['default'];

    $messages = [];
    $chatbotClosing = 'Dạ vậy anh/chị còn cần gì thêm không ạ ?';

    $wrapChatbotResponse = function(array $payload): array {
        return [
            'choices' => [[
                'message' => [
                    'content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ],
            ]],
        ];
    };

    $looksLikeProductLookup = function(string $text): bool {
        $text = mb_strtolower(trim($text), 'UTF-8');
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

    if ($looksLikeProductLookup($userMsg)) {
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

    $appName = $_ENV['APP_NAME'] ?? 'Shop';
    $systemPrompt = "Bạn là trợ lý tư vấn bán hàng {$appName}. Chỉ trả lời Tiếng Việt, ngắn gọn thân thiện. Theo sự kiện {$themeContext['name']}. Không bịa. Không markdown. JSON: {\"reply\":\"...\",\"url\":\"\",\"products\":[]}";

    $messages[] = ['role' => 'system', 'content' => $systemPrompt];

    $messages[] = ['role' => 'user', 'content' => $userMsg];

    $callAiApi = function(array $msgs, bool $forceJson = false) use ($apiKey, $apiUrl, $model): array {
        if (!function_exists('curl_init')) {
            return ['error' => ['message' => 'Hosting does not support CURL.']];
        }

        if ($apiKey === '' || $apiUrl === '' || $model === '') {
            return ['error' => ['message' => 'Missing AI configuration.']];
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
            'X-Title: ' . ($_ENV['APP_NAME'] ?? 'Event Shop'),
            'User-Agent: EventShop/1.0'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);

        $res = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['error' => ['message' => 'Server Connection Error: ' . $err]];
        }
        curl_close($ch);

        $decoded = json_decode((string)$res, true);
        return is_array($decoded) ? $decoded : ['error' => ['message' => 'Invalid AI response']];
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
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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
