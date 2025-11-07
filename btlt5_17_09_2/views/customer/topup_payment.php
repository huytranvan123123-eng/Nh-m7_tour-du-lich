<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../login.php");
    exit();
}
$pending = $_SESSION['pending_topup'] ?? null;
if (!$pending || $pending['username'] !== $_SESSION['username']) {
    header("Location: index.php");
    exit();
}
$amount = number_format($pending['amount'], 0, ',', '.');
$txn = htmlspecialchars($pending['txn']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<title>Hướng dẫn nạp tiền</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container" style="max-width:720px;">
  <h3>Hướng dẫn nạp tiền</h3>
  <p>Vui lòng chuyển khoản theo thông tin bên dưới, nhập đúng mã giao dịch để hệ thống xác nhận nhanh.</p>

  <div class="card mb-3">
    <div class="card-body">
      <p><strong>Số tiền cần nạp:</strong> <?php echo $amount; ?>₫</p>
      <p><strong>Mã giao dịch (ghi chú/chủ đề):</strong> <code><?php echo $txn; ?></code></p>

      <hr>
      <h5>Thông tin tài khoản nhận</h5>
      <p><strong>Ngân hàng:</strong> MB BANK </p>
      <p><strong>Chủ tài khoản:</strong> Tran Van Huy </p>
      <p><strong>Số tài khoản (STK):</strong> <code>0394502706</code></p>
      <p><strong>Chi nhánh:</strong> MB BANK </p>

      <div class="alert alert-info mt-3">
        Sau khi chuyển, bấm nút "Tôi đã chuyển tiền" để gửi yêu cầu xác nhận. Quản trị viên sẽ kiểm tra và cộng tiền vào tài khoản hoặc bạn có thể dùng tính năng tự động nếu có.
      </div>

      <form method="post" action="../../handle/topup_confirm.php">
        <input type="hidden" name="txn" value="<?php echo $txn; ?>">
        <button type="submit" class="btn btn-success">Tôi đã chuyển tiền</button>
        <a href="index.php" class="btn btn-secondary ms-2">Quay lại</a>
      </form>
    </div>
  </div>
</div>
</body>
</html>