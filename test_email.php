<?php
// test_email.php - Test PHPMailer hoạt động không
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Kiểm tra gửi email</h2>";

// Kiểm tra file tồn tại
$files_to_check = [
    __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php',
    __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php',
    __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php',
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "✅ " . basename($file) . " tồn tại<br>";
    } else {
        echo "❌ " . basename($file) . " KHÔNG tồn tại tại: " . $file . "<br>";
    }
}

// Thử include
try {
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/phpmailer/phpmailer/src/SMTP.php';
    echo "✅ Đã include PHPMailer thành công<br>";
} catch (Exception $e) {
    echo "❌ Lỗi include: " . $e->getMessage() . "<br>";
}

// Thử gửi email
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Cấu hình SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'nam2382000@gmail.com';  // THAY EMAIL CỦA BẠN
    $mail->Password = 'nqnr qjvu ibxz cscr';     // THAY MẬT KHẨU ỨNG DỤNG
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';
    
    // Bật debug
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = 'html';
    
    // Người gửi/nhận
    $mail->setFrom('nam2382000@gmail.com', 'Test System');
    $mail->addAddress('nam2382000@gmail.com', 'Test User'); // Gửi đến chính email của bạn
    
    // Nội dung
    $mail->isHTML(true);
    $mail->Subject = 'Test email từ hệ thống';
    $mail->Body = '<h1>Test thành công!</h1><p>Email đã được gửi từ PHPMailer.</p>';
    
    $mail->send();
    echo "<h3 style='color:green'>✅ EMAIL ĐÃ ĐƯỢC GỬI THÀNH CÔNG!</h3>";
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>❌ LỖI: " . $mail->ErrorInfo . "</h3>";
}
?>