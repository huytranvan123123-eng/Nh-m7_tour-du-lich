<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../views/customer/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../views/customer/index.php");
    exit();
}

$amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
if ($amount < 1000) {
    // số tiền không hợp lệ
    header("Location: ../views/customer/index.php?topup=invalid");
    exit();
}

// Tạo mã giao dịch tạm
$txn = uniqid('tp_', true);

// Lưu tạm vào session (hoặc bạn có thể lưu vào bảng pending_topups)
$_SESSION['pending_topup'] = [
    'username' => $_SESSION['username'],
    'amount' => $amount,
    'txn' => $txn,
    'created_at' => time()
];

// Chuyển tới trang hướng dẫn chuyển khoản
header("Location: ../views/customer/topup_payment.php");
exit();