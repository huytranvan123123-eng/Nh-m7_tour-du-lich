<?php
<?php
session_start();
header('Content-Type: application/json');

$ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
if (!$ajax) {
    echo json_encode(['ok'=>false,'message'=>'AJAX required']); exit;
}
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['ok'=>false,'message'=>'Không có quyền']); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok'=>false,'message'=>'Phải POST']); exit;
}

// CSRF
$token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    echo json_encode(['ok'=>false,'message'=>'CSRF không hợp lệ']); exit;
}

$id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = trim($_POST['role'] ?? 'user');

if ($id <= 0 || $username === '') {
    echo json_encode(['ok'=>false,'message'=>'Dữ liệu không hợp lệ']); exit;
}

require_once __DIR__ . '/../functions/db_connection.php';
$conn = getDbConnection();
if (!$conn) {
    echo json_encode(['ok'=>false,'message'=>'Lỗi DB']); exit;
}

// Check duplicates (username/email) except this id
$stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR (email <> '' AND email = ?)) AND id <> ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("ssi", $username, $email, $id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        $conn->close();
        echo json_encode(['ok'=>false,'message'=>'Username hoặc email đã tồn tại']); exit;
    }
    $stmt->close();
}

// Perform update
$upd = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ? LIMIT 1");
if (!$upd) {
    $err = $conn->error;
    $conn->close();
    echo json_encode(['ok'=>false,'message'=>'Prepare failed: '.$err]); exit;
}
$upd->bind_param("sssi", $username, $email, $role, $id);
$ok = $upd->execute();
$upd->close();
$conn->close();

if ($ok) {
    echo json_encode(['ok'=>true,'message'=>'Cập nhật thành công','data'=>['username'=>$username,'email'=>$email,'role'=>$role]]);
} else {
    echo json_encode(['ok'=>false,'message'=>'Cập nhật thất bại']);
}
exit;
?>