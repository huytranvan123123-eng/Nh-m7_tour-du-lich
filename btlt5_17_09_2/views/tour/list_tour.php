<?php
// ... (Giữ nguyên đoạn PHP logic ở đầu file)
session_start();

// Kiểm tra quyền (admin)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . '/../../functions/db_connection.php';
$conn = getDbConnection();

// --- Thống kê ---
$totalTours = 0;
$r = $conn->query("SELECT COUNT(*) AS cnt FROM tours");
if ($r) { $row = $r->fetch_assoc(); $totalTours = (int)$row['cnt']; }

$totalBookings = 0;
$r = $conn->query("SELECT COUNT(*) AS cnt FROM bookings");
if ($r) { $row = $r->fetch_assoc(); $totalBookings = (int)$row['cnt']; }

$totalRevenue = 0.0;
$r = $conn->query("SELECT IFNULL(SUM(total_price),0) AS s FROM bookings WHERE status = 'confirmed'");
if ($r) { $row = $r->fetch_assoc(); $totalRevenue = (float)$row['s']; }

$todayBookings = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE DATE(booking_date) = CURDATE()");
if ($stmt) {
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) { $row = $res->fetch_assoc(); $todayBookings = (int)$row['cnt']; }
    $stmt->close();
}

// --- Lấy danh sách tours ---
$search = "";
$tours = [];
$result = null;

if (isset($_GET['q']) && trim($_GET['q']) !== "") {
    $search = trim($_GET['q']);
    $like = "%$search%";
    $stmt2 = $conn->prepare("SELECT * FROM tours WHERE name LIKE ? OR description LIKE ? ORDER BY id DESC");
    if ($stmt2) {
        $stmt2->bind_param("ss", $like, $like);
        $stmt2->execute();
        $result = $stmt2->get_result();
    }
} else {
    $result = $conn->query("SELECT * FROM tours ORDER BY id DESC");
}

if ($result) {
    while ($tour = $result->fetch_assoc()) {
        $tours[] = $tour;
    }
    $result->free();
}
// Đóng kết nối DB
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tour Hàng không (Admin)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0b7af7; /* Xanh da trời */
            --secondary-color: #10b981; /* Xanh lá */
            --danger-color: #ef4444; /* Đỏ */
            --warning-color: #facc15; /* Vàng */
            --text-dark: #1a202c; /* Đen đậm hơn */
            --text-muted: #718096; /* Xám dịu hơn */
            --bg-main-start: #e0f2fe; /* Gradient nền nhẹ */
            --bg-main-end: #d0e7ff;
            --card-shadow: 0 15px 45px rgba(0, 0, 0, 0.1); /* Bóng thẻ mạnh và mượt hơn */
            --card-bg: rgba(255, 255, 255, 0.9); /* Card hơi trong suốt */
            /* Màu tour máy bay */
            --sky-blue: #0ea5e9;
            --navy-blue: #1e3a8a;
            --light-grey: #f8fafc;
        }

        body { 
            background: linear-gradient(135deg, var(--bg-main-start) 0%, var(--bg-main-end) 100%); /* Nền gradient cao cấp */
            font-family: 'Montserrat', sans-serif; /* Font Montserrat hiện đại */
            color: var(--text-dark); 
            min-height: 100vh;
            display: flex; /* Dùng flexbox cho layout toàn trang */
            flex-direction: column;
            overflow-x: hidden; /* Tránh scroll ngang không mong muốn */
        }
        
        .container { flex: 1; /* Đảm bảo container chiếm hết không gian còn lại */ }

        /* --- Navbar (Premium Gradient & Glassmorphism) --- */
        .navbar { 
            background: linear-gradient(90deg, #1a202c 0%, #2d3748 100%); /* Nền navbar xanh đen sang trọng */
            box-shadow: 0 5px 20px rgba(0,0,0,0.3); 
            padding: 20px 0; 
            border-bottom: none; /* Bỏ border dưới */
            position: relative;
            z-index: 1000;
        }
        .navbar-brand { 
            font-weight:800; 
            font-size:1.7rem; 
            color: #ffffff !important; 
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2); 
            letter-spacing: 0.5px;
        }
        .navbar-brand i { color: var(--warning-color); margin-right: 10px; transform: rotate(-45deg); transition: transform 0.4s ease-out; }
        .navbar-brand:hover i { transform: rotate(-55deg) scale(1.1); } /* Hiệu ứng hover cho icon máy bay */
        
        .search-form .form-control { 
            border-radius: 12px; 
            border: 1px solid rgba(255,255,255,0.2); 
            background-color: rgba(255,255,255,0.1); 
            color: white;
            transition: all 0.3s ease;
        }
        .search-form .form-control::placeholder { color: rgba(255,255,255,0.6); }
        .search-form .form-control:focus { 
            background-color: rgba(255,255,255,0.2); 
            border-color: var(--warning-color); 
            box-shadow: 0 0 0 0.2rem rgba(252, 186, 3, 0.25);
            color: white;
        }
        .search-form .btn-primary { 
            background-color: var(--warning-color); 
            border-color: var(--warning-color); 
            color: var(--text-dark);
            border-radius: 0 12px 12px 0;
            transition: all 0.3s ease;
        }
        .search-form .btn-primary:hover { 
            background-color: #eab308; 
            border-color: #eab308;
            transform: scale(1.05);
        }

        .btn-logout { 
            border-radius: 12px; 
            font-weight: 600; 
            color: var(--warning-color); 
            border: 2px solid var(--warning-color);
            padding: 8px 20px;
            transition: all 0.3s ease;
            background: transparent;
        }
        .btn-logout:hover { 
            background-color: var(--warning-color); 
            color: var(--text-dark); 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(252, 186, 3, 0.4);
        }

        /* --- Stat Cards (Glassmorphism & Interactive) --- */
        .stat-card { 
            border-radius: 20px; /* Bo góc nhiều hơn */
            padding: 30px; /* Tăng padding */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); /* Bóng nhẹ ban đầu */
            background: var(--card-bg); /* Nền hơi trong suốt */
            backdrop-filter: blur(8px); /* Hiệu ứng kính mờ */
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3); /* Border mờ */
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1); /* Chuyển động mượt mà hơn */
            position: relative;
            z-index: 1;
        }
        .stat-card:hover { 
            transform: translateY(-8px) scale(1.02); /* Nhảy lên và phóng to nhẹ */
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2); /* Bóng mạnh hơn khi hover */
            border-color: rgba(255, 255, 255, 0.5);
        }
        /* Gradient overlay cho mỗi card để tạo điểm nhấn */
        .stat-card::before { 
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 50%);
            z-index: 0;
            transition: opacity 0.4s ease;
        }
        .stat-card:hover::before { opacity: 0; } /* Ẩn overlay khi hover */

        .stat-icon { 
            font-size: 3rem; /* Icon lớn hơn */
            margin-bottom: 15px; 
            opacity: 0.8; 
            z-index: 1; 
            position: relative;
            filter: drop-shadow(0 2px 5px rgba(0,0,0,0.1)); /* Bóng icon */
        }
        .stat-number { font-size: 2.8rem; font-weight: 800; color: var(--navy-blue); z-index: 1; position: relative; }
        .stat-label { color: var(--text-muted); margin-top: 5px; font-weight: 600; font-size: 1.05rem; z-index: 1; position: relative; }
        
        /* Màu sắc Stat Card cụ thể với gradient tinh tế */
        .stat-card-1 .stat-icon { background: linear-gradient(45deg, #facc15, #eab308); -webkit-background-clip: text; -webkit-text-fill-color: transparent; } /* Tour */
        .stat-card-2 .stat-icon { background: linear-gradient(45deg, #38bdf8, #0ea5e9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; } /* Booking */
        .stat-card-3 .stat-icon { background: linear-gradient(45deg, #4ade80, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; } /* Revenue */
        .stat-card-4 .stat-icon { background: linear-gradient(45deg, #fb7185, #ef4444); -webkit-background-clip: text; -webkit-text-fill-color: transparent; } /* Today Booking */


        /* --- Tiêu đề & Nút Thêm (Refined) --- */
        .section-header { 
            border-bottom: 2px solid rgba(0, 0, 0, 0.08); 
            padding-bottom: 20px; 
            margin-bottom: 40px; 
            margin-top: 30px;
        }
        .tour-title-main { font-size: 2.2rem; font-weight: 700; color: var(--text-dark); letter-spacing: 0.5px; }
        .tour-title-main i { color: var(--sky-blue); margin-right: 12px; }

        .btn-add { 
            background: linear-gradient(90deg, #10b981 0%, #059669 100%); /* Gradient nút thêm */
            color: white; 
            border-radius: 12px; 
            padding: 12px 25px; 
            font-weight: 600; 
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4); 
            transition: all 0.3s ease;
            border: none;
        }
        .btn-add:hover { 
            transform: translateY(-3px) scale(1.02); 
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.6); 
            background: linear-gradient(90deg, #059669 0%, #047857 100%);
        }

        /* --- Tour Cards (Interactive & Aesthetic) --- */
        .card { 
            border: none;
            border-radius: 20px; 
            overflow: hidden; 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1); /* Bóng ban đầu */
            background: var(--card-bg); 
            backdrop-filter: blur(5px); /* Kính mờ nhẹ cho card tour */
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .card:hover { 
            transform: translateY(-7px) scale(1.01); 
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2); 
            border-color: rgba(255, 255, 255, 0.4);
        }

        .price-badge { 
            top: 20px; 
            right: 20px; 
            background: linear-gradient(45deg, #ef4444, #dc2626); /* Gradient cho badge giá */
            padding: 10px 20px; 
            border-radius: 50px; 
            font-size: 1.05rem;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
            letter-spacing: 0.5px;
            z-index: 10;
        }
        .card img { 
            height: 250px; 
            width: 100%; 
            object-fit: cover; 
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }
        
        .card-body { 
            padding: 25px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
            flex-grow: 1;
        }
        
        .tour-name { 
            font-size: 1.4rem; 
            font-weight: 700; 
            color: var(--text-dark); 
            margin-bottom: 10px; 
            line-height: 1.3;
        }
        .tour-desc { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 15px; min-height: 50px; line-height: 1.6; }
        .tour-date { color: var(--text-muted); font-size: 0.9rem; font-weight: 600; margin-bottom: 20px;}
        .tour-date i { margin-right: 10px; color: var(--sky-blue); font-size: 1.1rem; }
        
        /* --- Nút hành động (Enhanced) --- */
        .btn-action { 
            padding: 10px 18px; 
            border-radius: 10px; 
            font-weight: 600; 
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-action i { margin-right: 6px; } /* Khoảng cách cho icon trong nút */

        .btn-detail { background: linear-gradient(90deg, var(--navy-blue) 0%, var(--sky-blue) 100%); color: #fff; border: none;}
        .btn-detail:hover { 
            background: linear-gradient(90deg, var(--sky-blue) 0%, #0ea5e9 100%); 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.4);
        }
        
        .btn-edit { background: var(--warning-color); color: var(--text-dark); border: none; }
        .btn-edit:hover { 
            background: #eab308; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(252, 186, 3, 0.3);
        }
        
        .btn-delete { background: var(--danger-color); color: #fff; border: none; }
        .btn-delete:hover { 
            background: #dc2626; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }

        .action-buttons { display: flex; gap: 10px; }

        /* Smooth, modern pill nav */
.navbar { background: linear-gradient(90deg, rgba(255,255,255,0.7), rgba(255,255,255,0.6)); backdrop-filter: blur(6px); border-bottom: 1px solid rgba(15,23,42,0.04); padding:12px 0; }
.nav-pills .nav-link { border-radius: 999px; padding:10px 16px; color:#0f172a; background:transparent; transition: all .18s ease; font-weight:600; box-shadow: none; }
.nav-pills .nav-link.btn-pill i { color: #0ea5e9; }
.nav-pills .nav-link:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(14,165,233,0.08); background: linear-gradient(90deg, rgba(14,165,233,0.06), rgba(30,58,138,0.03)); }
.nav-pills .nav-link.active { background: linear-gradient(90deg,#0ea5e9,#1e3a8a); color:#fff; box-shadow: 0 10px 30px rgba(14,165,233,0.14); }
.btn-light.btn-sm { background:#ffffffcc; border:1px solid rgba(15,23,42,0.04); box-shadow: 0 6px 20px rgba(2,6,23,0.04); }
.btn-outline-danger { border-radius:10px; padding:8px 10px; }
@media (max-width: 767px){ .navbar .navbar-brand small{ display:none; } }

    </style>
</head>
<body>

<nav class="navbar sticky-top" aria-label="Main navigation">
  <div class="container d-flex align-items-center justify-content-between" style="gap:12px;">
    <div class="d-flex align-items-center">
      <a class="navbar-brand d-flex align-items-center" href="/btlt5_17_09_2/views/tour/list_tour.php" style="gap:10px;">
        <span style="display:inline-block;width:46px;height:46px;border-radius:10px;background:linear-gradient(135deg,#0ea5e9,#1e3a8a);box-shadow:0 6px 20px rgba(14,165,233,0.16);display:grid;place-items:center;color:#fff;font-weight:700;">
          <i class="fa-solid fa-plane-departure"></i>
        </span>
        <div style="line-height:1;">
          <div style="font-weight:700;color:#0f172a">Admin Panel</div>
          <small style="color:#64748b">Quản lý Tour & Đặt vé</small>
        </div>
      </a>
    </div>

    <div class="d-none d-md-flex align-items-center" id="adminNav">
      <ul class="nav nav-pills" role="tablist" style="gap:10px;">
        <li class="nav-item" role="presentation">
          <a class="nav-link btn-pill" href="/btlt5_17_09_2/views/tour/list_tour.php"><i class="fa-solid fa-list me-2"></i>Danh sách</a>
        </li>
        <!-- "Thêm tour" đã bị loại bỏ -->
        <li class="nav-item" role="presentation">
          <a class="nav-link btn-pill" href="/btlt5_17_09_2/views/users.php"><i class="fa-solid fa-users me-2"></i>Người dùng</a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link btn-pill" href="/btlt5_17_09_2/views/tour/today_bookings_overview.php"><i class="fa-solid fa-calendar-check me-2"></i>Đặt hôm nay</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="/btlt5_17_09_2/handle/logout.php">Đăng xuất</a>
        </li>
      </ul>
    </div>

    <!-- mobile: collapse into icons (loại bỏ biểu tượng Thêm) -->
    <div class="d-flex d-md-none" style="gap:8px;">
      <a class="btn btn-light btn-sm" href="/btlt5_17_09_2/views/tour/list_tour.php" title="Danh sách"><i class="fa-solid fa-list"></i></a>
      <!-- add icon removed -->
      <a class="btn btn-light btn-sm" href="/btlt5_17_09_2/views/users.php" title="Users"><i class="fa-solid fa-users"></i></a>
      <a class="btn btn-light btn-sm" href="/btlt5_17_09_2/views/tour/today_bookings_overview.php" title="Đặt hôm nay"><i class="fa-solid fa-calendar-check"></i></a>
      <a class="btn btn-outline-danger btn-sm" href="/btlt5_17_09_2/logout.php" title="Đăng xuất"><i class="fa-solid fa-sign-out-alt"></i></a>
    </div>
  </div>
</nav>

<div class="container py-5">

    <div class="row g-4 mb-5">
        <div class="col-sm-6 col-md-3">
            <div class="stat-card stat-card-1 text-center">
                <i class="fa-solid fa-plane-up stat-icon"></i> <div class="stat-number"><?php echo number_format($totalTours); ?></div>
                <div class="stat-label">Tổng số tour</div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="stat-card stat-card-2 text-center">
                <i class="fa-solid fa-ticket-airline stat-icon"></i> <div class="stat-number"><?php echo number_format($totalBookings); ?></div>
                <div class="stat-label">Tổng số đặt vé</div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="stat-card stat-card-3 text-center">
                <i class="fa-solid fa-money-bill-wave stat-icon"></i> <div class="stat-number"><?php echo number_format($totalRevenue,0,',','.'); ?> VNĐ</div>
                <div class="stat-label">Tổng doanh thu (Confirmed)</div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="stat-card stat-card-4 text-center">
                <i class="fa-solid fa-calendar-check stat-icon"></i> <div class="stat-number"><?php echo number_format($todayBookings); ?></div>
                <div class="stat-label">Đặt vé hôm nay</div>
            </div>
        </div>
    </div>
    
    <div class="section-header d-flex justify-content-between align-items-center">
        <h2 class="tour-title-main"><i class="fa-solid fa-map-marked-alt"></i> Quản lý Tuyến bay & Tour</h2>
        <a href="add_tour.php" class="btn-add"><i class="fa-solid fa-plus me-2"></i> Thêm Tour Mới</a>
    </div>
    
    <div class="section-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Danh sách tour</h3>
        <!-- Nút xem tổng quan đặt vé hôm nay (admin) -->
        <div>
            <a href="today_bookings_overview.php" class="btn btn-sm btn-success">
                <i class="fa-solid fa-calendar-check me-1"></i> Đặt hôm nay
            </a>
        </div>
    </div>
    
    <div class="row g-4">
        <?php if (!empty($tours)): ?>
            <?php foreach ($tours as $tour): ?>
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="card position-relative">
                        <div class="price-badge"><?= number_format($tour['price'], 0, ',', '.') ?>đ</div>
                        <img src="../../uploads/<?= htmlspecialchars($tour['image'] ?? 'no-image.jpg') ?>" alt="tour image" class="card-img-top">
                        <div class="card-body">
                            <div>
                                <h5 class="tour-name"><?= htmlspecialchars($tour['name']) ?></h5>
                                <p class="tour-desc">
                                    <?= htmlspecialchars(mb_substr($tour['description'], 0, 100, 'UTF-8')) . (mb_strlen($tour['description'], 'UTF-8') > 100 ? '...' : '') ?>
                                </p>
                                <p class="tour-date">
                                    <i class="fa-solid fa-calendar-days"></i> 
                                    <?= htmlspecialchars($tour['start_date']) ?> → <?= htmlspecialchars($tour['end_date']) ?>
                                </p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top: 1px solid rgba(0, 0, 0, 0.05);">
                                <div class="action-buttons">
                                    <a href="edit_tour.php?id=<?= $tour['id'] ?>" class="btn-action btn-edit"><i class="fa-solid fa-pen"></i></a>
                                    <a href="delete_tour.php?id=<?= $tour['id'] ?>" onclick="return confirm('Xác nhận xóa tour này?')" class="btn-action btn-delete"><i class="fa-solid fa-trash"></i></a>
                                </div>
                                <a href="view_tour.php?id=<?= $tour['id'] ?>" class="btn-action btn-detail"><i class="fa-solid fa-eye"></i> Xem</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center mt-4" role="alert" style="border-radius: 15px; background: rgba(255,255,255,0.7); backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.3); color: var(--text-dark);">
                    <i class="fa-solid fa-info-circle me-2"></i> Không có tour nào được tìm thấy.
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// set active nav item based on current path
(function(){
  const links = document.querySelectorAll('.nav-pills .nav-link');
  const path = location.pathname.replace(/\/+$/,'');
  links.forEach(a=>{
    try{
      const href = new URL(a.href).pathname.replace(/\/+$/,'');
      if(href === path) a.classList.add('active'); 
    }catch(e){}
  });
})();
</script>
</body>
</html>