<?php
require_once __DIR__ . '/../../functions/db_connection.php';
$conn = getDbConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list_tour.php?error=invalid_id");
    exit;
}

$id = intval($_GET['id']);

// --- Lấy thông tin ảnh ---
$stmt = $conn->prepare("SELECT image FROM tours WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$tour = $result->fetch_assoc();

if (!$tour) {
    $stmt->close();
    $conn->close();
    header("Location: list_tour.php?error=not_found");
    exit;
}

// --- Xóa file ảnh ---
if (!empty($tour['image'])) {
    $uploadDir = realpath(__DIR__ . '/../../uploads');
    $imagePath = $uploadDir . DIRECTORY_SEPARATOR . basename($tour['image']);
    
    if (file_exists($imagePath) && str_contains($imagePath, $uploadDir)) {
        unlink($imagePath);
    }
}

// --- Xóa tour ---
$deleteStmt = $conn->prepare("DELETE FROM tours WHERE id = ?");
$deleteStmt->bind_param("i", $id);
$deleteStmt->execute();

// --- Reset AUTO_INCREMENT nếu trống ---
$count = $conn->query("SELECT COUNT(*) AS total FROM tours")->fetch_assoc();
if ($count['total'] == 0) {
    $conn->query("ALTER TABLE tours AUTO_INCREMENT = 1");
}

// --- Dọn tài nguyên ---
$stmt->close();
$deleteStmt->close();
$conn->close();

// --- Quay lại danh sách kèm thông báo ---
header("Location: list_tour.php?deleted=success");
exit;
?>
