<?php
session_start();
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Đăng nhập - Hệ thống Đặt Tour Du Lịch</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0077B6;
            --secondary-color: #00B4D8;
            --text-light: #fff;
            --text-dark: #333;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(120deg, rgba(0, 119, 182, 0.4), rgba(0, 180, 216, 0.4)),
                        url('https://images.pexels.com/photos/1572033/pexels-photo-1572033.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2') center/cover no-repeat fixed; 
            display: flex;
            /* Đảo vị trí: đưa phần đăng nhập (card-login) sang trái, phần mô tả sang phải */
            flex-direction: row-reverse;
            justify-content: space-between;
            align-items: center;
            padding: 40px;
            overflow: hidden;
            color: var(--text-dark);
        }

        .left-content {
            flex: 1;
            max-width: 650px;
            color: var(--text-light);
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
            animation: slideInLeft 1s ease-out;
        }

        .left-content .brand-logo img {
            width: 100px;
            height: auto;
            margin-bottom: 20px;
            filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.3));
        }

        .left-content h1 {
            font-size: 3rem;
            font-weight: 700;
            color: #fff;
        }

        .left-content h2 {
            font-size: 1.5rem;
            color: var(--secondary-color);
            margin-bottom: 30px;
        }

        .card-login {
            width: 380px;
            padding: 35px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.15); 
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2); 
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.25);
            color: var(--text-light);
        }

        .card-login h4 {
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
        }

        label {
            color: #eee;
            font-weight: 500;
        }

        input.form-control {
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        input.form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .btn-primary {
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            padding: 12px;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0099ff, #005ecb);
        }

        .alert {
            font-size: 14px;
            padding: 10px;
            border-radius: 10px;
            color: #fff;
            margin-bottom: 15px;
        }

        .alert-danger {
            background-color: rgba(255, 0, 0, 0.3);
        }

        .alert-success {
            background-color: rgba(0, 128, 0, 0.3);
        }

        .login-links {
            text-align: center;
            margin-top: 20px;
        }

        .login-links a {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-links a:hover {
            color: #fff;
            text-decoration: underline;
        }

        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-100px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .global-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            padding: 12px 0;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body>

<!-- Thông tin bên trái -->
<div class="left-content">
    <div class="brand-logo">
        <img src="assets/images/logo_tour.png" alt="Logo Tour">
    </div>
    <h1>Khám Phá Thế Giới Cùng Chúng Tôi ✈️</h1>
    <h2>Hệ Thống Đặt Tour Du Lịch Toàn Cầu</h2>
    <p>Đăng nhập để xem các ưu đãi độc quyền và quản lý chuyến đi của bạn.</p>
</div>

<!-- Form đăng nhập -->
<div class="card-login">
    <h4>Chào Mừng Trở Lại</h4>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form method="post" action="handle/login_process.php">
        <div class="mb-3">
            <label for="username" class="form-label">Tên đăng nhập</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="Tên đăng nhập của bạn..." required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Mật khẩu</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Mật khẩu..." required>
        </div>
        <div class="d-grid mt-4">
            <button class="btn btn-primary" name="login" type="submit">Đăng nhập ngay</button>
        </div>
    </form>

    <!-- ✅ Thêm phần tạo tài khoản và quên mật khẩu -->
    <div class="login-links">
        <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
        <p><a href="handle/forgot_password.php">Quên mật khẩu?</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
