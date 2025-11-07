<?php
session_start();

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['username'])) {
    header("Location: ../../login.php"); 
    exit();
}

// 2. KẾT NỐI CƠ SỞ DỮ LIỆU
require_once(__DIR__ . "/../../functions/db_connection.php"); 
$conn = getDbConnection();
$username = $_SESSION['username'];

// 3. LẤY THÔNG TIN TỪ URL VÀ THIẾT LẬP MẶC ĐỊNH
// NHẬN BIẾN 'id' TỪ URL (index.php)
$tour_id = intval($_GET['id'] ?? 0); 
$quantity = 1;         // Mặc định 1 vé
$status = 'confirmed'; // Mặc định đã xác nhận

if ($tour_id <= 0) {
    echo "<script>alert('Lỗi: Thiếu hoặc không tìm thấy mã tour hợp lệ!'); window.history.back();</script>";
    exit();
}

// 4. Lấy User ID
$user_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$user_stmt->bind_param("s", $username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

if (!$user) {
    echo "<script>alert('Lỗi: Không tìm thấy thông tin người dùng!'); window.history.back();</script>";
    $conn->close();
    exit();
}
$user_id = $user['id'];

// 5. Lấy Giá Tour và Tính Tổng tiền
$price_stmt = $conn->prepare("SELECT price, name FROM tours WHERE id = ?");
$price_stmt->bind_param("i", $tour_id);
$price_stmt->execute();
$price_result = $price_stmt->get_result();
$tour = $price_result->fetch_assoc();
$price_stmt->close();

if (!$tour) {
    echo "<script>alert('Lỗi: Không tìm thấy tour có mã ID này!'); window.history.back();</script>";
    $conn->close();
    exit();
}

$total_price = $tour['price'] * $quantity;
$tour_name = $tour['name'];

// 6. LƯU ĐƠN ĐẶT VÉ VÀO DATABASE (INSERT)
$stmt = $conn->prepare("
    INSERT INTO bookings 
    (user_id, tour_id, booking_date, quantity, total_price, status) 
    VALUES (?, ?, NOW(), ?, ?, ?)
");

$stmt->bind_param("iiids", 
    $user_id, 
    $tour_id, 
    $quantity, 
    $total_price, 
    $status
);


if ($stmt->execute()) {
    // Đặt vé thành công
    echo "<script>
        alert('Chúc mừng! Bạn đã đặt thành công Tour: " . htmlspecialchars($tour_name) . ". Tổng tiền: " . number_format($total_price, 0, ',', '.') . "đ');
        window.location.href = 'bookings.php'; // Chuyển hướng đến trang vé đã đặt
    </script>";
} else {
    // Đặt vé thất bại
    echo "<script>
        alert('Lỗi khi đặt vé! Vui lòng thử lại. Chi tiết lỗi: " . htmlspecialchars($conn->error) . "');
        window.history.back();
    </script>";
}

$conn->close();
?>