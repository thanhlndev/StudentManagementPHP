<?php
// Nhúng các file core của PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/phpmailer/src/Exception.php';
require '../vendor/phpmailer/src/PHPMailer.php';
require '../vendor/phpmailer/src/SMTP.php';

/**
 * Hàm gửi email chuẩn SMTP
 * @param string $toEmail Email người nhận
 * @param string $toName Tên người nhận
 * @param string $subject Tiêu đề thư
 * @param string $body Nội dung thư (Hỗ trợ HTML)
 * @return bool Trả về true nếu gửi thành công, false nếu thất bại
 */
function sendSystemEmail($toEmail, $toName, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // --- CẤU HÌNH SERVER SMTP ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';                     // Máy chủ SMTP của Gmail
        $mail->SMTPAuth   = true;                                 // Bật xác thực SMTP
        $mail->Username   = 'thanhln.dev@gmail.com';              // Email gửi đi
        $mail->Password   = 'ehjr mvzi ccyq dcmd';                // Mật khẩu ứng dụng (App Password) của Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       // Mã hóa TLS
        $mail->Port       = 587;                                  // Cổng kết nối TLS
        $mail->CharSet    = 'UTF-8';                              // Đảm bảo không bị lỗi font tiếng Việt

        // --- NGƯỜI GỬI & NGƯỜI NHẬN ---
        $mail->setFrom('email_truong_cua_ban@gmail.com', 'PHÒNG ĐÀO TẠO - QLSV');
        $mail->addAddress($toEmail, $toName);

        // --- NỘI DUNG ---
        $mail->isHTML(true);                                      // Thiết lập định dạng HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Ghi log lỗi nếu cần: $e->getMessage()
        return false;
    }
}