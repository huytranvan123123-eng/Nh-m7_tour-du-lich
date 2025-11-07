<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}
if (!isset($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Thêm Tour</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container" style="max-width:900px">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Thêm Tour mới</h4>
    <a href="/btlt5_17_09_2/views/tour/list_tour.php" class="btn btn-secondary btn-sm">← Quay lại</a>
  </div>

  <form method="post" action="/btlt5_17_09_2/handle/tour_process.php" enctype="multipart/form-data">
    <input type="hidden" name="create_single" value="1">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <div class="row g-3">
      <div class="col-md-8">
        <input name="name" class="form-control" placeholder="Tên tour" required>
      </div>
      <div class="col-md-4">
        <input name="price" class="form-control" type="number" min="0" placeholder="Giá (VNĐ)" required>
      </div>
      <div class="col-md-6">
        <input name="location" class="form-control" placeholder="Địa điểm">
      </div>
      <div class="col-md-3">
        <input name="start_date" class="form-control" type="date">
      </div>
      <div class="col-md-3">
        <input name="end_date" class="form-control" type="date">
      </div>
      <div class="col-12">
        <textarea name="description" class="form-control" rows="4" placeholder="Mô tả"></textarea>
      </div>
      <div class="col-md-6">
        <label class="form-label">Ảnh</label>
        <input name="image" class="form-control" type="file" accept="image/*">
      </div>
      <div class="col-12">
        <button class="btn btn-success">Lưu Tour</button>
      </div>
    </div>
  </form>
</div>
</body>
</html>
