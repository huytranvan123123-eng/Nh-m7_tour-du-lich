<?php
// Sử dụng session để kiểm tra đăng nhập (nếu trang này dành cho khách hàng đã đăng nhập)
session_start();
if (!isset($_SESSION['username'])) {
    // Tùy chọn: Chuyển hướng người dùng chưa đăng nhập về trang đăng nhập
    // header("Location: ../../login.php"); 
    // exit();
}

require_once __DIR__ . '/../../functions/db_connection.php';
$conn = getDbConnection();

// Lấy ID từ URL (đảm bảo dùng intval để tránh SQL Injection)
$id = intval($_GET['id']);

// Sử dụng Prepared Statement để bảo mật hơn
$stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$tour = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$tour) {
    // Tùy chọn: Hiển thị trang 404 tùy chỉnh thay vì die()
    http_response_code(404);
    die("
    <div style='text-align:center; padding: 50px; font-family: Montserrat, sans-serif;'>
        <h1 style='color:#ef4444;'>Lỗi 404</h1>
        <p>Không tìm thấy tour này!</p>
        <a href='index.php' style='text-decoration:none; color:#1a202c;'>Quay lại trang chủ</a>
    </div>
    ");
}

// Giả định $username (nếu không đăng nhập, có thể để trống)
$username = $_SESSION['username'] ?? 'customer';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($tour['name']) ?> - Chi Tiết Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* --- Biến màu sắc và Style đồng bộ --- */
        :root {
            --primary-color: #0b7af7; 
            --danger-color: #ef4444; 
            --warning-color: #facc15; 
            --text-dark: #1a202c; 
            --bg-main-start: #e0f2fe; 
            --bg-main-end: #d0e7ff;
            --sky-blue: #0ea5e9;
            --navy-blue: #1e3a8a;
        }

        /* --- Tổng thể & Nền --- */
        body {
            background: linear-gradient(135deg, var(--bg-main-start) 0%, var(--bg-main-end) 100%);
            font-family: 'Montserrat', sans-serif;
            min-height: 100vh;
            color: var(--text-dark);
            overflow-x: hidden;
        }
        .container { padding-top: 50px; padding-bottom: 50px; }
        
        /* --- Header Tinh Gọn (Giống index.php) --- */
        .main-header {
            background: linear-gradient(90deg, #1a202c 0%, #2d3748 100%);
            color: white;
            padding: 5px 0; /* Header tinh gọn */
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        h1.logo { font-weight: 800; font-size: 1.5rem; color: #ffffff !important; margin: 0; }
        h1.logo i { color: var(--warning-color); margin-right: 10px; }
        .action-links { display: flex; align-items: center; gap: 20px; }
        .user-info { color: white; font-weight: 500; font-size: 0.9rem; }
        .user-info b { color: var(--warning-color); font-weight: 700; }
        .btn-link { border-radius: 10px; font-weight: 600; padding: 6px 15px; text-decoration: none; font-size: 0.9rem; transition: all 0.3s; }
        .btn-logout { color: var(--warning-color); border: 2px solid var(--warning-color); background: transparent; }
        .btn-logout:hover { background-color: var(--warning-color); color: var(--text-dark); transform: translateY(-2px); }
        .btn-view-bookings { color: white; background: rgba(255, 255, 255, 0.1); }
        .btn-view-bookings:hover { background: rgba(255, 255, 255, 0.2); color: white; }

        /* --- Style cho Trang Chi Tiết Tour --- */
        .detail-card {
            background: rgba(255, 255, 255, 0.9); 
            backdrop-filter: blur(8px); 
            -webkit-backdrop-filter: blur(8px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .tour-image {
            border-radius: 20px;
            object-fit: cover;
            height: 350px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .tour-title {
            font-weight: 800;
            color: var(--navy-blue);
            font-size: 2.5rem;
            margin-bottom: 20px;
        }
        
        .price-display {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--danger-color);
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.08);
        }

        .info-item {
            font-size: 1.1rem;
            margin-bottom: 15px;
            font-weight: 600;
            color: #475569;
        }
        .info-item i {
            color: var(--sky-blue);
            margin-right: 15px;
            font-size: 1.3rem;
        }

        .description-box {
            background: rgba(240, 248, 255, 0.7);
            border-radius: 15px;
            padding: 25px;
            margin-top: 25px;
            border-left: 5px solid var(--primary-color);
        }
        .description-box h3 {
            color: var(--text-dark);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        .description-box p {
            line-height: 1.8;
            color: var(--text-dark);
        }

        /* Nút Đặt vé Lớn (CTA) */
        .btn-book-large {
            width: 100%;
            background: linear-gradient(90deg, var(--navy-blue) 0%, var(--primary-color) 100%);
            color: white;
            border-radius: 15px; 
            padding: 15px 30px;
            font-weight: 700;
            font-size: 1.2rem;
            box-shadow: 0 8px 25px rgba(14, 165, 233, 0.4);
            transition: all 0.3s ease;
            margin-top: 30px;
            border: none;
        }
        .btn-book-large:hover {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--sky-blue) 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(14, 165, 233, 0.6);
            color: #fff;
        }
    </style>
</head>
<body>

<header class="main-header">
    <div class="container header-content">
        <h1 class="logo"><i class="fa-solid fa-plane-departure"></i> Travel Explorer</h1>
        <div class="action-links">
            <div class="user-info">
                Xin chào, <b><?= htmlspecialchars($username) ?></b>!
            </div>
             <a href="bookings.php" class="btn-link btn-view-bookings">
                <i class="fa-solid fa-ticket-simple me-2"></i> Vé đã đặt
            </a>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="../../handle/logout.php" class="btn-link btn-logout">
                    <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="container">
    <a href="index.php" class="btn btn-sm mb-4" style="background: rgba(0,0,0,0.1); color: var(--text-dark); border-radius: 10px; font-weight: 600;">
        <i class="fa-solid fa-arrow-left me-2"></i> Quay lại danh sách Tour
    </a>
    
    <div class="row detail-card">
        <div class="col-lg-6">
            <img src="../../uploads/<?php echo htmlspecialchars($tour['image'] ?? 'placeholder.jpg'); ?>" class="tour-image" alt="<?php echo htmlspecialchars($tour['name']); ?>">
        </div>
        
        <div class="col-lg-6 pt-4 pt-lg-0">
            <h2 class="tour-title"><?= htmlspecialchars($tour['name']) ?></h2>
            
            <p class="price-display">
                Giá: <?= number_format($tour['price'], 0, ',', '.') ?>đ
            </p>

            <div class="info-group">
                <p class="info-item">
                    <i class="fa-solid fa-calendar-alt"></i> Ngày bắt đầu: <b><?= htmlspecialchars($tour['start_date']) ?></b>
                </p>
                <p class="info-item">
                    <i class="fa-solid fa-calendar-alt"></i> Ngày kết thúc: <b><?= htmlspecialchars($tour['end_date']) ?></b>
                </p>
                <p class="info-item">
                    <i class="fa-solid fa-users"></i> Số lượng khách tối đa: <b><?= htmlspecialchars($tour['max_guests'] ?? 'Không giới hạn') ?></b>
                </p>
                <p class="info-item">
                    <i class="fa-solid fa-map-marker-alt"></i> Địa điểm: <b><?= htmlspecialchars($tour['location'] ?? 'Chưa cập nhật') ?></b>
                </p>
            </div>
            
            <a href="book.php?id=<?= $tour['id'] ?>" class="btn-book-large">
                <i class="fa-solid fa-paper-plane me-2"></i> Đặt vé ngay
            </a>
        </div>
        
        <div class="col-12">
            <div class="description-box">
                <h3><i class="fa-solid fa-info-circle me-2"></i> Mô tả chi tiết</h3>
                <p><?= nl2br(htmlspecialchars($tour['description'])) ?></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>