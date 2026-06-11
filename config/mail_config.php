<?php
// config/mail_config.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';

// CẤU HÌNH GMAIL - DÙNG MẬT KHẨU ỨNG DỤNG
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'nam2382000@gmail.com');        // Email GMAIL của bạn
define('SMTP_PASS', 'nqnr qjvu ibxz cscr');         // MẬT KHẨU ỨNG DỤNG (16 ký tự, có dấu cách)
define('SMTP_FROM', 'nam2382000@gmail.com');
define('SMTP_FROM_NAME', 'Hệ thống báo cáo sự cố');
?>