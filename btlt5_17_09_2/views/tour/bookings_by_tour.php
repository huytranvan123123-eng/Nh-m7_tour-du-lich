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

$tour_id = isset($_GET['tour_id']) ? intval($_GET['tour_id']) : 0;
$filterDate = isset($_GET['date']) && $_GET['date'] === 'today';

if ($tour_id <= 0) {
    header('Location: list_tour.php');
    exit;
}

// Get tour name
$tourName = '(Không xác định)';
$stmtT = $conn->prepare("SELECT name FROM tours WHERE id = ?");
if ($stmtT) {
    $stmtT->bind_param("i", $tour_id);
    $stmtT->execute();
    $stmtT->bind_result($tn);
    if ($stmtT->fetch()) $tourName = $tn;
    $stmtT->close();
}

// Group by user using booking_date and total_price
$whereDate = $filterDate ? " AND b.booking_date = CURDATE() " : "";
$sql = "SELECT 
            COALESCE(u.username, 'Khách vãng lai') AS username,
            COUNT(*) AS orders_count,
            IFNULL(SUM(b.total_price),0) AS total_amount
        FROM bookings b
        LEFT JOIN users u ON u.id = b.user_id
        WHERE b.tour_id = ? $whereDate
        GROUP BY username
        ORDER BY total_amount DESC, orders_count DESC";

$stmt = $conn->prepare($sql);
$rows = [];
$overallTotal = 0.0;
if ($stmt) {
    $stmt->bind_param("i", $tour_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $r['total_amount'] = (float)$r['total_amount'];
        $overallTotal += $r['total_amount'];
        $rows[] = $r;
    }
    $stmt->close();
}
$conn->close();

// Include menu fallback
$menuCandidates = [
    __DIR__ . '/../menu.php',
    __DIR__ . '/menu.php',
    __DIR__ . '/../customer/menu.php',
    __DIR__ . '/../../menu.php',
];
$included = false;
foreach ($menuCandidates as $mp) {
    if (file_exists($mp)) {
        include $mp;
        $included = true;
        break;
    }
}
if (!$included) {
    echo '<nav class="navbar navbar-expand-lg navbar-light bg-light"><div class="container"><a class="navbar-brand" href="../../index.php"><i class="fa-solid fa-plane-departure"></i> Travel Explorer</a></div></nav>';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt vé theo user - Tour #<?= htmlspecialchars($tour_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php
// include menu fallback
$menuCandidates = [
    __DIR__ . '/../menu.php',
    __DIR__ . '/menu.php',
    __DIR__ . '/../customer/menu.php',
    __DIR__ . '/../../menu.php',
];
$included = false;
foreach ($menuCandidates as $mp) {
    if (file_exists($mp)) {
        include $mp;
        $included = true;
        break;
    }
}
if (!$included) {
    echo '<nav class="navbar navbar-expand-lg navbar-light bg-light"><div class="container"><a class="navbar-brand" href="../../index.php"><i class="fa-solid fa-plane-departure"></i> Travel Explorer</a></div></nav>';
}
?>
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Đặt vé — Tour: <?= htmlspecialchars($tourName) ?> (ID: <?= $tour_id ?>)</h4>
        <div>
            <a href="list_tour.php" class="btn btn-secondary btn-sm">← Quay lại</a>
            <a href="bookings_by_tour.php?tour_id=<?= $tour_id ?>" class="btn btn-outline-primary btn-sm">Tất cả</a>
            <a href="bookings_by_tour.php?tour_id=<?= $tour_id ?>&date=today" class="btn btn-outline-success btn-sm">Hôm nay</a>
        </div>
    </div>

    <?php if (empty($rows)): ?>
        <div class="alert alert-info">Không có đơn đặt (theo bộ lọc).</div>
    <?php else: ?>
        <div class="card">
            <div class="card-body">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Số đơn</th>
                            <th>Tổng tiền (VNĐ)</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($rows as $r): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><?= htmlspecialchars($r['username']) ?></td>
                                <td><?= (int)$r['orders_count'] ?></td>
                                <td><?= number_format($r['total_amount'], 0, ',', '.') ?>đ</td>
                                <td>
                                    <!-- Xóa tất cả booking của user cho tour này (nếu cần) -->
                                    <form method="post" action="/btlt5_17_09_2/handle/delete_booking.php" onsubmit="return confirm('Xác nhận xóa tất cả đặt vé của user này cho tour?');" style="display:inline">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="booking_id" value="0">
                                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($some_user_id ?? 0) ?>">
                                        <input type="hidden" name="tour_id" value="<?= $tour_id ?>">
                                        <input type="hidden" name="return_to" value="/btlt5_17_09_2/views/tour/bookings_by_tour.php?tour_id=<?= $tour_id ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Xóa toàn bộ</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-end">Tổng doanh thu</th>
                            <th><?= number_format($overallTotal, 0, ',', '.') ?>đ</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>