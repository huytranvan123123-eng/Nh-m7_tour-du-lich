<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Trang khách hàng</title>
</head>
<body>
    <h1>Chào mừng <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>Bạn đã đăng nhập thành công.</p>
    <a href="../../logout.php">Đăng xuất</a>
</body>
</html>
