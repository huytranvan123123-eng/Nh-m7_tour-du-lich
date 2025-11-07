<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../../login.php");
    exit();
}

require_once(__DIR__ . "/../../functions/db_connection.php");
$conn = getDbConnection();
$username = $_SESSION['username'];

// Lấy số dư (nếu có trong bảng users)
$balance = 0;
if ($stmt = $conn->prepare("SELECT balance FROM users WHERE username = ?")) {
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($bal);
    if ($stmt->fetch()) {
        $balance = $bal ?? 0;
    }
    $stmt->close();
}

// Lấy danh sách tour
$tours = [];
$sql = "SELECT * FROM tours ORDER BY start_date ASC";
$result = $conn->query($sql);

if ($result) {
    while ($tour = $result->fetch_assoc()) {
        $tours[] = $tour;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách Tour Du lịch - Travel Explorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- Biến màu sắc và Style đồng bộ (Giữ nguyên) --- */
        :root {
            --primary-color: #0b7af7; 
            --secondary-color: #10b981; 
            --danger-color: #ef4444; 
            --warning-color: #facc15; 
            --text-dark: #1a202c; 
            --text-muted: #718096; 
            --bg-main-start: #e0f2fe; 
            --bg-main-end: #d0e7ff;
            --card-shadow: 0 15px 45px rgba(0, 0, 0, 0.1); 
            --card-bg: rgba(255, 255, 255, 0.9);
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
        
        /* --- Navbar/Header --- */
        .main-header {
            background: linear-gradient(90deg, #1a202c 0%, #2d3748 100%);
            color: white;
            padding: 5px 0; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-content { display: flex; justify-content: space-between; align-items: center; }
        h1 {
            font-weight: 800;
            font-size: 1.5rem; 
            color: #ffffff !important; 
            margin: 0;
        }
        h1 i { color: var(--warning-color); margin-right: 10px; transform: rotate(-45deg); transition: transform 0.4s ease-out; }
        .action-links { display: flex; align-items: center; gap: 20px; }
        .user-info { color: white; font-weight: 500; font-size: 0.9rem; }
        .user-info b { color: var(--warning-color); font-weight: 700; }
        
        .btn-logout { 
            border-radius: 10px; 
            font-weight: 600; 
            color: var(--warning-color); 
            border: 2px solid var(--warning-color);
            padding: 6px 15px; 
            transition: all 0.3s ease;
            background: transparent;
            text-decoration: none;
            font-size: 0.9rem; 
        }
        .btn-logout:hover { 
            background-color: var(--warning-color); 
            color: var(--text-dark); 
            transform: translateY(-2px);
        }
        .btn-view-bookings {
            color: white;
            text-decoration: none;
            font-weight: 600;
            padding: 6px 12px; 
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            transition: background 0.3s;
            font-size: 0.9rem; 
        }
        .btn-view-bookings:hover { background: rgba(255, 255, 255, 0.2); color: white; }

        /* Balance / Topup styles */
       .balance-box {
            background: rgba(255,255,255,0.06);
            padding: 6px 12px;
            border-radius: 10px;
            font-weight: 700;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .balance-box b { color: var(--warning-color); }
        .btn-topup {
            border-radius: 10px;
            padding: 6px 12px;
            font-weight: 700;
            background: linear-gradient(90deg, var(--navy-blue), var(--primary-color));
            color: white;
            border: none;
        }
        .btn-topup:hover { opacity: 0.95; transform: translateY(-1px); }

        /* --- Card Tour --- */
        .card {
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            background: var(--card-bg); 
            backdrop-filter: blur(5px); 
            -webkit-backdrop-filter: blur(5px);
            transition: all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1);
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-7px) scale(1.01); 
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2); 
            border-color: rgba(255, 255, 255, 0.4);
        }

        .card-img-top {
            height: 250px;
            width: 100%;
            object-fit: cover;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        .price-tag {
            position: absolute; 
            top: 20px; 
            right: 20px; 
            background: linear-gradient(45deg, var(--danger-color), #dc2626); 
            padding: 8px 16px; 
            border-radius: 50px; 
            font-size: 1rem; 
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.4);
            color: white; 
            z-index: 10;
        }

        .card-body {
            padding: 25px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }
        .card-title {
            font-weight: 700; 
            color: var(--navy-blue); 
            font-size: 1.4rem; 
            margin-bottom: 10px; 
        }
        .card-text {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 20px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            min-height: 60px;
        }
        .tour-details {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 15px;
            font-weight: 600;
        }
        .tour-details i {
            color: var(--sky-blue);
            margin-right: 8px;
            font-size: 1.1rem;
        }

        /* Vùng chứa hai nút */
        .card-actions {
            display: flex;
            gap: 10px; 
            margin-top: auto;
        }
        
        /* --- Nút Đặt vé (Primary CTA) --- */
        .btn-book {
            flex-grow: 1; 
            background: linear-gradient(90deg, var(--navy-blue) 0%, var(--primary-color) 100%);
            color: white;
            border-radius: 10px; 
            padding: 12px 0; 
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 5px 15px rgba(14, 165, 233, 0.3);
            transition: all 0.3s ease;
            text-align: center;
            border: none;
            text-decoration: none; 
        }
        .btn-book:hover {
            background: linear-gradient(90deg, var(--primary-color) 0%, var(--sky-blue) 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(14, 165, 233, 0.5);
            color: #fff;
        }
        .btn-book i { margin-right: 8px; }
        
        /* --- Nút Xem Chi Tiết (Secondary CTA) --- */
        .btn-detail {
            width: 40px; 
            height: 40px; 
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            border: 2px solid var(--sky-blue); 
            color: var(--sky-blue); 
            background-color: transparent;
            transition: all 0.3s ease;
            text-decoration: none;
            align-self: flex-end; 
        }
        .btn-detail:hover {
            background-color: var(--sky-blue);
            color: white;
            box-shadow: 0 4px 10px rgba(14, 165, 233, 0.5);
            transform: scale(1.05);
        }

        /* Tiêu đề trang */
        .page-title {
            font-size: 2.2rem; 
            font-weight: 800; 
            color: var(--text-dark); 
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.08); 
            padding-bottom: 15px;
        }
        .page-title i { color: var(--sky-blue); margin-right: 12px; }
    </style>
</head>
<body>

<header class="main-header">
    <div class="container header-content">
        <h1><i class="fa-solid fa-plane-departure"></i> Travel Explorer</h1>
        <div class="action-links">
            <div class="user-info">
                Xin chào, <b><?php echo htmlspecialchars($username); ?></b>!
            </div>
            <div class="balance-box" title="Số dư tài khoản">
                <i class="fa-solid fa-wallet"></i>
                Số dư: <b><?php echo number_format($balance ?? 0, 0, ',', '.'); ?>đ</b>
            </div>
            <button type="button" class="btn-topup" data-bs-toggle="modal" data-bs-target="#topupModal">
                <i class="fa-solid fa-plus me-1"></i> Nạp tiền
            </button>
            <a href="bookings.php" class="btn-view-bookings">
                <i class="fa-solid fa-ticket-simple me-2"></i> Vé đã đặt
            </a>
            <a href="../../handle/logout.php" class="btn-logout">
                <i class="fa-solid fa-arrow-right-from-bracket me-2"></i> Đăng xuất
            </a>
        </div>
    </div>
</header>

<div class="container">
    
    <h2 class="page-title"><i class="fa-solid fa-route"></i> Khám phá các Tour Du lịch</h2>

    <div class="row g-4">
        <?php if (!empty($tours)): ?>
            <?php foreach ($tours as $tour): ?>
                <div class="col-sm-6 col-lg-4 col-xl-3">
                    <div class="card position-relative">
                        <img src="../../uploads/<?php echo htmlspecialchars($tour['image'] ?? 'placeholder.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($tour['name']); ?>">
                        
                        <div class="price-tag"><?php echo number_format($tour['price'], 0, ',', '.'); ?>đ</div>
                        
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($tour['name']); ?></h5>
                            
                            <?php if (isset($tour['start_date']) && isset($tour['end_date'])): ?>
                                <p class="tour-details">
                                    <i class="fa-solid fa-calendar-alt"></i> 
                                    Khởi hành: <?php echo htmlspecialchars($tour['start_date']); ?>
                                </p>
                            <?php endif; ?>

                            <p class="card-text">
                                <?php 
                                    $desc = htmlspecialchars($tour['description'] ?? 'Chưa có mô tả chi tiết.');
                                    echo mb_substr($desc, 0, 100, 'UTF-8') . (mb_strlen($desc, 'UTF-8') > 100 ? '...' : '');
                                ?>
                            </p>
                            
                            <div class="card-actions">
                                <a href="detail.php?id=<?php echo $tour['id']; ?>" class="btn-detail" title="Xem Chi Tiết">
                                    <i class="fa-solid fa-eye fa-lg"></i>
                                </a>
                                
                                <a href="book.php?id=<?php echo $tour['id']; ?>" class="btn-book">
                                    <i class="fa-solid fa-paper-plane"></i> Đặt vé
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center mt-5" role="alert" style="border-radius: 15px; background: rgba(255,255,255,0.7); backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.3); color: var(--text-dark);">
                    <i class="fa-solid fa-exclamation-circle me-2"></i> Rất tiếc, hiện tại không có tour nào để hiển thị.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nạp tiền -->
<div class="modal fade" id="topupModal" tabindex="-1" aria-labelledby="topupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="topupForm" method="post" action="../../handle/topup.php">
        <div class="modal-header">
          <h5 class="modal-title" id="topupModalLabel"><i class="fa-solid fa-wallet me-2"></i> Nạp tiền</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <div class="mb-3">
                <label for="amount" class="form-label">Số tiền (VNĐ)</label>
                <input type="number" class="form-control" id="amount" name="amount" min="1000" step="1000" placeholder="Ví dụ: 50000" required>
                <div class="form-text">Tiền sẽ được cộng vào số dư khi xử lý thành công.</div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-success">Xác nhận nạp</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('topupForm')?.addEventListener('submit', function(e){
    const amt = Number(document.getElementById('amount').value || 0);
    if (!amt || amt < 1000) {
        e.preventDefault();
        alert('Vui lòng nhập số tiền hợp lệ (tối thiểu 1.000đ).');
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>