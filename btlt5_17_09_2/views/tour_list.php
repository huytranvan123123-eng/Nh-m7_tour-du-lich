<?php
require_once __DIR__ . '/../functions/auth.php';
checkLogin(__DIR__ . '/../index.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>🏖 Danh sách tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include './menu.php'; ?>

<div class="container mt-4">
    <h3 class="mb-3">🏖 DANH SÁCH TOUR DU LỊCH</h3>
    <a href="../handle/tour_process.php?action=create" class="btn btn-primary mb-3">➕ Thêm tour mới</a>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Tên tour</th>
                <th>Địa điểm</th>
                <th>Giá (VNĐ)</th>
                <th>Ngày khởi hành</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php
            require_once '../handle/tour_process.php';
            $tours = getAllTours();

            if (empty($tours)) {
                echo '<tr><td colspan="6" class="text-center text-muted">Chưa có tour nào</td></tr>';
            } else {
                foreach ($tours as $t) {
                    echo "<tr>
                        <td>{$t['id']}</td>
                        <td>{$t['name']}</td>
                        <td>{$t['location']}</td>
                        <td>" . number_format($t['price'], 0, ',', '.') . "</td>
                        <td>{$t['departure_date']}</td>
                        <td>
                            <a href='../handle/tour_process.php?action=edit&id={$t['id']}' class='btn btn-warning btn-sm'>Sửa</a>
                            <a href='../handle/tour_process.php?action=delete&id={$t['id']}' class='btn btn-danger btn-sm'
                               onclick='return confirm(\"Bạn có chắc muốn xóa tour này?\")'>Xóa</a>
                        </td>
                    </tr>";
                }
            }
            ?>
        </tbody>
    </table>
</div>
</body>
</html>
