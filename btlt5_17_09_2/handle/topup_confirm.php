<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../views/customer/index.php");
    exit();
}

$pending = $_SESSION['pending_topup'] ?? null;
if (!$pending || $pending['username'] !== $_SESSION['username']) {
    header("Location: ../views/customer/index.php");
    exit();
}

// optional: verify txn from POST matches session
if (!isset($_POST['txn']) || $_POST['txn'] !== $pending['txn']) {
    header("Location: ../views/customer/topup_payment.php");
    exit();
}

require_once(__DIR__ . "/../functions/db_connection.php");
$conn = getDbConnection();

$amount = intval($pending['amount']);
$username = $_SESSION['username'];

if ($amount <= 0) {
    header("Location: ../views/customer/index.php?topup=invalid");
    exit();
}

// Cập nhật số dư (thực hiện bằng giao dịch)
$conn->begin_transaction();
$stmt = $conn->prepare("UPDATE users SET balance = COALESCE(balance,0) + ? WHERE username = ?");
if ($stmt) {
    $stmt->bind_param("is", $amount, $username);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        $conn->commit();
        // xóa pending
        unset($_SESSION['pending_topup']);
        $conn->close();
        header("Location: ../views/customer/index.php?topup=success");
        exit();
    }
}
$conn->rollback();
$conn->close();
header("Location: ../views/customer/index.php?topup=failed");
exit();