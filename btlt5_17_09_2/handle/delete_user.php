<?php
<?php
session_start();

$ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    if ($ajax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => 'Không có quyền']);
        exit;
    }
    header('Location: ../views/users.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($ajax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => 'Phải gửi POST']);
        exit;
    }
    header('Location: ../views/users.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    if ($ajax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => 'CSRF token không hợp lệ']);
        exit;
    }
    header('Location: ../views/users.php?deleted=0');
    exit;
}

$delId = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$postedReturn = $_POST['return_to'] ?? '/views/users.php';

if ($delId <= 0) {
    if ($ajax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => 'ID không hợp lệ']);
        exit;
    }
    header('Location: ' . $postedReturn . '?deleted=0');
    exit;
}

require_once __DIR__ . '/../functions/db_connection.php';
$conn = getDbConnection();

// Không cho xóa chính admin đang đăng nhập (bảo vệ)
$currentId = $_SESSION['user_id'] ?? null;
if ($currentId && intval($currentId) === $delId) {
    if ($ajax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => 'Không thể xóa chính bạn']);
        exit;
    }
    $conn->close();
    header('Location: ' . $postedReturn . '?deleted=0');
    exit;
}

// Lấy role target
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
if (!$stmt) {
    $err = $conn->error;
    $conn->close();
    if ($ajax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => 'Lỗi DB: ' . $err]);
        exit;
    }
    header('Location: ' . $postedReturn . '?deleted=0');
    exit;
}
$stmt->bind_param("i", $delId);
$stmt->execute();
$res = $stmt->get_result();
$target = $res->fetch_assoc() ?? null;
$stmt->close();

if (!$target) {
    $conn->close();
    if ($ajax) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'message' => 'User không tồn tại']);
        exit;
    }
    header('Location: ' . $postedReturn . '?deleted=0');
    exit;
}

// nếu target là admin, kiểm tra số lượng admin hiện có
if ($target['role'] === 'admin') {
    $r = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role = 'admin'");
    $cnt = $r ? intval($r->fetch_assoc()['cnt']) : 0;
    if ($cnt <= 1) {
        $conn->close();
        if ($ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Không thể xóa admin cuối cùng']);
            exit;
        }
        header('Location: ' . $postedReturn . '?deleted=0');
        exit;
    }
}

// Thực hiện xóa an toàn trong transaction:
// 1) set bookings.user_id = NULL where user_id = ?
// 2) delete user
$ok = false;
$conn->begin_transaction();
try {
    $stmt1 = $conn->prepare("UPDATE bookings SET user_id = NULL WHERE user_id = ?");
    if ($stmt1) {
        $stmt1->bind_param("i", $delId);
        $stmt1->execute();
        $stmt1->close();
    }

    $stmt2 = $conn->prepare("DELETE FROM users WHERE id = ? LIMIT 1");
    if ($stmt2) {
        $stmt2->bind_param("i", $delId);
        $ok = $stmt2->execute();
        $stmt2->close();
    }

    if ($ok) $conn->commit();
    else $conn->rollback();
} catch (Exception $e) {
    $conn->rollback();
    $ok = false;
}
$conn->close();

if ($ajax) {
    header('Content-Type: application/json');
    if ($ok) {
        echo json_encode(['ok' => true, 'message' => 'Xóa user thành công']);
    } else {
        echo json_encode(['ok' => false, 'message' => 'Xóa thất bại']);
    }
    exit;
}

// chuẩn hóa và redirect (không AJAX)
$projectBase = '/btlt5_17_09_2';
$rt = trim($postedReturn);
if ($rt && $rt[0] !== '/') $rt = '/' . ltrim($rt, '/');
if (strpos($rt, $projectBase) !== 0) $rt = $projectBase . $rt;
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$redirect = $scheme . '://' . $host . $rt;
$sep = (strpos($redirect, '?') === false) ? '?' : '&';
header('Location: ' . $redirect . $sep . 'deleted=' . ($ok ? '1' : '0'));
exit;