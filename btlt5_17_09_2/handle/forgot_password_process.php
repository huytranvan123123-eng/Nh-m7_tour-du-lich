<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = getDbConnection();
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password']);

    // Kiểm tra email tồn tại
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Mã hóa mật khẩu mới
        $hashed_password = $new_password;

        // Cập nhật mật khẩu mới
        $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->bind_param("ss", $hashed_password, $email);
        $update->execute();

        $_SESSION['message'] = "✅ Cập nhật mật khẩu thành công!";
        header("Location: forgot_password.php");
        exit();

    } else {
        $_SESSION['message'] = "❌ Email không tồn tại trong hệ thống!";
        header("Location: forgot_password.php");
        exit();
    }
}
?>
