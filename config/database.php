<?php
// Cấu hình database - KIỂM TRA LẠI CÁC THÔNG SỐ NÀY
$host = 'localhost';      // Nếu dùng XAMPP: localhost
$dbname = 'bcsc';  // Tên database bạn vừa tạo
$username = 'root';        // Mặc định XAMPP: root
$password = '';            // Mặc định XAMPP: để trống

// Nếu dùng MAMP trên Mac: $host = 'localhost', $username = 'root', $password = 'root'

try {
    // Tạo kết nối PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Test kết nối
    // echo "Kết nối thành công!"; // Bỏ comment để test
    
} catch(PDOException $e) {
    // Nếu lỗi, hiển thị chi tiết để debug
    die("LỖI KẾT NỐI DATABASE: " . $e->getMessage() . 
        "<br><br>Hãy kiểm tra:<br>
        1. MySQL đã được bật chưa?<br>
        2. Tên database '{$dbname}' đã tồn tại chưa?<br>
        3. Username/password có đúng không?<br>
        4. Bạn đã tạo bảng 'users' chưa?");
}

// Bắt đầu session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>