<?php
// handle/logout.php
session_start();

// Xóa tất cả session data
$_SESSION = [];

// Hủy session cookie (nếu có)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hủy session
session_destroy();

// Chuyển về trang chủ project (dùng đường dẫn tuyệt đối để tránh 404 do path lặp)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base = $scheme . '://' . $host . '/btlt5_17_09_2/index.php';

header('Location: ' . $base);
exit;
?>
