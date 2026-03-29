<?php
declare(strict_types=1);

http_response_code(404);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require_once __DIR__ . '/config/database.php';

$pageTitle = '404';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$path = $_SERVER['REDIRECT_URL'] ?? ($_SERVER['REQUEST_URI'] ?? '/');
$query = $_SERVER['REDIRECT_QUERY_STRING'] ?? ($_SERVER['QUERY_STRING'] ?? '');

if ($query !== '') {
    $path .= '?' . $query;
}

$requestedUrl = $scheme . '://' . $host . $path;
$referer = $_SERVER['HTTP_REFERER'] ?? '';

include 'includes/header.php';
?>

<style>
    .theme-debug-corner {
        display: none !important;
    }

    .not-found-stage {
        position: relative;
        padding: clamp(3.5rem, 7vw, 6rem) 0;
        overflow: hidden;
        isolation: isolate;
    }

    .not-found-stage::before,
    .not-found-stage::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        filter: blur(10px);
        opacity: 0.7;
        z-index: -1;
    }

    .not-found-stage::before {
        width: 320px;
        height: 320px;
        top: 40px;
        left: -120px;
        background: radial-gradient(circle, rgba(197, 160, 89, 0.22), transparent 72%);
        animation: nfDrift 12s ease-in-out infinite;
    }

    .not-found-stage::after {
        width: 420px;
        height: 420px;
        right: -180px;
        bottom: -80px;
        background: radial-gradient(circle, rgba(17, 17, 17, 0.08), transparent 70%);
        animation: nfDrift 15s ease-in-out infinite reverse;
    }

    .not-found-panel {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 249, 250, 0.9));
        border: 1px solid rgba(197, 160, 89, 0.18);
        box-shadow: 0 28px 80px rgba(17, 17, 17, 0.08);
        padding: clamp(1.5rem, 3vw, 3rem);
        overflow: hidden;
        position: relative;
    }

    .not-found-panel::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, rgba(197, 160, 89, 0.08), transparent 34%),
            linear-gradient(320deg, rgba(17, 17, 17, 0.04), transparent 40%);
        pointer-events: none;
    }

    .not-found-copy {
        position: relative;
        z-index: 1;
    }

    .not-found-kicker {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        margin-bottom: 1rem;
        background: rgba(197, 160, 89, 0.1);
        color: var(--primary-color);
        letter-spacing: 0.16em;
        text-transform: uppercase;
        font-size: 0.78rem;
    }

    .not-found-kicker i {
        color: var(--accent-color);
    }

    .not-found-title {
        font-family: var(--font-heading);
        font-size: clamp(2.4rem, 5vw, 4.6rem);
        line-height: 1.05;
        margin-bottom: 1.25rem;
    }

    .not-found-desc {
        max-width: 520px;
        color: var(--secondary-color);
        font-size: 1.02rem;
        margin-bottom: 2rem;
    }

    .not-found-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
    }

    .not-found-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        border: 1px solid rgba(17, 17, 17, 0.16);
        color: var(--primary-color);
        text-decoration: none;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }

    .not-found-secondary:hover {
        transform: translateY(-2px);
        border-color: var(--accent-color);
        color: var(--primary-color);
    }

    .not-found-meta {
        margin-top: 1.5rem;
        color: var(--secondary-color);
        font-size: 0.95rem;
        max-width: 520px;
    }

    .not-found-visual {
        position: relative;
        min-height: 460px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .not-found-ring {
        position: absolute;
        border-radius: 50%;
        border: 1px solid rgba(17, 17, 17, 0.08);
    }

    .not-found-ring.ring-1 {
        width: min(28rem, 78vw);
        height: min(28rem, 78vw);
        border-top-color: rgba(197, 160, 89, 0.9);
        animation: nfSpin 16s linear infinite;
    }

    .not-found-ring.ring-2 {
        width: min(21rem, 58vw);
        height: min(21rem, 58vw);
        border-right-color: rgba(17, 17, 17, 0.3);
        animation: nfSpin 11s linear infinite reverse;
    }

    .not-found-core {
        position: relative;
        width: min(18rem, 50vw);
        height: min(18rem, 50vw);
        border-radius: 50%;
        background:
            radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.95), rgba(245, 245, 245, 0.85) 45%, rgba(197, 160, 89, 0.18) 100%);
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.9),
            0 30px 60px rgba(17, 17, 17, 0.08);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        animation: nfFloat 5.5s ease-in-out infinite;
    }

    .not-found-core::before {
        content: "";
        position: absolute;
        inset: 16px;
        border-radius: 50%;
        border: 1px dashed rgba(197, 160, 89, 0.45);
        animation: nfSpin 24s linear infinite;
    }

    .not-found-icon {
        width: 78px;
        height: 78px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(17, 17, 17, 0.92);
        color: #fff;
        font-size: 1.7rem;
        box-shadow: 0 14px 28px rgba(17, 17, 17, 0.18);
        animation: nfPulse 2.6s ease-in-out infinite;
    }

    .not-found-code {
        font-family: var(--font-heading);
        font-size: clamp(4.6rem, 9vw, 6.8rem);
        line-height: 0.9;
        letter-spacing: 0.08em;
        color: var(--primary-color);
        margin-left: 0.08em;
    }

    .not-found-label {
        font-size: 0.82rem;
        letter-spacing: 0.28em;
        text-transform: uppercase;
        color: var(--secondary-color);
    }

    .not-found-chip {
        position: absolute;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(17, 17, 17, 0.07);
        box-shadow: 0 14px 30px rgba(17, 17, 17, 0.08);
        backdrop-filter: blur(8px);
        color: var(--primary-color);
        font-size: 0.85rem;
        animation: nfFloat 5s ease-in-out infinite;
    }

    .not-found-chip i {
        color: var(--accent-color);
    }

    .not-found-chip.chip-a {
        top: 18%;
        left: 8%;
        animation-delay: -1s;
    }

    .not-found-chip.chip-b {
        top: 14%;
        right: 6%;
        animation-delay: -2.2s;
    }

    .not-found-chip.chip-c {
        bottom: 16%;
        left: 12%;
        animation-delay: -1.6s;
    }

    .not-found-chip.chip-d {
        right: 10%;
        bottom: 12%;
        animation-delay: -2.8s;
    }

    @keyframes nfSpin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    @keyframes nfFloat {
        0%, 100% {
            transform: translateY(0);
        }
        50% {
            transform: translateY(-12px);
        }
    }

    @keyframes nfPulse {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 14px 28px rgba(17, 17, 17, 0.18);
        }
        50% {
            transform: scale(1.06);
            box-shadow: 0 20px 38px rgba(17, 17, 17, 0.22);
        }
    }

    @keyframes nfDrift {
        0%, 100% {
            transform: translate3d(0, 0, 0);
        }
        50% {
            transform: translate3d(16px, -20px, 0);
        }
    }

    @media (max-width: 991.98px) {
        .not-found-copy {
            text-align: center;
        }

        .not-found-desc {
            margin-left: auto;
            margin-right: auto;
        }

        .not-found-actions,
        .not-found-meta {
            justify-content: center;
        }

        .not-found-visual {
            min-height: 380px;
            margin-bottom: 1rem;
        }

        .not-found-chip.chip-a {
            left: 2%;
        }

        .not-found-chip.chip-b {
            right: 2%;
        }
    }

    @media (max-width: 575.98px) {
        .not-found-panel {
            padding: 1.25rem;
        }

        .not-found-visual {
            min-height: 300px;
        }

        .not-found-chip {
            font-size: 0.72rem;
            padding: 8px 10px;
        }

        .not-found-meta {
            gap: 10px 18px;
            justify-content: flex-start;
        }
    }
</style>

<section class="not-found-stage">
    <div class="container">
        <div class="not-found-panel fade-in-page">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 order-2 order-lg-1">
                    <div class="not-found-copy">
                        <span class="not-found-kicker">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            Trang không tồn tại
                        </span>
                        <h1 class="not-found-title">Rất tiếc, chúng tôi không tìm thấy trang bạn đang cần</h1>
                        <p class="not-found-desc">
                            Có thể liên kết này đã thay đổi, nội dung đã được gỡ xuống, hoặc địa chỉ bạn nhập chưa chính xác.
                            Bạn có thể quay lại trang chủ hoặc tiếp tục khám phá các sản phẩm đang có tại cửa hàng.
                        </p>

                        <div class="not-found-actions">
                            <a href="index.php" class="btn btn-luxury">Về trang chủ</a>
                            <a href="category.php" class="not-found-secondary">Xem sản phẩm</a>
                            <button type="button" class="not-found-secondary" onclick="window.history.back()">Quay lại</button>
                        </div>

                    </div>
                </div>

                <div class="col-lg-6 order-1 order-lg-2">
                    <div class="not-found-visual" aria-hidden="true">
                        <div class="not-found-ring ring-1"></div>
                        <div class="not-found-ring ring-2"></div>

                        <div class="not-found-core">
                            <span class="not-found-icon">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <div class="not-found-code">404</div>
                            <div class="not-found-label">Không tìm thấy</div>
                        </div>

                        <span class="not-found-chip chip-a">
                            <i class="fa-solid fa-compass"></i>
                            Sai đường dẫn
                        </span>
                        <span class="not-found-chip chip-b">
                            <i class="fa-solid fa-stars"></i>
                            Trang đã đổi
                        </span>
                        <span class="not-found-chip chip-c">
                            <i class="fa-solid fa-rotate-left"></i>
                            Quay lại
                        </span>
                        <span class="not-found-chip chip-d">
                            <i class="fa-solid fa-house"></i>
                            Về trang chủ
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    console.groupCollapsed('[404] Wrong path detected');
    console.log('URL:', <?= json_encode($requestedUrl, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
    <?php if ($referer !== ''): ?>
    console.log('Referrer:', <?= json_encode($referer, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>);
    <?php endif; ?>
    console.groupEnd();
</script>

<?php include 'includes/footer.php'; ?>
