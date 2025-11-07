<?php
session_start();
require_once __DIR__ . '/../functions/db_connection.php';

function handleLogin() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
        $conn = getDbConnection();

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // ✅ Truy vấn user theo username
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // ✅ Kiểm tra mật khẩu (có hỗ trợ cả loại cũ chưa mã hóa)
            $checkPassword = false;
            if ($password === $user['password']) {
                $checkPassword = true;
            }

            if ($checkPassword) {
                // Lưu thông tin session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['success'] = "Đăng nhập thành công!";

                // ✅ Phân quyền điều hướng
                if ($user['role'] === 'admin') {
                    header("Location: ../views/tour/list_tour.php");
                } else {
                    header("Location: ../views/customer/index.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Sai mật khẩu. Vui lòng thử lại.";
                header("Location: ../index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Không tìm thấy tài khoản này.";
            header("Location: ../index.php");
            exit();
        }
    }
}

handleLogin();
?>
