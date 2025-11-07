<?php
// Đường dẫn này LÙI MỘT CẤP (handle/ -> ROOT) để đến functions/
require_once __DIR__ . '/../functions/tour_functions.php'; 

// Chỉ xử lý nếu yêu cầu là POST (gửi từ form)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Lấy dữ liệu từ form
    $name = trim($_POST['tour_name'] ?? '');
    $description = trim($_POST['tour_description'] ?? '');
    $price = (float)($_POST['tour_price'] ?? 0);
    $destination_id = (int)($_POST['destination_id'] ?? 0);

    // 2. Kiểm tra dữ liệu cơ bản (Validation)
    if (empty($name) || $price <= 0 || $destination_id <= 0) {
        // Dữ liệu không hợp lệ
        header("Location: ../views/tour_list.php?status=invalid_data");
        exit;
    }

    // 3. Gọi hàm thêm Tour (đã được định nghĩa trong tour_functions.php)
    if (addTour($name, $description, $price, $destination_id)) {
        // Thêm thành công
        header("Location: ../views/tour_list.php?status=add_success");
        exit;
    } else {
        // Thêm thất bại (Lỗi DB)
        header("Location: ../views/tour_list.php?status=add_fail");
        exit;
    }
} else {
    // Nếu truy cập trực tiếp file xử lý mà không qua form POST
    header("Location: ../views/tour_list.php");
    exit;
}
?>