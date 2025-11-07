<?php
// functions/auth.php
// Đảm bảo session đã được bắt
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Nếu bạn dùng getDbConnection() ở nơi khác, không cần include db_connection.php ở đây.
 * Chỉ định nghĩa các hàm auth, checkLogin, getCurrentUser.
 */

/**
 * Kiểm tra nếu chưa đăng nhập thì quay về trang login
 * @param string $redirect đường dẫn quay về (mặc định: ../index.php)
 */
function checkLogin($redirect = '../index.php') {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['username'])) {
        header("Location: $redirect");
        exit();
    }
}

/**
 * Xác thực user: expects $conn là một mysqli connection
 * Trả về user row (assoc) nếu thành công, hoặc null
 */
function authenticateUser($conn, $username, $password) {
    // Nếu bạn lưu mật khẩu MD5 trong DB:
    $hashed = md5($password);

    $sql = "SELECT * FROM users WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param("ss", $username, $hashed);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}

/**
 * Lấy thông tin user hiện tại từ session (nếu đã login)
 * Trả về mảng ['user_id','username','role'] hoặc null
 */
function getCurrentUser() {
    if (isset($_SESSION['user_id']) || isset($_SESSION['username'])) {
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'username' => $_SESSION['username'] ?? null,
            'role' => $_SESSION['role'] ?? 'user'
        ];
    }
    return null;
}
?>
