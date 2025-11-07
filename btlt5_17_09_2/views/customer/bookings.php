<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

require_once("../../functions/db_connection.php");
$conn = getDbConnection();

$username = $_SESSION['username'];

// Lấy ID người dùng
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("Không tìm thấy người dùng!");
}
$user_id = $user['id'];

// Lấy danh sách các vé đã đặt
$query = "
    SELECT b.id AS booking_id, t.name AS tour_name, t.price, b.booking_date, b.status, b.quantity, b.total_price, t.description, t.start_date, t.end_date
    FROM bookings b
    JOIN tours t ON b.tour_id = t.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Vé đã đặt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f5f7fb; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { margin-top: 40px; }
        h1 { color: #1a2b6d; font-weight: 700; }
        .table { background-color: #fff; border-radius: 12px; overflow: hidden; }

        .btn-view, .btn-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            color: #fff;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            padding: 8px 18px;
            transition: 0.2s;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            text-decoration: none;
        }
        .btn-view {
            background: linear-gradient(90deg, #1e3a8a 0%, #007bff 100%);
        }
        .btn-view:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-delete {
            background: linear-gradient(90deg, #dc3545 0%, #f87171 100%);
        }
        .btn-delete:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-back { background-color: #1a2b6d; color: white; border-radius: 8px; padding: 8px 16px; text-decoration: none; }
        .btn-back:hover { background-color: #13204d; }
    </style>
</head>
<body>
<div class="container">
    <h1>🎟️ Vé bạn đã đặt</h1>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered mt-4">
            <thead class="table-primary">
                <tr>
                    <th>Tên tour</th>
                    <th>Giá</th>
                    <th>Ngày đặt</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['tour_name']); ?></td>
                        <td><?= number_format($row['price']); ?> đ</td>
                        <td><?= $row['booking_date']; ?></td>
                        <td>
                            <!-- Nút Xem -->
                            <button class="btn-view" data-bs-toggle="modal" data-bs-target="#detailModal<?= $row['booking_id']; ?>">
                                <i class="bi bi-eye"></i> Xem
                            </button>

                            <!-- Nút Xóa -->
                            <a href="delete_booking.php?id=<?= $row['booking_id']; ?>"
                               class="btn-delete"
                               onclick="return confirm('Bạn có chắc muốn xóa vé này không?');">
                                <i class="bi bi-trash"></i> Xóa
                            </a>
                        </td>
                    </tr>

                    <!-- Modal xem chi tiết -->
                    <div class="modal fade" id="detailModal<?= $row['booking_id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">Chi tiết vé - <?= htmlspecialchars($row['tour_name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Tên tour:</strong> <?= htmlspecialchars($row['tour_name']); ?></p>
                                    <p><strong>Giá:</strong> <?= number_format($row['price']); ?> đ</p>
                                    <p><strong>Số lượng:</strong> <?= $row['quantity']; ?></p>
                                    <p><strong>Tổng tiền:</strong> <?= number_format($row['total_price']); ?> đ</p>
                                    <p><strong>Ngày đặt:</strong> <?= $row['booking_date']; ?></p>
                                    <p><strong>Trạng thái:</strong> <?= ucfirst($row['status']); ?></p>
                                    <hr>
                                    <p><strong>Mô tả tour:</strong><br> <?= nl2br(htmlspecialchars($row['description'])); ?></p>
                                    <p><strong>Thời gian:</strong> <?= $row['start_date']; ?> → <?= $row['end_date']; ?></p>
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="mt-4">Bạn chưa đặt tour nào.</p>
    <?php endif; ?>

    <a href="index.php" class="btn-back mt-3 d-inline-block">⬅️ Quay lại danh sách tour</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
