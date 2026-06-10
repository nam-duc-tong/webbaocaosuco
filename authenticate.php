<?php
session_start();
// require_once 'config/database.php';
require_once(__DIR__ . '/config/database.php');
// Kiểm tra nếu không phải POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// Lấy dữ liệu từ form
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$remember = isset($_POST['remember']) ? true : false;

// Validate input
if (empty($username) || empty($password)) {
    header("Location: login.php?error=invalid");
    exit();
}

try {
    // Tìm user trong database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND status = 'active'");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // Kiểm tra mật khẩu
    if ($user && md5($password) === $user['password']) {
        // Đăng nhập thành công
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        // Cập nhật thời gian đăng nhập cuối
        $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $updateStmt->execute([':id' => $user['id']]);

        // Xử lý ghi nhớ đăng nhập
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), "/");

            // Tạo bảng remember_tokens nếu chưa có
            $pdo->exec("CREATE TABLE IF NOT EXISTS remember_tokens (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                token VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Lưu token
            $tokenStmt = $pdo->prepare("INSERT INTO remember_tokens (user_id, token) VALUES (:user_id, :token)");
            $tokenStmt->execute([':user_id' => $user['id'], ':token' => $token]);
        }

        // Chuyển đến dashboard
        header("Location: ./admin/dashboard.php");
        exit();
    } else {
        // Đăng nhập thất bại
        header("Location: login.php?error=invalid");
        exit();
    }
} catch (PDOException $e) {
    // Lỗi database
    error_log("Login error: " . $e->getMessage());
    header("Location: login.php?error=db_error");
    exit();
}
