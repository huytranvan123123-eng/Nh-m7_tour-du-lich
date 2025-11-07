<?php
session_start();
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Quên mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="col-md-5 mx-auto card p-4 shadow">
        <h3 class="text-center mb-3">Quên mật khẩu</h3>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info text-center"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <form action="forgot_password_process.php" method="post">
            <div class="mb-3">
                <label class="form-label">Email đã đăng ký</label>
                <input type="email" name="email" class="form-control" placeholder="Nhập email..." required>
            </div>

            <div class="mb-3">
                <label class="form-label">Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control" placeholder="Nhập mật khẩu mới..." required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Cập nhật mật khẩu</button>
        </form>

        <div class="text-center mt-3">
            <a href="../index.php">🔙 Trở lại đăng nhập</a>
        </div>
    </div>
</div>

</body>
</html>
