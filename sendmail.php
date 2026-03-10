<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader nếu chưa được load
if (!class_exists(PHPMailer::class)) {
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
    }
}

// Nhúng file template hóa đơn
require_once __DIR__ . '/includes/invoice_template.php';

function sendInvoiceEmail($toEmail, $toName, $orderId, $orderData, $orderItems) {
    // Kiểm tra xem thư viện PHPMailer đã được cài đặt chưa
    if (!class_exists(PHPMailer::class)) {
        // Có thể log lỗi vào file log của server nếu cần
        // error_log("PHPMailer not found. Run 'composer require phpmailer/phpmailer'");
        return false; 
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0;                      
        $mail->isSMTP();                                            
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = $_ENV['EMAIL_ADMIN']; 
        $mail->Password   = 'klnupvgegggzwmdr';
        $mail->SMTPSecure = 'tls';            
        $mail->Port       = 587;                                    

        // Recipients
        $mail->setFrom($_ENV['EMAIL_ADMIN'], $_ENV['APP_NAME']);
        $mail->addAddress($toEmail, $toName);     

        // Content
        // Chuẩn bị dữ liệu đầy đủ cho template
        $templateData = $orderData;
        $templateData['name'] = $toName; // Đảm bảo tên khách hàng có trong mảng dữ liệu
        $templateData['created_at'] = date('Y-m-d H:i:s');

        $mail->isHTML(true);                                  
        $mail->Subject = "Xác nhận đơn hàng #$orderId - " . $_ENV['APP_NAME'];
        $mail->Body    = getLuxuryInvoiceHTML($orderId, $templateData, $orderItems);
        $mail->AltBody = "Cảm ơn bạn đã đặt hàng. Mã đơn hàng: #$orderId. Tổng tiền: "
            . (function_exists('formatVND') ? formatVND($orderData['total']) : number_format((float)$orderData['total'], 0, ',', '.') . ' VNĐ');

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Có thể log lỗi vào file nếu cần: error_log($mail->ErrorInfo);
        return false;
    }
}
