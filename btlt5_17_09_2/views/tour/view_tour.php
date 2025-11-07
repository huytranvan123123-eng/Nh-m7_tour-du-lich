<?php
require_once __DIR__ . '/../../functions/db_connection.php';
$conn = getDbConnection();

// Nếu không có id thì quay lại danh sách
if (!isset($_GET['id'])) {
  header("Location: list_tour.php");
  exit;
}

$id = intval($_GET['id']);
$tour = $conn->query("SELECT * FROM tours WHERE id = $id")->fetch_assoc();

// Nếu không tìm thấy tour
if (!$tour) {
  echo "<h2 class='text-center mt-5 text-danger'>Tour không tồn tại!</h2>";
  exit;
}

// Xử lý ảnh (nếu bị thiếu hoặc file không tồn tại)
$imagePath = "../../uploads/" . htmlspecialchars($tour['image']);
if (empty($tour['image']) || !file_exists($imagePath)) {
  $imagePath = "../../uploads/no-image.jpg";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($tour['name']) ?> - Chi tiết tour</title>

  <!-- Bootstrap & Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background: linear-gradient(135deg, #dbeafe, #f0fdf4);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Poppins', 'Segoe UI', sans-serif;
      padding: 30px;
    }

    .tour-container {
      max-width: 900px;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .tour-container:hover {
      transform: translateY(-5px);
    }

    .tour-image {
      width: 100%;
      height: 420px;
      object-fit: cover;
      border-bottom: 1px solid #eee;
    }

    .tour-content {
      padding: 30px;
    }

    .tour-title {
      font-size: 1.9rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 12px;
    }

    .tour-desc {
      color: #475569;
      font-size: 1rem;
      line-height: 1.6;
      margin-bottom: 22px;
    }

    .tour-info p {
      margin-bottom: 10px;
      font-size: 1rem;
      color: #334155;
    }

    .price {
      font-size: 1.5rem;
      font-weight: 700;
      color: #ef4444;
    }

    .btn-back {
      background-color: #2563eb;
      color: #fff;
      padding: 10px 20px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 500;
      transition: 0.2s;
    }

    .btn-back:hover {
      background-color: #1e40af;
      color: #fff;
    }

    .icon {
      color: #2563eb;
      width: 20px;
    }

    @media (max-width: 768px) {
      .tour-content {
        padding: 20px;
      }
      .tour-title {
        font-size: 1.5rem;
      }
    }
  </style>
</head>

<body>

<div class="tour-container">
  <img src="<?= $imagePath ?>" class="tour-image" alt="Tour image">

  <div class="tour-content">
    <h1 class="tour-title"><?= htmlspecialchars($tour['name']) ?></h1>
    <p class="tour-desc"><?= nl2br(htmlspecialchars($tour['description'])) ?></p>

    <div class="tour-info">
      <p><i class="fa-solid fa-calendar-days icon"></i> 
         <strong>Ngày bắt đầu:</strong> <?= htmlspecialchars($tour['start_date']) ?></p>

      <p><i class="fa-solid fa-calendar-check icon"></i> 
         <strong>Ngày kết thúc:</strong> <?= htmlspecialchars($tour['end_date']) ?></p>

      <p><i class="fa-solid fa-money-bill-wave icon"></i> 
         <strong>Giá:</strong> <span class="price"><?= number_format($tour['price'], 0, ',', '.') ?>đ</span></p>
    </div>

    <div class="mt-4">
      <a href="list_tour.php" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
      </a>
    </div>
  </div>
</div>

</body>
</html>
