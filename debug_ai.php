<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/vendor/autoload.php';

// Tải biến môi trường
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$apiKey = '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';
$apiUrl = $_ENV['OPENROUTER_API_URL'] ?? 'https://openrouter.ai/api/v1/chat/completions';
$model  = $_ENV['OPENROUTER_MODEL'] ?? 'nvidia/nemotron-3-super-120b-a12b:free';

$userMsg = $_POST['message'] ?? '';
$rawResponse = null;
$curlError = null;
$payloadJson = null;
$decodedResponse = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && trim($userMsg) !== '') {
    // Giả lập System Prompt giống trong api/process.php
    $systemPrompt = "Bạn là trợ lý tư vấn bán hàng " . ($_ENV['APP_NAME'] ?? 'Cửa hàng') . ". Chỉ trả lời tiếng Việt, ngắn gọn, thân thiện. Không bịa. Không dùng markdown. Trả về đúng chuẩn JSON: {\"reply\":\"...\",\"url\":\"\",\"products\":[]}";

    $messages = [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => trim($userMsg)]
    ];

    $payload = [
        'model' => $model,
        'messages' => $messages,
        'response_format' => ['type' => 'json_object'] // Ép định dạng JSON
    ];
    
    $payloadJson = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if (empty($apiKey)) {
        $curlError = "Thiếu cấu hình OPENROUTER_API_KEY trong file .env";
    } else {
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
            'HTTP-Referer: ' . ($_ENV['BASE_URL'] ?? 'http://localhost'),
            'X-Title: ' . ($_ENV['APP_NAME'] ?? 'Crowne')
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bỏ qua xác thực SSL local
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $rawResponse = curl_exec($ch);
        
        if (curl_errno($ch)) {
            $curlError = curl_error($ch);
        } else {
            $decodedResponse = json_decode($rawResponse, true);
        }
        curl_close($ch);
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug OpenRouter AI API</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre { background-color: #2b2b2b; color: #a9b7c6; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 14px; }
        .key-hidden { color: #cc7832; }
        .string { color: #6a8759; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="mb-4">🛠 Debug AI Chat (OpenRouter)</h2>
            
            <!-- Cấu hình hiện tại -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-dark text-white">Cấu hình API hiện tại</div>
                <div class="card-body">
                    <p class="mb-1"><strong>API URL:</strong> <code><?= htmlspecialchars($apiUrl) ?></code></p>
                    <p class="mb-1"><strong>Model:</strong> <code><?= htmlspecialchars($model) ?></code></p>
                    <p class="mb-0"><strong>API Key:</strong> <code><?= !empty($apiKey) ? substr($apiKey, 0, 10) . '...' . substr($apiKey, -4) : '<span class="text-danger">Chưa cấu hình</span>' ?></code></p>
                </div>
            </div>

            <!-- Form test -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nhập câu hỏi (Prompt):</label>
                            <input type="text" name="message" class="form-control form-control-lg" placeholder="Ví dụ: Tư vấn cho mình áo thun đi" value="<?= htmlspecialchars($userMsg) ?>" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-4">Gửi Request &rarr;</button>
                        <a href="debug_ai.php" class="btn btn-outline-secondary btn-lg ms-2">Làm mới</a>
                    </form>
                </div>
            </div>

            <!-- Kết quả xử lý -->
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                
                <?php if ($curlError): ?>
                    <div class="alert alert-danger shadow-sm">
                        <h5 class="alert-heading">🚨 Lỗi cURL / Hệ thống:</h5>
                        <p class="mb-0"><?= htmlspecialchars($curlError) ?></p>
                    </div>
                <?php endif; ?>

                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-info text-dark fw-bold">1. Payload gửi đi (JSON)</div>
                    <div class="card-body p-0">
                        <pre class="m-0 border-0 rounded-0"><?= htmlspecialchars($payloadJson) ?></pre>
                    </div>
                </div>

                <?php if ($rawResponse !== null): ?>
                    <div class="card mb-4 shadow-sm border-secondary">
                        <div class="card-header bg-secondary text-white fw-bold">2. Raw Response (Phản hồi gốc từ API)</div>
                        <div class="card-body p-0">
                            <?php
                                $formattedRaw = json_encode(json_decode($rawResponse), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                                if (!$formattedRaw) $formattedRaw = $rawResponse;
                            ?>
                            <pre class="m-0 border-0 rounded-0"><?= htmlspecialchars($formattedRaw) ?></pre>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($decodedResponse !== null): ?>
                    <div class="card shadow-sm border-success">
                        <div class="card-header bg-success text-white fw-bold">3. Trích xuất nội dung tin nhắn (Nội dung hiển thị trên UI)</div>
                        <div class="card-body p-0">
                            <?php
                                $content = $decodedResponse['choices'][0]['message']['content'] ?? 'Không tìm thấy key choices[0].message.content';
                            ?>
                            <pre class="m-0 border-0 rounded-0 text-white"><?= htmlspecialchars($content) ?></pre>
                        </div>
                    </div>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>
