<?php
// ✅ Cấu hình thông tin kết nối
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'huyprvc1'); // nếu bạn đặt mật khẩu MySQL thì thêm vào đây
define('DB_NAME', 'btlt5_17_09_2');
define('DB_CHARSET', 'utf8mb4');

// ✅ Hàm kết nối CSDL
function getDbConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("❌ Kết nối thất bại: " . $conn->connect_error);
    }

    $conn->set_charset(DB_CHARSET);
    return $conn;
}
?>