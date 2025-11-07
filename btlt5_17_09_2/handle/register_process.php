<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

// Bật debug tạm nếu cần
ini_set('display_errors',1);
error_reporting(E_ALL);

if (!isset($_POST['register'])) {
    header("Location: ../register.php");
    exit();
}

$conn = getDbConnection();

$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

// Validate
if ($username === '' || $email === '' || $password === '') {
    $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    header("Location: ../register.php");
    exit();
}
if ($password !== $confirm) {
    $_SESSION['error'] = "Mật khẩu không khớp!";
    header("Location: ../register.php");
    exit();
}

// Kiểm tra trùng username/email
$check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
if (!$check) {
    file_put_contents(__DIR__.'/../storage/register_error.log', "Prepare check failed: ".$conn->error.PHP_EOL, FILE_APPEND);
    $_SESSION['error'] = "Lỗi server, thử lại sau.";
    header("Location: ../register.php");
    exit();
}
$check->bind_param("ss", $username, $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    $check->close();
    $conn->close();
    $_SESSION['error'] = "Tên đăng nhập hoặc email đã tồn tại!";
    header("Location: ../register.php");
    exit();
}
$check->close();

// Hash mật khẩu và chèn
$hashed = password_hash($password, PASSWORD_DEFAULT);
$role = 'user';
$stmt = $conn->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
if (!$stmt) {
    file_put_contents(__DIR__.'/../storage/register_error.log', "Prepare insert failed: ".$conn->error.PHP_EOL, FILE_APPEND);
    $_SESSION['error'] = "Lỗi server, thử lại sau.";
    $conn->close();
    header("Location: ../register.php");
    exit();
}
$stmt->bind_param("ssss", $username, $email, $hashed, $role);
$ok = $stmt->execute();
if (!$ok) {
    file_put_contents(__DIR__.'/../storage/register_error.log', "Execute insert failed: ".$stmt->error.PHP_EOL, FILE_APPEND);
    $_SESSION['error'] = "Không thể tạo tài khoản, thử lại.";
    $stmt->close();
    $conn->close();
    header("Location: ../register.php");
    exit();
}
$stmt->close();
$conn->close();

$_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
header("Location: ../index.php");
exit;
?>
