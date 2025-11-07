<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../views/tour/list_tour.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/tour/list_tour.php');
    exit;
}

$token = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    header('Location: ../views/tour/list_tour.php?msg=csrf');
    exit;
}

$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$tour_id = isset($_POST['tour_id']) ? intval($_POST['tour_id']) : 0;
$postedReturn = $_POST['return_to'] ?? '';

require_once __DIR__ . '/../functions/db_connection.php';
$conn = getDbConnection();

$ok = false;

if ($booking_id > 0) {
    $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $booking_id);
        $ok = $stmt->execute();
        $stmt->close();
    }
} elseif ($user_id > 0 && $tour_id > 0) {
    $stmt = $conn->prepare("DELETE FROM bookings WHERE user_id = ? AND tour_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $user_id, $tour_id);
        $ok = $stmt->execute();
        $stmt->close();
    }
}

$conn->close();

/*
 Build absolute return URL to avoid duplicated project path or relative resolution.
 Expect project is served from '/btlt5_17_09_2'.
*/
$projectBase = '/btlt5_17_09_2';
$defaultPath = $projectBase . '/views/tour/today_bookings_overview.php';

$rt = trim($postedReturn);
$path = $defaultPath;

if ($rt !== '') {
    if (preg_match('#^https?://#i', $rt)) {
        // full URL provided by caller
        $return_to = $rt;
    } else {
        // make sure rt starts with a single slash
        if ($rt[0] !== '/') $rt = '/' . ltrim($rt, '/');
        // if rt already contains project base at start, use it; else prepend project base
        if (strpos($rt, $projectBase) === 0) {
            $path = $rt;
        } else {
            $path = $projectBase . $rt;
        }
        // build absolute URL
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $return_to = $scheme . '://' . $host . $path;
    }
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $return_to = $scheme . '://' . $host . $defaultPath;
}

// Append deleted flag
$sep = (strpos($return_to, '?') === false) ? '?' : '&';
header('Location: ' . $return_to . $sep . 'deleted=' . ($ok ? '1' : '0'));
exit;