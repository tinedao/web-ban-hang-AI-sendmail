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
        echo json_encode(['status' => 'error', 'message' => 'Báº¡n chÆ°a Ä‘Äƒng nháº­p.']);
        exit;
    }

    $message = trim($_POST['message'] ?? '');
    if ($message === '') {
        echo json_encode(['status' => 'error', 'message' => 'Tin nháº¯n Ä‘ang trá»‘ng.']);
        exit;
    }

    $data = [
        'user_id' => $userId,
        'message' => $message,
        'is_admin' => 0,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $saved = insertData('messages', $data);
    echo json_encode([
        'status' => $saved ? 'success' : 'error',
        'message' => $saved ? null : 'KhÃ´ng thá»ƒ lÆ°u tin nháº¯n vÃ o há»‡ thá»‘ng.',
    ]);
} elseif ($action === 'fetch') {
    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Báº¡n chÆ°a Ä‘Äƒng nháº­p.']);
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
    if ($userMsg === '') {
        echo json_encode(['status' => 'error']);
        exit;
    }

    $previousAiContextRaw = trim((string)($_POST['previous_ai_context'] ?? ''));

    $apiKey = $_ENV['OPENROUTER_API_KEY'] ?? '';
    $apiUrl = $_ENV['OPENROUTER_API_URL'] ?? '';
    $model = $_ENV['OPENROUTER_MODEL'] ?? '';
    $appName = $_ENV['APP_NAME'] ?? 'Cua hang';

    $activeTheme = $THEME['slug'] ?? 'default';
    $themeContexts = [
        'tet' => ['name' => 'Tet', 'focus' => 'do tong do-vang, qua tang, set du xuan, ao, mu, phu kien le hoi'],
        'gpmnam' => ['name' => '30/4', 'focus' => 'do su kien, ao, mu, giay, phu kien cho hoat dong ngoai troi va du lich'],
        'quockhanh' => ['name' => '2/9', 'focus' => 'trang phuc su kien, giay, phu kien, qua tang Quoc khanh'],
        'noel' => ['name' => 'Noel', 'focus' => 'do mua le hoi cuoi nam, hoodie, ao am, phu kien Giang sinh, qua tang'],
        'default' => ['name' => 'Thuong ngay', 'focus' => 'quan ao, mu, giay, phu kien, do luu niem theo nhu cau thuong ngay'],
    ];
    $themeContext = $themeContexts[$activeTheme] ?? $themeContexts['default'];
    $chatbotClosing = 'Da vay anh/chi con can gi them khong a?';

    $wrapChatbotResponse = function(array $payload): array {
        return ['choices' => [[
            'message' => ['content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
        ]]];
    };

    $getTextStats = function($value): array {
        $text = is_string($value)
            ? $value
            : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!is_string($text)) {
            $text = '';
        }

        $trimmed = trim($text);
        $bytes = strlen($text);
        $chars = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        $words = $trimmed === '' ? 0 : count(preg_split('/\s+/u', $trimmed, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        return [
            'chars' => $chars,
            'bytes' => $bytes,
            'words' => $words,
            'approx_tokens' => max(1, (int)round($bytes / 4)),
        ];
    };

    $buildAiDebug = function(string $mode, array $messages, bool $forceJson = false, array $extraPayload = [], array $context = []) use ($model, $getTextStats): array {
        $requestPayload = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $extraPayload);

        if ($forceJson) {
            $requestPayload['response_format'] = ['type' => 'json_object'];
        }

        $messageStats = [];
        $joinedMessageText = [];
        foreach ($messages as $index => $message) {
            $content = (string)($message['content'] ?? '');
            $messageStats[] = [
                'index' => $index,
                'role' => (string)($message['role'] ?? ''),
                'stats' => $getTextStats($content),
            ];
            $joinedMessageText[] = $content;
        }

        return [
            'mode' => $mode,
            'request_payload' => $requestPayload,
            'request_payload_stats' => $getTextStats($requestPayload),
            'messages_text_stats' => $getTextStats(implode("\n\n", $joinedMessageText)),
            'message_stats' => $messageStats,
            'context' => $context,
        ];
    };

    $respondWithPayload = function(array $payload, array $debug = []) use ($wrapChatbotResponse): void {
        ob_clean();
        $response = $wrapChatbotResponse($payload);
        if (!empty($debug)) {
            $response['debug'] = $debug;
        }
        echo json_encode($response);
        exit;
    };

    $callAiApi = function(array $messages, bool $forceJson = false, array $extraPayload = []) use ($apiKey, $apiUrl, $model, $appName, $getTextStats): array {
        if (!function_exists('curl_init')) {
            return [
                'error' => ['message' => 'Hosting hien khong ho tro CURL.'],
                '_provider' => ['status' => 'transport_error'],
            ];
        }

        if ($apiKey === '' || $apiUrl === '' || $model === '') {
            return [
                'error' => ['message' => 'Thieu cau hinh AI.'],
                '_provider' => ['status' => 'config_error'],
            ];
        }

        $payload = array_merge(['model' => $model, 'messages' => $messages], $extraPayload);
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
            'X-Title: ' . $appName,
            'User-Agent: CrowneShop/1.0',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        $result = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return [
                'error' => ['message' => 'Loi ket noi may chu: ' . $error],
                '_provider' => [
                    'status' => 'curl_error',
                    'http_code' => $httpCode,
                    'curl_error' => $error,
                ],
            ];
        }

        curl_close($ch);
        $decoded = json_decode((string)$result, true);
        $providerMeta = [
            'status' => is_array($decoded) ? 'ok' : 'invalid_json_response',
            'http_code' => $httpCode,
            'raw_response_stats' => $getTextStats((string)$result),
            'raw_response_preview' => function_exists('mb_substr')
                ? mb_substr(trim((string)$result), 0, 1200, 'UTF-8')
                : substr(trim((string)$result), 0, 1200),
        ];

        if (is_array($decoded)) {
            $providerMeta['finish_reason'] = (string)($decoded['choices'][0]['finish_reason'] ?? '');
            $providerMeta['message_content_preview'] = function_exists('mb_substr')
                ? mb_substr(trim((string)($decoded['choices'][0]['message']['content'] ?? '')), 0, 800, 'UTF-8')
                : substr(trim((string)($decoded['choices'][0]['message']['content'] ?? '')), 0, 800);
            if (isset($decoded['error'])) {
                $providerMeta['status'] = 'provider_error';
                $providerMeta['error'] = $decoded['error'];
            }
            $decoded['_provider'] = $providerMeta;
            return $decoded;
        }

        return [
            'error' => ['message' => 'Phan hoi AI khong hop le.'],
            '_provider' => $providerMeta,
        ];
    };

    $extractAiPayloadFromContent = function(string $rawContent, array $fallbackPayload): array {
        $content = trim($rawContent);
        $content = preg_replace('/^```json\\s*|\\s*```$/s', '', $content);
        $content = preg_replace('/^```\\s*|\\s*```$/s', '', (string)$content);
        $content = trim((string)$content);

        $jsonCandidate = $content;
        $start = strpos($jsonCandidate, '{');
        $end = strrpos($jsonCandidate, '}');
        if ($start !== false && $end !== false && $end >= $start) {
            $jsonCandidate = substr($jsonCandidate, $start, $end - $start + 1);
        }
        $jsonCandidate = trim((string)$jsonCandidate);

        if ($jsonCandidate !== '') {
            $decoded = json_decode($jsonCandidate, true);
            if (is_array($decoded)) {
                $payload = $fallbackPayload;
                $reply = trim((string)($decoded['reply'] ?? ''));
                if ($reply !== '') {
                    $payload['reply'] = $reply;
                }
                if (array_key_exists('url', $decoded)) {
                    $payload['url'] = trim((string)$decoded['url']);
                }
                if (isset($decoded['products']) && is_array($decoded['products'])) {
                    $payload['products'] = $decoded['products'];
                }
                if (isset($decoded['invoice']) && is_array($decoded['invoice'])) {
                    $payload['invoice'] = $decoded['invoice'];
                }
                if (trim((string)($payload['reply'] ?? '')) !== '') {
                    return ['status' => 'json_object', 'payload' => $payload, 'cleaned_content' => $jsonCandidate];
                }
            }
        }

        $replyLines = [];
        $url = '';
        $lines = preg_split('/\R/u', $content, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^(?:url|link)\s*:\s*(.+)$/i', $line, $match)) {
                if ($url === '') {
                    $url = trim((string)$match[1]);
                }
                continue;
            }
            if (preg_match('/^reply\s*:\s*(.+)$/i', $line, $match)) {
                $replyLines[] = trim((string)$match[1]);
                continue;
            }
            $replyLines[] = $line;
        }

        $reply = trim((string)(preg_replace('/\s+/u', ' ', implode(' ', $replyLines)) ?? ''));
        if ($reply !== '') {
            $payload = $fallbackPayload;
            $payload['reply'] = $reply;
            $payload['url'] = $url !== '' ? $url : trim((string)($payload['url'] ?? ''));
            return ['status' => 'plain_text', 'payload' => $payload, 'cleaned_content' => $reply];
        }

        return ['status' => 'empty', 'payload' => $fallbackPayload, 'cleaned_content' => $jsonCandidate !== '' ? $jsonCandidate : $content];
    };

    $inspectAiResponse = function(array $response) use ($extractAiPayloadFromContent): array {
        $providerDebug = is_array($response['_provider'] ?? null) ? $response['_provider'] : [];

        if (!is_array($response) || isset($response['error'])) {
            $providerDebug['status'] = $providerDebug['status'] ?? 'provider_error';
            $providerDebug['error'] = $response['error'] ?? null;
            return $providerDebug;
        }

        if (isset($response['choices'][0]['error']) && is_array($response['choices'][0]['error'])) {
            $providerDebug['status'] = 'choice_error';
            $providerDebug['error'] = $response['choices'][0]['error'];
            return $providerDebug;
        }

        if (!isset($response['choices'][0]['message']['content'])) {
            $providerDebug['status'] = 'missing_message_content';
            return $providerDebug;
        }

        $rawContent = trim((string)$response['choices'][0]['message']['content']);
        $parsedContent = $extractAiPayloadFromContent($rawContent, ['reply' => '', 'url' => '', 'products' => []]);
        $cleanedContent = trim((string)($parsedContent['cleaned_content'] ?? ''));

        $providerDebug['raw_content_preview'] = function_exists('mb_substr')
            ? mb_substr($rawContent, 0, 800, 'UTF-8')
            : substr($rawContent, 0, 800);
        $providerDebug['cleaned_content_preview'] = function_exists('mb_substr')
            ? mb_substr($cleanedContent, 0, 800, 'UTF-8')
            : substr($cleanedContent, 0, 800);

        if (in_array($parsedContent['status'], ['json_object', 'plain_text'], true)) {
            $providerDebug['status'] = 'ok';
            $providerDebug['parsed_payload_type'] = $parsedContent['status'];
            return $providerDebug;
        }

        if ($parsedContent['status'] === 'empty') {
            $providerDebug['status'] = 'missing_reply';
            return $providerDebug;
        }

        $providerDebug['status'] = 'invalid_payload';
        return $providerDebug;
    };

    $cleanAiJsonResponse = function(array $response) use ($wrapChatbotResponse): array {
        if (!is_array($response) || isset($response['error']) || !isset($response['choices'][0]['message']['content'])) {
            return $wrapChatbotResponse([
                'reply' => 'Da, he thong tu van dang ban mot chut. Anh/chi vui long thu lai sau giup em nhe.',
                'url' => '',
                'products' => [],
            ]);
        }

        $content = (string)$response['choices'][0]['message']['content'];
        $content = preg_replace('/^```json\\s*|\\s*```$/s', '', $content);
        $content = preg_replace('/^```\\s*|\\s*```$/s', '', (string)$content);
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start !== false && $end !== false && $end >= $start) {
            $content = substr($content, $start, $end - $start + 1);
        }

        $response['choices'][0]['message']['content'] = trim($content);
        return $response;
    };

    $decodeAiPayload = function(array $response, array $fallbackPayload) use ($extractAiPayloadFromContent): array {
        if (!is_array($response) || isset($response['error']) || !isset($response['choices'][0]['message']['content'])) {
            return $fallbackPayload;
        }

        $parsedContent = $extractAiPayloadFromContent((string)$response['choices'][0]['message']['content'], $fallbackPayload);
        if (!in_array($parsedContent['status'], ['json_object', 'plain_text'], true)) {
            return $fallbackPayload;
        }

        return $parsedContent['payload'];
    };

    $normalizeIntentText = function(string $text): string {
        $text = mb_strtolower(trim($text), 'UTF-8');
        if ($text === '') {
            return '';
        }

        $text = str_replace('đ', 'd', $text);
        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if (is_string($ascii) && $ascii !== '') {
                $text = $ascii;
            }
        }

        $text = preg_replace('/[^a-z0-9\\s#]+/i', ' ', $text) ?? $text;
        $text = preg_replace('/\\s+/u', ' ', $text) ?? $text;
        return trim($text);
    };

    $normalizeIntentText = function(string $text): string {
        $text = mb_strtolower(trim($text), 'UTF-8');
        if ($text === '') {
            return '';
        }

        $patterns = [
            '/[áàảãạăắằẳẵặâấầẩẫậ]/u' => 'a',
            '/[éèẻẽẹêếềểễệ]/u' => 'e',
            '/[íìỉĩị]/u' => 'i',
            '/[óòỏõọôốồổỗộơớờởỡợ]/u' => 'o',
            '/[úùủũụưứừửữự]/u' => 'u',
            '/[ýỳỷỹỵ]/u' => 'y',
            '/đ/u' => 'd',
        ];
        foreach ($patterns as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        if (function_exists('iconv')) {
            $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if (is_string($ascii) && $ascii !== '') {
                $text = $ascii;
            }
        }

        $text = preg_replace('/[^a-z0-9\\s#]+/i', ' ', $text) ?? $text;
        $text = preg_replace('/\\s+/u', ' ', $text) ?? $text;
        return trim($text);
    };

    $normalizeIntentText = function(string $text): string {
        $text = mb_strtolower(trim($text), 'UTF-8');
        if ($text === '') {
            return '';
        }

        $map = [
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
        ];
        $text = strtr($text, $map);
        $text = preg_replace('/[^a-z0-9\\s#]+/i', ' ', $text) ?? $text;
        $text = preg_replace('/\\s+/u', ' ', $text) ?? $text;
        return trim($text);
    };

    $containsNormalizedPhrase = function(string $normalizedText, array $phrases): bool {
        $haystack = ' ' . trim($normalizedText) . ' ';
        foreach ($phrases as $phrase) {
            $needle = trim((string)$phrase);
            if ($needle !== '' && str_contains($haystack, ' ' . $needle . ' ')) {
                return true;
            }
        }
        return false;
    };

    $decodePreviousAiContext = function(string $raw): array {
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [];
        }

        $products = [];
        if (isset($decoded['products']) && is_array($decoded['products'])) {
            foreach (array_slice($decoded['products'], 0, 6) as $product) {
                if (!is_array($product)) {
                    continue;
                }

                $products[] = [
                    'name' => trim((string)($product['name'] ?? '')),
                    'price_formatted' => trim((string)($product['price_formatted'] ?? '')),
                    'category_name' => trim((string)($product['category_name'] ?? '')),
                    'url' => trim((string)($product['url'] ?? '')),
                ];
            }
        }

        return [
            'reply' => trim((string)($decoded['reply'] ?? '')),
            'url' => trim((string)($decoded['url'] ?? '')),
            'products' => $products,
            'saved_at' => isset($decoded['saved_at']) ? (int)$decoded['saved_at'] : 0,
        ];
    };

    $buildPreviousAiSearchHint = function(array $memory): string {
        $parts = [];

        foreach (array_slice($memory['products'] ?? [], 0, 6) as $product) {
            $name = trim((string)($product['name'] ?? ''));
            $categoryName = trim((string)($product['category_name'] ?? ''));
            if ($name !== '') {
                $parts[] = $name;
            }
            if ($categoryName !== '') {
                $parts[] = $categoryName;
            }
        }

        $reply = trim((string)($memory['reply'] ?? ''));
        if ($reply !== '') {
            $parts[] = $reply;
        }

        $hint = trim(implode(' ', array_values(array_unique(array_filter($parts)))));
        if ($hint !== '' && mb_strlen($hint, 'UTF-8') > 240) {
            $hint = mb_substr($hint, 0, 240, 'UTF-8');
        }

        return $hint;
    };

    $buildPreviousAiAssistantMessage = function(array $memory): string {
        $parts = [];

        $reply = trim((string)($memory['reply'] ?? ''));
        if ($reply !== '') {
            $parts[] = $reply;
        }

        $productLabels = [];
        foreach (array_slice($memory['products'] ?? [], 0, 6) as $product) {
            $name = trim((string)($product['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $label = $name;
            $priceFormatted = trim((string)($product['price_formatted'] ?? ''));
            if ($priceFormatted !== '') {
                $label .= ' (' . $priceFormatted . ')';
            }
            $productLabels[] = $label;
        }

        if (!empty($productLabels)) {
            $parts[] = 'San pham vua goi y: ' . implode(', ', $productLabels) . '.';
        }

        return trim(implode("\n", $parts));
    };

    $shouldUsePreviousAiContext = function(string $text, array $memory) use ($normalizeIntentText, $containsNormalizedPhrase): bool {
        $normalized = $normalizeIntentText($text);
        if ($normalized === '') {
            return false;
        }

        $hasMemory = trim((string)($memory['reply'] ?? '')) !== '' || !empty($memory['products']);
        if (!$hasMemory) {
            return false;
        }

        $followUpPhrases = [
            'co', 'ok', 'oke', 'duoc', 'xem', 'xem di', 'cho xem', 'gui em xem', 'gui toi xem',
            'mau nao', 'mau do', 'mau nay', 'cai do', 'cai nay', 'san pham do', 'san pham nay',
            'size nao', 'mau gi', 'gia sao', 'gia bao nhieu', 'con khong', 'con hang khong',
            'co mau khac', 'mau khac', 're hon', 'them mau', 'them mau khac', 'chi tiet hon',
            'loai nay', 'lay mau nay', 'gui link', 'gui toi', 'gui em'
        ];

        if ($containsNormalizedPhrase($normalized, $followUpPhrases)) {
            return true;
        }

        $tokens = preg_split('/\s+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        return count($tokens) <= 2 && !preg_match('/\b(?:ao|quan|mu|non|giay|hoodie|phu kien|qua tang|luu niem|san pham|gift)\b/u', $normalized);
    };

    $parseBudgetValue = function(string $amount, string $unit = ''): ?int {
        $amount = str_replace(',', '.', trim($amount));
        if ($amount === '' || !is_numeric($amount)) {
            return null;
        }

        $value = (float)$amount;
        $unit = strtolower(trim($unit));
        if (in_array($unit, ['tr', 'trieu'], true)) {
            $value *= 1000000;
        } elseif (in_array($unit, ['k', 'nghin', 'ngan'], true) || ($unit === '' && $value >= 10 && $value < 1000)) {
            $value *= 1000;
        }

        return (int)round($value);
    };

    $extractBudgetFromText = function(string $text) use ($normalizeIntentText, $parseBudgetValue): array {
        $normalized = $normalizeIntentText($text);
        $budget = ['min_price' => null, 'max_price' => null, 'label' => ''];

        if (preg_match('/\\b(?:tu|trong khoang|khoang)\\s*(\\d+(?:[.,]\\d+)?)\\s*(trieu|tr|k|nghin|ngan)?\\b\\s*(?:den|toi|-)\\s*(\\d+(?:[.,]\\d+)?)\\s*(trieu|tr|k|nghin|ngan)?\\b/u', $normalized, $matches)) {
            $minPrice = $parseBudgetValue($matches[1] ?? '', $matches[2] ?? '');
            $maxPrice = $parseBudgetValue($matches[3] ?? '', $matches[4] ?? '');
            if ($minPrice !== null && $maxPrice !== null) {
                if ($minPrice > $maxPrice) {
                    [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
                }
                return ['min_price' => $minPrice, 'max_price' => $maxPrice, 'label' => 'tu ' . formatVND($minPrice) . ' den ' . formatVND($maxPrice)];
            }
        }

        if (preg_match('/\\b(?:duoi|toi da|khong qua|do lai|gia duoi|re hon)\\s*(\\d+(?:[.,]\\d+)?)\\s*(trieu|tr|k|nghin|ngan)?\\b/u', $normalized, $matches)) {
            $maxPrice = $parseBudgetValue($matches[1] ?? '', $matches[2] ?? '');
            if ($maxPrice !== null) {
                return ['min_price' => null, 'max_price' => $maxPrice, 'label' => 'duoi ' . formatVND($maxPrice)];
            }
        }

        if (preg_match('/\\b(?:tren|tu)\\s*(\\d+(?:[.,]\\d+)?)\\s*(trieu|tr|k|nghin|ngan)?\\b(?:\\s*(?:tro len|do len))?/u', $normalized, $matches)) {
            $minPrice = $parseBudgetValue($matches[1] ?? '', $matches[2] ?? '');
            if ($minPrice !== null) {
                return ['min_price' => $minPrice, 'max_price' => null, 'label' => 'tu ' . formatVND($minPrice) . ' tro len'];
            }
        }

        if (preg_match('/\\b(?:tam|khoang|gan|budget|ngan sach|gia)\\s*(\\d+(?:[.,]\\d+)?)\\s*(trieu|tr|k|nghin|ngan)?\\b/u', $normalized, $matches)) {
            $centerPrice = $parseBudgetValue($matches[1] ?? '', $matches[2] ?? '');
            if ($centerPrice !== null) {
                return [
                    'min_price' => (int)round($centerPrice * 0.85),
                    'max_price' => (int)round($centerPrice * 1.15),
                    'label' => 'quanh muc ' . formatVND($centerPrice),
                ];
            }
        }

        if (preg_match('/\\b(\\d+(?:[.,]\\d+)?)\\s*(trieu|tr|k|nghin|ngan)\\b/u', $normalized, $matches)) {
            $centerPrice = $parseBudgetValue($matches[1] ?? '', $matches[2] ?? '');
            if ($centerPrice !== null) {
                return [
                    'min_price' => (int)round($centerPrice * 0.85),
                    'max_price' => (int)round($centerPrice * 1.15),
                    'label' => 'quanh muc ' . formatVND($centerPrice),
                ];
            }
        }

        return $budget;
    };

    $extractOrderIdFromText = function(string $text) use ($normalizeIntentText): ?int {
        $normalized = $normalizeIntentText($text);
        if ($normalized === '') {
            return null;
        }
        if (preg_match('/\\b(?:ma|madon|don|donhang|hoa\\s*don|hoadon|invoice|order)\\D*0*(\\d{1,10})\\b/u', $normalized, $matches)) {
            return max(1, (int)$matches[1]);
        }
        return null;
    };

    $looksLikeOrderLookup = function(string $text) use ($normalizeIntentText, $extractOrderIdFromText): bool {
        $normalized = $normalizeIntentText($text);
        if ($normalized === '' || $extractOrderIdFromText($normalized) === null) {
            return false;
        }
        return (bool)preg_match('/\\b(?:ma|madon|don|donhang|hoa\\s*don|hoadon|invoice|order|tra\\s*cuu|kiem\\s*tra)\\b/u', $normalized);
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
        return $map[$paymentMethod] ?? strtoupper($paymentMethod !== '' ? $paymentMethod : 'cod');
    };

    $getOrderStatusMeta = function(?string $status): array {
        $status = strtolower(trim((string)$status));
        $map = [
            'paid' => ['label' => 'Đã thanh toán', 'tone' => 'success'],
            'pending' => ['label' => 'Chờ xử lý', 'tone' => 'warning'],
            'shipped' => ['label' => 'Đang giao', 'tone' => 'info'],
            'cancelled' => ['label' => 'Đã hủy', 'tone' => 'danger'],
        ];
        return $map[$status] ?? ['label' => ucfirst($status !== '' ? $status : 'Không xác định'), 'tone' => 'secondary'];
    };

    $buildInvoiceLookupPayload = function(int $orderId) use ($userId, $chatbotClosing, $getOrderStatusMeta, $getPaymentMethodLabel): array {
        $orderCode = '#' . str_pad((string)$orderId, 6, '0', STR_PAD_LEFT);
        if (!$userId) {
            return ['reply' => "Để tra cứu hóa đơn {$orderCode}, anh chị vui lòng đăng nhập trước giúp em.", 'url' => 'login.php?msg=auth', 'products' => [], 'invoice' => null];
        }

        $orders = getData('orders', ['where' => ['id' => $orderId, 'user_id' => $userId], 'limit' => 1]);
        if (empty($orders)) {
            return ['reply' => "Em chưa tìm thấy hóa đơn {$orderCode} trong tài khoản của anh/chị.\n\nAnh/chị vui lòng kiểm tra lại giúp em nhé", 'url' => '', 'products' => [], 'invoice' => null];
        }

        $order = $orders[0];
        $statusMeta = $getOrderStatusMeta($order['status'] ?? 'pending');
        $items = getData('order_items', ['where' => ['order_id' => $orderId], 'order_by' => 'id ASC']);
        $invoiceItems = [];
        $totalQuantity = 0;
        foreach ($items as $item) {
            $qty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
            $price = (float)($item['price'] ?? 0);
            $subtotal = $price * $qty;
            $totalQuantity += $qty;
            $invoiceItems[] = [
                'name' => (string)($item['product_name'] ?? $item['name'] ?? 'San pham'),
                'qty' => $qty,
                'price_formatted' => formatVND($price),
                'subtotal_formatted' => formatVND($subtotal),
            ];
        }

        return [
            'reply' => "Em đã tìm thấy hóa đơn {$orderCode}. Em gửi anh/chị bản tóm tắt nhanh ngày bên dưới để theo dõi nhanh nhé.",
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
                'customer_name' => trim((string)($order['name'] ?? $_SESSION['name'] ?? 'Khach hang')),
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
        $compact = preg_replace('/\\s+/u', '', $normalized) ?? $normalized;
        return (bool)preg_match('/^(?:xinchao|xinchaoshop|chao|chaoshop|hello|helloshop|hi|hishop|hey|heyshop|alo|aloshop)$/u', $compact);
    };

    $shouldUseProductTool = function(string $text) use ($normalizeIntentText, $isGreetingOnly): bool {
        $normalized = $normalizeIntentText($text);
        if ($normalized === '' || $isGreetingOnly($normalized)) {
            return false;
        }
        return (bool)preg_match('/\\b(tim|kiem|mua|xem|goi y|tu van|san pham|ao|quan|mu|non|giay|hoodie|phu kien|qua tang|luu niem|size|mau|gia|duoi|tren|be gai|be trai|nu|nam|gia dinh|family|cap doi|couple)\\b/u', $normalized);
    };

    $cleanupColorList = function(array $colors): array {
        $colors = array_values(array_unique($colors));
        if (in_array('xanh duong', $colors, true) || in_array('xanh la', $colors, true)) {
            $colors = array_values(array_diff($colors, ['xanh']));
        }
        return $colors;
    };

    $normalizeSizeToken = function(string $size): string {
        $size = strtoupper(str_replace([' ', '-'], '', trim($size)));
        return match ($size) {
            'FREE', 'FREESIZE' => 'FREESIZE',
            '2XL' => 'XXL',
            '3XL', 'XXX' => 'XXXL',
            default => $size,
        };
    };

    $extractSizeTokensFromText = function(string $text) use ($normalizeSizeToken): array {
        $tokens = [];
        if (trim($text) === '') {
            return $tokens;
        }

        $sizePattern = '/\\b(3xl|2xl|xxxl|xxx|xxl|xl|xs|s|m|l|freesize|free\\s*size|free)\\b/ui';
        if (preg_match_all('/\\b(?:size|sz)\\s*(3xl|2xl|xxxl|xxx|xxl|xl|xs|s|m|l|freesize|free\\s*size|free)\\b/ui', $text, $matches)) {
            foreach ($matches[1] as $match) {
                $tokens[] = $normalizeSizeToken((string)$match);
            }
        }
        if (preg_match_all($sizePattern, $text, $matches)) {
            foreach ($matches[1] as $match) {
                $tokens[] = $normalizeSizeToken((string)$match);
            }
        }

        return array_values(array_unique(array_filter($tokens)));
    };

    $buildDescriptionAttributeGroups = function(array $intent) use ($normalizeIntentText, $containsNormalizedPhrase): array {
        $groups = [];
        $normalizedSearch = $normalizeIntentText((string)($intent['search'] ?? ''));

        $typeKeywordMap = [
            'ao' => ['ao', 'hoodie', 'shirt', 'thun'],
            'quan' => ['quan', 'jean'],
            'mu' => ['mu', 'non', 'hat', 'cap'],
            'giay' => ['giay', 'sneaker', 'shoe'],
            'phu kien' => ['phu kien', 'vong', 'day', 'moc treo'],
            'qua tang' => ['qua tang', 'gift'],
            'luu niem' => ['luu niem', 'souvenir'],
            'set' => ['set', 'bo', 'combo'],
        ];
        $typeDescriptionTerms = [
            'ao' => ['ao', 'áo', 'hoodie', 'shirt', 'thun'],
            'quan' => ['quan', 'quần', 'jean'],
            'mu' => ['mu', 'mũ', 'non', 'nón', 'hat', 'cap'],
            'giay' => ['giay', 'giày', 'sneaker', 'shoe'],
            'phu kien' => ['phu kien', 'phụ kiện', 'vong', 'vòng', 'day', 'dây', 'moc treo', 'móc treo'],
            'qua tang' => ['qua tang', 'quà tặng', 'gift'],
            'luu niem' => ['luu niem', 'lưu niệm', 'souvenir'],
            'set' => ['set', 'bo', 'bộ', 'combo'],
        ];

        $typeTerms = [];
        foreach ($typeKeywordMap as $key => $aliases) {
            if ($containsNormalizedPhrase($normalizedSearch, $aliases)) {
                $typeTerms = array_merge($typeTerms, $typeDescriptionTerms[$key] ?? []);
            }
        }
        if (!empty($typeTerms)) {
            $groups[] = [
                'key' => 'type',
                'sql_terms' => array_values(array_unique($typeTerms)),
                'normalized_terms' => array_values(array_unique(array_filter(array_map($normalizeIntentText, $typeTerms)))),
            ];
        }

        $colorDescriptionTerms = [
            'do' => ['do', 'đỏ'],
            'vang' => ['vang', 'vàng'],
            'den' => ['den', 'đen'],
            'trang' => ['trang', 'trắng'],
            'xanh la' => ['xanh la', 'xanh lá'],
            'xanh duong' => ['xanh duong', 'xanh dương'],
            'xanh' => ['xanh'],
            'hong' => ['hong', 'hồng'],
            'tim' => ['tim', 'tím'],
            'nau' => ['nau', 'nâu'],
            'xam' => ['xam', 'xám', 'gray', 'grey'],
            'be' => ['be', 'kem'],
        ];
        $colorTerms = [];
        foreach ((array)($intent['colors'] ?? []) as $color) {
            $colorTerms = array_merge($colorTerms, $colorDescriptionTerms[(string)$color] ?? [(string)$color]);
        }
        if (!empty($colorTerms)) {
            $groups[] = [
                'key' => 'color',
                'sql_terms' => array_values(array_unique($colorTerms)),
                'normalized_terms' => array_values(array_unique(array_filter(array_map($normalizeIntentText, $colorTerms)))),
            ];
        }

        $sizeDescriptionTerms = [
            'XS' => ['size xs', 'sz xs', 'xs'],
            'S' => ['size s', 'sz s', 's'],
            'M' => ['size m', 'sz m', 'm'],
            'L' => ['size l', 'sz l', 'l'],
            'XL' => ['size xl', 'sz xl', 'xl'],
            'XXL' => ['size xxl', 'sz xxl', 'xxl', 'size 2xl', '2xl'],
            'XXXL' => ['size xxxl', 'sz xxxl', 'xxxl', 'size 3xl', '3xl', 'size xxx', 'xxx'],
            'FREESIZE' => ['freesize', 'free size', 'size free', 'free'],
        ];
        $sizeTerms = [];
        $sizeSqlTerms = [];
        foreach ((array)($intent['size_request'] ?? []) as $size) {
            $sizeTerms = array_merge($sizeTerms, $sizeDescriptionTerms[(string)$size] ?? [(string)$size]);
            $sizeSqlTerms = array_merge($sizeSqlTerms, array_values(array_filter($sizeDescriptionTerms[(string)$size] ?? [(string)$size], static function(string $term): bool {
                return mb_strlen(trim($term), 'UTF-8') > 1;
            })));
        }
        if (!empty($sizeTerms)) {
            $groups[] = [
                'key' => 'size',
                'sql_terms' => array_values(array_unique(!empty($sizeSqlTerms) ? $sizeSqlTerms : $sizeTerms)),
                'normalized_terms' => array_values(array_unique(array_filter(array_map($normalizeIntentText, $sizeTerms)))),
            ];
        }

        $audienceDescriptionTerms = [
            'nu' => ['nu', 'nữ', 'cho nu', 'cho nữ', 'phai nu', 'phái nữ', 'be gai', 'bé gái', 'con gai', 'con gái'],
            'nam' => ['nam', 'cho nam', 'phái nam', 'phai nam', 'be trai', 'bé trai', 'con trai'],
            'cap doi' => ['cap doi', 'cặp đôi', 'couple'],
            'gia dinh' => ['gia dinh', 'gia đình', 'family', 'ca nha', 'cả nhà'],
        ];
        $audience = (string)($intent['audience'] ?? '');
        if ($audience !== '' && isset($audienceDescriptionTerms[$audience])) {
            $groups[] = [
                'key' => 'audience',
                'sql_terms' => $audienceDescriptionTerms[$audience],
                'normalized_terms' => array_values(array_unique(array_filter(array_map($normalizeIntentText, $audienceDescriptionTerms[$audience])))),
            ];
        }

        return array_values(array_filter($groups, static function(array $group): bool {
            return !empty($group['sql_terms']) && !empty($group['normalized_terms']);
        }));
    };

    $shouldUseDescriptionAttributeTool = function(string $text, array $intent) use ($normalizeIntentText): bool {
        $normalized = $normalizeIntentText($text);
        if ($normalized === '' || !preg_match('/\b(?:tim|kiem|xem|mua)\b/u', $normalized)) {
            return false;
        }

        return !empty($intent['colors'])
            || !empty($intent['size_request'])
            || in_array((string)($intent['audience'] ?? ''), ['nu', 'nam', 'gia dinh', 'cap doi'], true);
    };

    $queryProductsByDescriptionAttributes = function(array $intent, int $limit = 6) use ($conn, $buildDescriptionAttributeGroups, $normalizeIntentText, $containsNormalizedPhrase): array {
        $limit = max(1, min(6, $limit));
        $groups = $buildDescriptionAttributeGroups($intent);
        if (empty($groups) || !hasTable('products')) {
            return [
                'products' => [],
                'total' => 0,
                'limit' => $limit,
                'applied_filters' => ['description_tool' => true, 'groups' => []],
            ];
        }

        $hasCategories = hasTable('categories');
        $hasEvents = hasTable('events');
        $hasEventColumn = hasProductEventColumn();

        $categorySelect = $hasCategories ? 'c.name AS category_name' : "NULL AS category_name";
        $categoryJoin = $hasCategories ? 'LEFT JOIN categories c ON c.id = p.category_id' : '';
        $eventSelect = ($hasEvents && $hasEventColumn) ? 'e.name AS event_name' : "NULL AS event_name";
        $eventJoin = ($hasEvents && $hasEventColumn) ? 'LEFT JOIN events e ON e.slug = p.event_slug' : '';

        $whereParts = ['p.stock > 0'];
        $params = [];
        $appliedEventSlug = '';

        if ($hasEventColumn) {
            $activeEventSlug = getActiveSaleEventSlug();
            if (empty($activeEventSlug)) {
                return [
                    'products' => [],
                    'total' => 0,
                    'limit' => $limit,
                    'applied_filters' => ['description_tool' => true, 'groups' => array_column($groups, 'key')],
                ];
            }

            $appliedEventSlug = $activeEventSlug;
            $whereParts[] = 'p.event_slug = :event_slug';
            $params[':event_slug'] = $activeEventSlug;
        }

        if (($intent['min_price'] ?? null) !== null) {
            $whereParts[] = 'p.price >= :min_price';
            $params[':min_price'] = (float)$intent['min_price'];
        }
        if (($intent['max_price'] ?? null) !== null) {
            $whereParts[] = 'p.price <= :max_price';
            $params[':max_price'] = (float)$intent['max_price'];
        }

        foreach ($groups as $groupIndex => $group) {
            $clauses = [];
            foreach ($group['sql_terms'] as $termIndex => $term) {
                $paramKey = ":desc_tool_{$groupIndex}_{$termIndex}";
                $clauses[] = "LOWER(COALESCE(p.description, '')) LIKE LOWER($paramKey)";
                $params[$paramKey] = '%' . trim((string)$term) . '%';
            }
            if (!empty($clauses)) {
                $whereParts[] = '(' . implode(' OR ', $clauses) . ')';
            }
        }

        $whereSql = ' WHERE ' . implode(' AND ', $whereParts);
        $sqlLimit = min(60, max($limit * 8, 24));

        try {
            $sql = "SELECT p.*, $categorySelect, $eventSelect
                    FROM products p
                    $categoryJoin
                    $eventJoin
                    $whereSql
                    ORDER BY p.stock DESC, p.created_at DESC
                    LIMIT $sqlLimit";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            if (isMissingTableError($e)) {
                $rows = [];
            } else {
                throw $e;
            }
        }

        $filteredRows = [];
        foreach ($rows as $row) {
            $normalizedDescription = $normalizeIntentText((string)($row['description'] ?? ''));
            if ($normalizedDescription === '') {
                continue;
            }

            $matchedAllGroups = true;
            foreach ($groups as $group) {
                if (!$containsNormalizedPhrase($normalizedDescription, $group['normalized_terms'])) {
                    $matchedAllGroups = false;
                    break;
                }
            }

            if ($matchedAllGroups) {
                $filteredRows[] = $row;
            }
        }

        $formattedProducts = [];
        foreach (array_slice($filteredRows, 0, $limit) as $product) {
            $description = trim((string)($product['description'] ?? ''));
            $description = preg_replace('/\s+/u', ' ', $description ?? '');
            if ($description !== '' && mb_strlen($description, 'UTF-8') > 140) {
                $description = mb_substr($description, 0, 137, 'UTF-8') . '...';
            }

            $formattedProducts[] = [
                'id' => (int)($product['id'] ?? 0),
                'name' => (string)($product['name'] ?? ''),
                'description' => $description,
                'price' => (float)($product['price'] ?? 0),
                'price_formatted' => formatVND($product['price'] ?? 0),
                'stock' => (int)($product['stock'] ?? 0),
                'image' => (string)($product['image'] ?? ''),
                'category_id' => isset($product['category_id']) ? (int)$product['category_id'] : null,
                'category_name' => (string)($product['category_name'] ?? ''),
                'event_slug' => (string)($product['event_slug'] ?? ''),
                'event_name' => (string)($product['event_name'] ?? ''),
                'url' => 'product.php?id=' . (int)($product['id'] ?? 0),
            ];
        }

        return [
            'products' => $formattedProducts,
            'total' => count($formattedProducts),
            'limit' => $limit,
            'applied_filters' => [
                'description_tool' => true,
                'event_slug' => $appliedEventSlug !== '' ? $appliedEventSlug : null,
                'groups' => array_map(static function(array $group): array {
                    return [
                        'key' => (string)($group['key'] ?? ''),
                        'terms' => array_values($group['normalized_terms'] ?? []),
                    ];
                }, $groups),
            ],
        ];
    };

    $extractProductIntent = function(string $text) use ($normalizeIntentText, $extractBudgetFromText, $containsNormalizedPhrase, $cleanupColorList, $extractSizeTokensFromText): array {
        $normalized = $normalizeIntentText($text);
        $normalizedForAttributes = preg_replace(
            '/^tim\s+(?=(?:ao|quan|mu|non|giay|hoodie|phu|qua|san|do|size|mau|gia)\b)/u',
            '',
            $normalized,
            1
        ) ?? $normalized;
        $budget = $extractBudgetFromText($text);
        $audience = '';
        if ($containsNormalizedPhrase($normalizedForAttributes, ['be gai', 'con gai', 'ban gai', 'phai nu', 'cho nu', 'nu', 'vo', 'me'])) {
            $audience = 'nu';
        } elseif ($containsNormalizedPhrase($normalizedForAttributes, ['be trai', 'con trai', 'ban trai', 'phai nam', 'cho nam', 'nam', 'bo', 'chong'])) {
            $audience = 'nam';
        } elseif ($containsNormalizedPhrase($normalizedForAttributes, ['cap doi', 'couple'])) {
            $audience = 'cap doi';
        } elseif ($containsNormalizedPhrase($normalizedForAttributes, ['gia dinh', 'family'])) {
            $audience = 'gia dinh';
        }

        $colorMap = [
            'do' => ['do'], 'vang' => ['vang'], 'den' => ['den'], 'trang' => ['trang'],
            'xanh la' => ['xanh la'], 'xanh duong' => ['xanh duong'], 'xanh' => ['xanh'],
            'hong' => ['hong'], 'tim' => ['tim'], 'nau' => ['nau'], 'xam' => ['xam'],
            'be' => ['be', 'kem'],
        ];
        $colors = [];
        foreach ($colorMap as $label => $aliases) {
            if ($containsNormalizedPhrase($normalizedForAttributes, $aliases)) {
                $colors[] = $label;
            }
        }
        $colors = $cleanupColorList($colors);

        $sizeRequest = $extractSizeTokensFromText($normalizedForAttributes);

        $bodyHints = [];
        if (preg_match('/\\b(\\d{2,3})\\s*kg\\b/u', $normalizedForAttributes, $match)) {
            $bodyHints[] = $match[1] . 'kg';
        }
        if (preg_match('/\\b1m(\\d{1,2})\\b/u', $normalizedForAttributes, $match)) {
            $bodyHints[] = '1m' . $match[1];
        } elseif (preg_match('/\\b(\\d{3})\\s*cm\\b/u', $normalizedForAttributes, $match)) {
            $bodyHints[] = $match[1] . 'cm';
        }

        return [
            'search' => $text,
            'min_price' => $budget['min_price'],
            'max_price' => $budget['max_price'],
            'budget_label' => $budget['label'],
            'audience' => $audience,
            'colors' => $colors,
            'size_request' => array_values(array_unique($sizeRequest)),
            'body_note' => implode(', ', array_values(array_unique($bodyHints))),
        ];
    };

    $isSpecificProductIntent = function(array $intent) use ($normalizeIntentText): bool {
        if (($intent['min_price'] ?? null) !== null || ($intent['max_price'] ?? null) !== null) {
            return true;
        }
        if (($intent['audience'] ?? '') !== '' || !empty($intent['colors']) || !empty($intent['size_request']) || ($intent['body_note'] ?? '') !== '') {
            return true;
        }

        $normalizedSearch = $normalizeIntentText((string)($intent['search'] ?? ''));
        if ($normalizedSearch === '') {
            return false;
        }

        return (bool)preg_match('/\b(?:size|mau|gia|kg|cm|1m|\d+)\b/u', $normalizedSearch);
    };

    $extractProductTraits = function(array $product) use ($normalizeIntentText, $containsNormalizedPhrase, $cleanupColorList, $extractSizeTokensFromText): array {
        $name = trim((string)($product['name'] ?? ''));
        $description = trim((string)($product['description'] ?? ''));
        $categoryName = trim((string)($product['category_name'] ?? ''));
        $combined = trim($name . ' ' . $description . ' ' . $categoryName);
        $normalized = $normalizeIntentText($combined);

        $type = $categoryName;
        if ($type === '') {
            $typeMap = [
                'Ao' => ['ao', 'hoodie', 'shirt', 'thun'],
                'Quan' => ['quan', 'jean'],
                'Mu' => ['mu', 'non', 'hat', 'cap'],
                'Giay' => ['giay', 'sneaker', 'shoe'],
                'Phu kien' => ['phu kien', 'vong', 'day', 'moc treo'],
                'Qua tang' => ['qua tang', 'gift'],
                'Do luu niem' => ['luu niem', 'souvenir'],
            ];
            foreach ($typeMap as $label => $aliases) {
                if ($containsNormalizedPhrase($normalized, $aliases)) {
                    $type = $label;
                    break;
                }
            }
        }

        $audience = '';
        if ($containsNormalizedPhrase($normalized, ['be gai', 'con gai', 'ban gai', 'phai nu', 'cho nu', 'nu', 'vo', 'me'])) {
            $audience = 'nu';
        } elseif ($containsNormalizedPhrase($normalized, ['be trai', 'con trai', 'ban trai', 'phai nam', 'cho nam', 'nam', 'bo', 'chong'])) {
            $audience = 'nam';
        } elseif ($containsNormalizedPhrase($normalized, ['cap doi', 'couple'])) {
            $audience = 'cap doi';
        } elseif ($containsNormalizedPhrase($normalized, ['gia dinh', 'family'])) {
            $audience = 'gia dinh';
        }

        $colorMap = [
            'do' => ['do'], 'vang' => ['vang'], 'den' => ['den'], 'trang' => ['trang'],
            'xanh la' => ['xanh la'], 'xanh duong' => ['xanh duong'], 'xanh' => ['xanh'],
            'hong' => ['hong'], 'tim' => ['tim'], 'nau' => ['nau'], 'xam' => ['xam'],
            'be' => ['be', 'kem'],
        ];
        $colors = [];
        foreach ($colorMap as $label => $aliases) {
            if ($containsNormalizedPhrase($normalized, $aliases)) {
                $colors[] = $label;
            }
        }
        $colors = $cleanupColorList($colors);

        $sizeTokens = $extractSizeTokensFromText($combined);

        $fitTokens = [];
        if (preg_match_all('/\\b\\d{2,3}\\s*kg\\b(?:\\s*(?:-|den)\\s*\\d{2,3}\\s*kg\\b)?/ui', $combined, $matches)) {
            foreach ($matches[0] as $match) {
                $fitTokens[] = trim((string)$match);
            }
        }
        if (preg_match_all('/\\b1m\\d{1,2}\\b(?:\\s*(?:-|den)\\s*1m\\d{1,2}\\b)?/ui', $combined, $matches)) {
            foreach ($matches[0] as $match) {
                $fitTokens[] = trim((string)$match);
            }
        }
        if (preg_match_all('/\\b\\d{3}\\s*cm\\b/ui', $combined, $matches)) {
            foreach ($matches[0] as $match) {
                $fitTokens[] = trim((string)$match);
            }
        }

        $summary = preg_replace('/\\s+/u', ' ', $description !== '' ? $description : $name) ?? '';
        $summary = trim((string)$summary);
        if ($summary !== '' && mb_strlen($summary, 'UTF-8') > 140) {
            $summary = mb_substr($summary, 0, 137, 'UTF-8') . '...';
        }

        return [
            'type' => $type !== '' ? $type : 'San pham',
            'audience' => $audience,
            'colors' => $colors,
            'size_tokens' => $sizeTokens,
            'size_note' => implode(', ', array_values(array_unique($sizeTokens))),
            'fit_note' => implode(', ', array_slice(array_values(array_unique($fitTokens)), 0, 3)),
            'summary' => $summary,
        ];
    };

    $rankProductCandidates = function(array $products, array $intent) use ($extractProductTraits): array {
        $rankedProducts = [];
        foreach ($products as $index => $product) {
            $traits = $extractProductTraits($product);
            $score = max(0, 4 - $index);
            if ($intent['audience'] !== '') {
                if ($traits['audience'] === $intent['audience']) {
                    $score += 6;
                } elseif ($traits['audience'] !== '' && $traits['audience'] !== $intent['audience'] && !in_array($traits['audience'], ['cap doi', 'gia dinh'], true)) {
                    $score -= 2;
                }
            }
            if (!empty($intent['colors']) && !empty($traits['colors'])) {
                $score += count(array_intersect($intent['colors'], $traits['colors'])) * 2;
            }
            if (!empty($intent['size_request']) && !empty($traits['size_tokens'])) {
                $score += count(array_intersect($intent['size_request'], $traits['size_tokens'])) * 3;
            }
            $product['_traits'] = $traits;
            $product['_score'] = $score;
            $rankedProducts[] = $product;
        }

        usort($rankedProducts, static function(array $left, array $right): int {
            $scoreCompare = ((int)($right['_score'] ?? 0)) <=> ((int)($left['_score'] ?? 0));
            if ($scoreCompare !== 0) {
                return $scoreCompare;
            }
            return ((float)($left['price'] ?? 0)) <=> ((float)($right['price'] ?? 0));
        });

        return $rankedProducts;
    };

    $stripInternalProductFields = function(array $product): array {
        unset($product['_traits'], $product['_score']);
        return $product;
    };

    $buildProductFallbackPayload = function(array $products, array $intent) use ($chatbotClosing, $stripInternalProductFields): array {
        $clientProducts = array_map($stripInternalProductFields, $products);
        if (empty($products)) {
            $budgetNote = $intent['budget_label'] !== '' ? ' trong khoảng giá ' . $intent['budget_label'] : '';
            return [
                'reply' => "Dạ, bên em chưa thấy mẫu nào thật sự khớp{$budgetNote} với nhu cầu anh/chị vừa nhắn. Anh/chị có thể đổi thêm kiểu dáng hoặc tầm giá để em lọc sát hơn nhé.\n\nAnh/chị còn cần em hỗ trợ thêm gì không ạ?",
                'url' => '',
                'products' => [],
            ];
        }

        $bestProduct = $products[0];
        $bestTraits = $bestProduct['_traits'] ?? [];
        $detailUrl = count($clientProducts) === 1 ? trim((string)($clientProducts[0]['url'] ?? '')) : '';
        $replyParts = [];
        $opening = 'Dạ em thấy' . trim((string)($bestProduct['name'] ?? 'mẫu này')) . ' khá hợp với nhu cầu của anh/chị';
        if ($intent['audience'] !== '' && ($bestTraits['audience'] ?? '') === $intent['audience']) {
            $opening .= ' vì mẫu này thiên về ' . $intent['audience'];
        } elseif (($bestTraits['type'] ?? '') !== '') {
            $opening .= ' nếu anh/chị đang tìm ' . mb_strtolower((string)$bestTraits['type'], 'UTF-8');
        }
        $replyParts[] = $opening . '.';
        if (!empty($bestProduct['price_formatted'])) {
            $replyParts[] = 'Giá em nay khoảng ' . trim((string)$bestProduct['price_formatted']) . '.';
        }
        if (($bestTraits['fit_note'] ?? '') !== '') {
            $replyParts[] = 'Phan mo ta shop dang co goi y form/co ' . trim((string)$bestTraits['fit_note']) . '.';
        } elseif (($bestTraits['size_note'] ?? '') !== '') {
            $replyParts[] = 'Mau nay dang co thong tin size ' . trim((string)$bestTraits['size_note']) . '.';
        } elseif (($bestTraits['summary'] ?? '') !== '') {
            $replyParts[] = trim((string)$bestTraits['summary']) . '.';
        }
        if (count($products) > 1) {
            $replyParts[] = 'Ngoai ra em de them ' . (count($products) - 1) . ' mau gan nhu cau de anh/chi xem nhanh nhe.';
        }

        return ['reply' => implode(' ', $replyParts) . "\n\n{$chatbotClosing}", 'url' => $detailUrl, 'products' => $clientProducts];
    };

    $buildProductAiContext = function(array $products, array $intent, string $customerRequest) use ($stripInternalProductFields): array {
        $contextProducts = [];
        foreach ($products as $product) {
            $traits = $product['_traits'] ?? [];
            $signals = [];
            if (($traits['audience'] ?? '') !== '') {
                $signals[] = 'phu hop ' . $traits['audience'];
            }
            if (!empty($traits['colors'])) {
                $signals[] = 'mau ' . implode(', ', $traits['colors']);
            }
            if (($traits['size_note'] ?? '') !== '') {
                $signals[] = 'size ' . $traits['size_note'];
            }
            if (($traits['fit_note'] ?? '') !== '') {
                $signals[] = 'form/co ' . $traits['fit_note'];
            }

            $safeProduct = $stripInternalProductFields($product);
            $contextProducts[] = [
                'name' => (string)($safeProduct['name'] ?? ''),
                'type' => (string)($traits['type'] ?? ($safeProduct['category_name'] ?? 'San pham')),
                'price' => (string)($safeProduct['price_formatted'] ?? ''),
                'summary' => (string)($traits['summary'] ?? ''),
                'signals' => $signals,
            ];
        }

        $preferences = array_filter([
            'budget' => (string)($intent['budget_label'] ?? ''),
            'audience' => (string)($intent['audience'] ?? ''),
            'colors' => !empty($intent['colors']) ? implode(', ', $intent['colors']) : '',
            'body_note' => (string)($intent['body_note'] ?? ''),
            'size_request' => !empty($intent['size_request']) ? implode(', ', $intent['size_request']) : '',
        ]);

        return ['customer_request' => $customerRequest, 'parsed_preferences' => $preferences, 'products' => $contextProducts];
    };

    $previousAiContext = $decodePreviousAiContext($previousAiContextRaw);
    $usePreviousAiContext = $shouldUsePreviousAiContext($userMsg, $previousAiContext);
    $previousAiAssistantMessage = $usePreviousAiContext ? $buildPreviousAiAssistantMessage($previousAiContext) : '';
    $previousAiSearchHint = $usePreviousAiContext ? $buildPreviousAiSearchHint($previousAiContext) : '';

    $orderLookupId = $extractOrderIdFromText($userMsg);
    if ($orderLookupId !== null && $looksLikeOrderLookup($userMsg)) {
        $respondWithPayload($buildInvoiceLookupPayload($orderLookupId), [
            'mode' => 'invoice_lookup_tool_only',
            'tool_only' => true,
            'used_previous_ai_context' => $usePreviousAiContext,
        ]);
    }

    if ($shouldUseProductTool($userMsg)) {
        $intent = $extractProductIntent($userMsg);
        $isSpecificQuery = $isSpecificProductIntent($intent);
        $useDescriptionAttributeTool = $shouldUseDescriptionAttributeTool($userMsg, $intent);
        $searchQuery = trim(($previousAiSearchHint !== '' ? $previousAiSearchHint . ' ' : '') . $intent['search']);
        $usedAllEventsFallback = false;
        $productResult = $useDescriptionAttributeTool
            ? $queryProductsByDescriptionAttributes($intent, $isSpecificQuery ? 6 : 3)
            : getChatbotProductSuggestions([
                'search' => $searchQuery !== '' ? $searchQuery : $intent['search'],
                'limit' => $isSpecificQuery ? 6 : 3,
                'sort' => 'relevance',
                'only_in_stock' => true,
                'event_slug' => 'auto',
                'match_all_keywords' => false,
                'min_price' => $intent['min_price'],
                'max_price' => $intent['max_price'],
            ]);

        if (!$useDescriptionAttributeTool && empty($productResult['products'])) {
            $productResult = getChatbotProductSuggestions([
                'search' => $searchQuery !== '' ? $searchQuery : $intent['search'],
                'limit' => $isSpecificQuery ? 6 : 3,
                'sort' => 'relevance',
                'only_in_stock' => true,
                'event_slug' => 'all',
                'match_all_keywords' => false,
                'min_price' => $intent['min_price'],
                'max_price' => $intent['max_price'],
            ]);
            $usedAllEventsFallback = !empty($productResult['products']);
        }

        $rankedProducts = $rankProductCandidates(is_array($productResult['products'] ?? null) ? $productResult['products'] : [], $intent);
        $selectedProducts = $isSpecificQuery ? $rankedProducts : array_slice($rankedProducts, 0, 3);
        $fallbackPayload = $buildProductFallbackPayload($selectedProducts, $intent);
        if (empty($selectedProducts)) {
            $respondWithPayload($fallbackPayload, [
                'mode' => 'product_lookup_tool_only',
                'tool_only' => true,
                'search_query' => $searchQuery !== '' ? $searchQuery : $intent['search'],
                'used_description_attribute_tool' => $useDescriptionAttributeTool,
                'used_all_events_fallback' => $usedAllEventsFallback,
                'description_tool_filters' => $productResult['applied_filters'] ?? [],
                'is_specific_query' => $isSpecificQuery,
                'used_previous_ai_context' => $usePreviousAiContext,
                'selected_products' => [],
            ]);
        }

        $productAdvicePrompt = "Ban la tro ly tu van ban hang {$appName}. Chi tra loi tieng Viet tu nhien, rat ngan gon, than thien nhu nhan vien shop. Uu tien 1-2 cau ngan. Khong liet ke metadata noi bo, khong markdown, khong mo bai dai. Chi nhac gia/size/mau/gioi tinh neu that su huu ich. Neu chi co 1 san pham hop, them dong 'url: ...'. Uu tien tra 1 dong JSON ngan {\"reply\":\"...\",\"url\":\"\"}; neu khong the thi tra plain text ngan va dong url rieng.";
        $productMessages = [['role' => 'system', 'content' => $productAdvicePrompt]];
        if ($previousAiAssistantMessage !== '') {
            $productMessages[] = ['role' => 'assistant', 'content' => $previousAiAssistantMessage];
        }
        $productMessages[] = ['role' => 'user', 'content' => "Du lieu tu van da loc san:\n" . json_encode($buildProductAiContext($selectedProducts, $intent, $userMsg), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)];

        $productDebug = $buildAiDebug('product_lookup_ai', $productMessages, false, [], [
            'search_query' => $searchQuery !== '' ? $searchQuery : $intent['search'],
            'used_description_attribute_tool' => $useDescriptionAttributeTool,
            'used_all_events_fallback' => $usedAllEventsFallback,
            'description_tool_filters' => $productResult['applied_filters'] ?? [],
            'is_specific_query' => $isSpecificQuery,
            'selected_count' => count($selectedProducts),
            'used_previous_ai_context' => $usePreviousAiContext,
            'selected_products' => array_map(static function(array $product): array {
                return [
                    'name' => (string)($product['name'] ?? ''),
                    'price_formatted' => (string)($product['price_formatted'] ?? ''),
                ];
            }, $selectedProducts),
        ]);

        $productApiResponse = $callAiApi($productMessages, false);
        $productDebug['provider_debug'] = $inspectAiResponse($productApiResponse);
        if (($productDebug['provider_debug']['status'] ?? '') !== 'ok') {
            $respondWithPayload([
                'reply' => 'Da, he thong tu van dang ban mot chut. Anh/chi vui long thu lai sau giup em nhe.',
                'url' => '',
                'products' => [],
            ], $productDebug);
        }
        $productPayload = $decodeAiPayload($productApiResponse, $fallbackPayload);
        $productPayload['products'] = $fallbackPayload['products'];
        if (count($selectedProducts) === 1 && trim((string)($productPayload['url'] ?? '')) === '') {
            $productPayload['url'] = trim((string)($fallbackPayload['url'] ?? ''));
        }
        $respondWithPayload($productPayload, $productDebug);
    }

    $fallbackPayload = ['reply' => 'Da, em dang nghe anh/chi day. Anh/chi nhan them nhu cau cu the giup em de em tu van sat hon nhe.', 'url' => '', 'products' => []];
    $generalPrompt = "Ban la tro ly tu van ban hang {$appName}. Chi tra loi tieng Viet, ngan gon, than thien. Toi da 1-2 cau ngan, khong markdown. Uu tien 1 dong JSON ngan {\"reply\":\"...\",\"url\":\"\"}; neu khong the thi tra plain text ngan.";
    $generalMessages = [['role' => 'system', 'content' => $generalPrompt]];
    if ($previousAiAssistantMessage !== '') {
        $generalMessages[] = ['role' => 'assistant', 'content' => $previousAiAssistantMessage];
    }
    $generalMessages[] = ['role' => 'user', 'content' => $userMsg];

    $generalDebug = $buildAiDebug('general_ai', $generalMessages, false, [], [
        'used_previous_ai_context' => $usePreviousAiContext,
    ]);
    $generalApiResponse = $callAiApi($generalMessages, false);
    $generalDebug['provider_debug'] = $inspectAiResponse($generalApiResponse);
    $respondWithPayload(
        $decodeAiPayload($generalApiResponse, $fallbackPayload),
        $generalDebug
    );
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
        echo json_encode(['status' => 'error', 'message' => 'Báº¡n khÃ´ng cÃ³ quyá»n truy cáº­p.']);
        exit;
    }

    $targetUserId = $_POST['user_id'] ?? 0;
    $message = trim($_POST['message'] ?? '');
    if (!$targetUserId || $message === '') {
        echo json_encode(['status' => 'error']);
        exit;
    }

    $data = [
        'user_id' => $targetUserId,
        'message' => $message,
        'is_admin' => 1,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    echo json_encode(['status' => insertData('messages', $data) ? 'success' : 'error']);
}
?>
