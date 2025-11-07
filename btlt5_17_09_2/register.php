<?php session_start(); ?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Đăng ký tài khoản - Hệ thống Tour</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(120deg, rgba(0, 119, 182, 0.4), rgba(0, 180, 216, 0.4)),
                url('https://images.pexels.com/photos/1572033/pexels-photo-1572033.jpeg') center/cover no-repeat fixed;
    font-family: 'Poppins', sans-serif;
    display: flex; justify-content: center; align-items: center;
    height: 100vh; color: #fff;
}
.card {
    width: 400px;
    padding: 35px;
    border-radius: 20px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
}
.btn-primary {
    background: linear-gradient(135deg,#00c6ff,#0072ff);
    border: none; border-radius: 10px;
}
</style>
</head>
<body>

<div class="card">
    <h4 class="text-center mb-3">Tạo tài khoản mới</h4>

    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <form action="handle/register_process.php" method="POST">
        <div class="mb-3">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Nhập lại mật khẩu</label>
            <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary w-100">Đăng ký</button>
        <p class="text-center mt-3"><a href="index.php" class="text-light">Quay lại đăng nhập</a></p>
    </form>
</div>

</body>
</html>
