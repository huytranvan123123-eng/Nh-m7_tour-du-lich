<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

require_once __DIR__ . '/../../functions/db_connection.php';
$conn = getDbConnection();

// Tạo CSRF token nếu chưa có
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Summary per user for today (uses booking_date and total_price)
$sql = "SELECT 
            COALESCE(u.username, 'Khách vãng lai') AS username,
            COUNT(*) AS orders_count,
            IFNULL(SUM(b.total_price),0) AS total_amount
        FROM bookings b
        LEFT JOIN users u ON u.id = b.user_id
        WHERE b.booking_date = CURDATE()
        GROUP BY username
        ORDER BY total_amount DESC, orders_count DESC";
$summary = [];
$overall = 0;
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $r['total_amount'] = (float)$r['total_amount'];
        $overall += $r['total_amount'];
        $summary[] = $r;
    }
    $stmt->close();
}

// Details of today's bookings
$details = [];
$sql2 = "SELECT b.id, b.tour_id, t.name AS tour_name, COALESCE(u.username, 'Khách vãng lai') AS username, b.total_price AS amount, b.booking_date AS dt
         FROM bookings b
         LEFT JOIN users u ON u.id = b.user_id
         LEFT JOIN tours t ON t.id = b.tour_id
         WHERE b.booking_date = CURDATE()
         ORDER BY dt DESC, b.id DESC";
if ($res2 = $conn->query($sql2)) {
    while ($row = $res2->fetch_assoc()) {
        $details[] = $row;
    }
}
$conn->close();

// include menu (try common locations)
$menuCandidates = [
    __DIR__ . '/../menu.php',
    __DIR__ . '/menu.php',
    __DIR__ . '/../customer/menu.php',
    __DIR__ . '/../../menu.php',
];
$included = false;
foreach ($menuCandidates as $mp) {
    if (file_exists($mp)) { include $mp; $included = true; break; }
}
if (!$included) {
    // fallback minimal nav
    $baseUrl = '/btlt5_17_09_2';
    echo '<nav class="navbar navbar-expand-lg navbar-dark" style="background:linear-gradient(90deg,#0ea5e9,#1e3a8a);"><div class="container"><a class="navbar-brand" href="'.$baseUrl.'/index.php"><i class="fa-solid fa-plane-departure"></i> Travel Explorer</a></div></nav>';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Đặt vé hôm nay - Tổng quan</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root{
  --bg-1: #f6fbff;
  --card-bg: rgba(255,255,255,0.75);
  --accent: linear-gradient(90deg,#0ea5e9,#1e3a8a);
  --muted: #6b7280;
  --glass-shadow: 0 6px 30px rgba(16,24,40,0.08);
}
body{
  font-family: 'Inter',system-ui,Arial,Helvetica,sans-serif;
  background: radial-gradient(1200px 600px at 10% 10%, rgba(14,165,233,0.06), transparent 8%),
              radial-gradient(1000px 500px at 90% 90%, rgba(30,58,138,0.04), transparent 8%),
              var(--bg-1);
  -webkit-font-smoothing:antialiased;
  color:#0f172a;
  padding-bottom:60px;
}
.container.my-4{ max-width:1200px; }

/* Header */
.page-header {
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:16px;
  margin-bottom:18px;
}
.page-title {
  display:flex;
  align-items:center;
  gap:12px;
  font-weight:700;
  color:#0f172a;
}
.page-title i { color:#0ea5e9; font-size:1.35rem; }

/* Cards */
.card.glass {
  background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(255,255,255,0.82));
  border: 1px solid rgba(15,23,42,0.06);
  border-radius:14px;
  box-shadow: var(--glass-shadow);
  transition:transform .18s ease, box-shadow .18s ease;
}
.card.glass:hover{ transform:translateY(-6px); box-shadow:0 14px 40px rgba(16,24,40,0.12); }

/* Summary list */
.summary-list { gap:10px; display:flex; flex-direction:column; }
.summary-item { display:flex; align-items:center; justify-content:space-between; padding:10px 12px; border-radius:10px; background:linear-gradient(90deg, rgba(14,165,233,0.04), rgba(30,58,138,0.02)); }
.summary-meta { display:flex; align-items:center; gap:12px; }
.avatar {
  width:42px; height:42px; border-radius:10px; display:inline-grid; place-items:center;
  background:linear-gradient(180deg,#fff,#eef6ff);
  border:1px solid rgba(14,165,233,0.12);
  font-weight:600; color: #0f172a;
}

/* Table styles */
.table thead th { border-bottom: 2px solid rgba(15,23,42,0.06); font-weight:600; color:#0f172a; }
.table tbody tr { transition: background .12s ease, transform .08s ease; }
.table tbody tr:hover { background:linear-gradient(90deg, rgba(14,165,233,0.03), rgba(30,58,138,0.02)); transform:translateY(-2px); }
.table td, .table th { vertical-align:middle; }
.small-muted { color:var(--muted); font-size:0.9rem; }

/* Actions */
.btn-danger.btn-sm { border-radius:8px; padding:4px 8px; }
.btn-outline { border-radius:10px; padding:6px 12px; font-weight:600; }

/* Responsive */
@media (max-width:900px){
  .summary-list { flex-direction:row; flex-wrap:wrap; }
  .summary-item { min-width:48%; }
  .page-header { flex-direction:column; align-items:flex-start; gap:10px; }
}
</style>
</head>
<body>

<div class="container my-4">
    <div class="page-header">
        <div class="page-title">
            <i class="fa-solid fa-calendar-check fa-lg"></i>
            <div>
                <div style="font-size:1.1rem">Đặt vé hôm nay — Tổng quan</div>
                <div class="small-muted">Danh sách người đặt và tổng doanh thu theo user hôm nay</div>
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap:8px">
            <a href="list_tour.php" class="btn btn-outline-secondary btn-sm">← Quay lại</a>
            <a href="#" class="btn btn-outline btn-sm"><i class="fa-solid fa-download me-1"></i> Xuất CSV</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card glass p-3">
                <h6 class="mb-3" style="font-weight:700">Doanh thu theo user (hôm nay)</h6>

                <?php if (empty($summary)): ?>
                    <div class="alert alert-info mb-0">Chưa có đặt vé hôm nay.</div>
                <?php else: ?>
                    <div class="summary-list">
                        <?php foreach ($summary as $s): ?>
                            <div class="summary-item">
                                <div class="summary-meta">
                                    <div class="avatar"><?= strtoupper(substr($s['username'],0,2)) ?></div>
                                    <div>
                                        <div style="font-weight:700"><?= htmlspecialchars($s['username']) ?></div>
                                        <div class="small-muted"><?= (int)$s['orders_count'] ?> đơn</div>
                                    </div>
                                </div>
                                <div style="text-align:right">
                                    <div style="font-weight:700; color:#0f172a;"><?= number_format($s['total_amount'],0,',','.') ?>đ</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div class="small-muted">Tổng số user: <strong><?= count($summary) ?></strong></div>
                        <div class="h6 mb-0" style="color:#0f172a">Tổng hôm nay: <strong><?= number_format($overall,0,',','.') ?>đ</strong></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card glass p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0" style="font-weight:700">Chi tiết đặt hôm nay</h6>
                    <div class="small-muted">Sắp xếp theo thời gian mới nhất</div>
                </div>

                <?php if (empty($details)): ?>
                    <div class="alert alert-info mb-0">Không có chi tiết đặt hôm nay.</div>
                <?php else: ?>
                    <div class="table-responsive" style="max-height:520px;overflow:auto">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tour</th>
                                    <th>Username</th>
                                    <th class="text-end">Số tiền</th>
                                    <th>Ngày đặt</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($details as $d): ?>
                                    <tr>
                                        <td style="width:64px"><?= htmlspecialchars($d['id']) ?></td>
                                        <td style="min-width:180px"><?= htmlspecialchars($d['tour_name'] ?? '—') ?></td>
                                        <td style="min-width:140px"><?= htmlspecialchars($d['username']) ?></td>
                                        <td class="text-end" style="width:120px"><?= number_format($d['amount'],0,',','.') ?>đ</td>
                                        <td style="width:140px" class="small-muted"><?= htmlspecialchars($d['dt']) ?></td>
                                        <td style="width:120px">
                                            <form method="post" action="/btlt5_17_09_2/handle/delete_booking.php" onsubmit="return confirm('Xác nhận xóa đặt vé này?');" style="display:inline">
                                                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($d['id']) ?>">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                                <input type="hidden" name="return_to" value="/btlt5_17_09_2/views/tour/today_bookings_overview.php">
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i> Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>