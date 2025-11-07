<?php
// Cấu hình kết nối cơ sở dữ liệu
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '123456789Mquan@'; // đổi nếu mật khẩu MySQL của bạn khác
$DB_NAME = 'btlt5_17_09';
$DB_CHARSET = 'utf8mb4';

// Hàm tạo kết nối
function getDbConnection() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_CHARSET;
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) {
        die('Kết nối thất bại: ' . $conn->connect_error);
    }
    $conn->set_charset($DB_CHARSET);
    return $conn;
}
?>