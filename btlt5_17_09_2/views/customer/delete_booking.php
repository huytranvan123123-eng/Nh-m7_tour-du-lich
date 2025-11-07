<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

require_once("../../functions/db_connection.php");
$conn = getDbConnection();

if (!isset($_GET['id'])) {
    die("Thiếu ID vé!");
}

$booking_id = intval($_GET['id']);
$username = $_SESSION['username'];

// Lấy ID người dùng hiện tại
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Không tìm thấy người dùng!");
}
$user_id = $user['id'];

// Xóa vé của chính người đó
$stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);

if ($stmt->execute()) {
    echo "<script>
        alert('Xóa vé thành công!');
        window.location.href = 'bookings.php';
    </script>";
} else {
    echo "<script>
        alert('Lỗi khi xóa vé!');
        window.history.back();
    </script>";
}

$conn->close();
?>
