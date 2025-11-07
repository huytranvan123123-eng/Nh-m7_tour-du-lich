<?php
// Đường dẫn lùi 1 cấp để đến file db_connection.php
require_once __DIR__ . '/db_connection.php'; 

/**
 * Hàm đăng ký người dùng mới (customer).
 * Mật khẩu sẽ được mã hóa (hashing) trước khi lưu vào database.
 * @param string $username Tên đăng nhập.
 * @param string $password Mật khẩu (chưa mã hóa).
 * @param string $email Địa chỉ email.
 * @return bool Trả về TRUE nếu đăng ký thành công, FALSE nếu thất bại hoặc tên đăng nhập/email đã tồn tại.
 */
function registerUser($username, $password, $email) {
    global $conn; // Lấy biến kết nối database

    // 1. Kiểm tra username hoặc email đã tồn tại chưa
    $stmt_check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    
    if ($stmt_check->num_rows > 0) {
        // Tên đăng nhập hoặc Email đã tồn tại
        $stmt_check->close();
        return false;
    }
    $stmt_check->close();
    
    // 2. Mã hóa mật khẩu (Hashing)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // 3. Chuẩn bị câu lệnh SQL để thêm người dùng
    // Giả định bảng người dùng có tên là 'users' và cột 'role' mặc định là 'customer' (hoặc 'user')
    $role = 'customer'; // Thiết lập vai trò mặc định cho người dùng tự đăng ký
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
    
    if ($stmt === false) {
        error_log("Lỗi prepare statement: " . $conn->error);
        return false;
    }
    
    // 4. Bind parameters và thực thi
    $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);
    $result = $stmt->execute();
    
    $stmt->close();
    return $result;
}
?>
