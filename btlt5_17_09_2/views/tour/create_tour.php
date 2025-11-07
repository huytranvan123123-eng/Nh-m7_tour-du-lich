<?php
require_once __DIR__ . '/../../handle/tour_process.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Thêm Tour Mới</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h3 class="mb-4">Thêm Tour Mới</h3>
  <form action="../../handle/tour_process.php" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Tên tour</label>
      <input type="text" name="tour_name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Mô tả</label>
      <textarea name="description" class="form-control" required></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Giá (VNĐ)</label>
      <input type="number" name="price" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Ngày bắt đầu</label>
      <input type="date" name="start_date" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Ngày kết thúc</label>
      <input type="date" name="end_date" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Hình ảnh</label>
      <input type="file" name="image" class="form-control" accept="image/*">
    </div>
    <button type="submit" name="add_tour" class="btn btn-success">Thêm tour</button>
    <a href="list_tour.php" class="btn btn-secondary">Hủy</a>
  </form>
</div>
</body>
</html>
