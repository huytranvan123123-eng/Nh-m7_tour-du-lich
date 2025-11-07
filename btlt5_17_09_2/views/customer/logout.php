<?php
session_start();

// Xóa toàn bộ session hiện tại
session_unset();
session_destroy();

// Quay về trang đăng nhập chính
header("Location: ../index.php");
exit;
?>
