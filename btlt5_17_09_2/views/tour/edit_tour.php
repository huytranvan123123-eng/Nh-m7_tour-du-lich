<?php
require_once __DIR__ . '/../../functions/db_connection.php';
$conn = getDbConnection();

$id = $_GET['id'];
$tour = $conn->query("SELECT * FROM tours WHERE id = $id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['name'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $start_date = $_POST['start_date'];
  $end_date = $_POST['end_date'];

  // Ảnh mới
  $image = $tour['image'];
  if (!empty($_FILES['image']['name'])) {
    $image = time() . '_' . basename($_FILES['image']['name']);
    move_uploaded_file($_FILES['image']['tmp_name'], "../../uploads/" . $image);
  }

  $stmt = $conn->prepare("UPDATE tours SET name=?, description=?, price=?, start_date=?, end_date=?, image=? WHERE id=?");
  $stmt->bind_param("ssdsssi", $name, $description, $price, $start_date, $end_date, $image, $id);
  $stmt->execute();

  header("Location: list_tour.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Sửa tour</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <h3 class="mb-4">Sửa Tour</h3>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Tên tour</label>
      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($tour['name']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Mô tả</label>
      <textarea name="description" class="form-control"><?= htmlspecialchars($tour['description']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Giá</label>
      <input type="number" name="price" class="form-control" value="<?= $tour['price'] ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Ngày bắt đầu</label>
      <input type="date" name="start_date" class="form-control" value="<?= $tour['start_date'] ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Ngày kết thúc</label>
      <input type="date" name="end_date" class="form-control" value="<?= $tour['end_date'] ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Hình ảnh hiện tại</label><br>
      <img src="../../uploads/<?= htmlspecialchars($tour['image']) ?>" width="120" height="90" style="object-fit:cover;"><br><br>
      <input type="file" name="image" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Cập nhật</button>
    <a href="list_tour.php" class="btn btn-secondary">Hủy</a>
  </form>
</div>
</body>
</html>
