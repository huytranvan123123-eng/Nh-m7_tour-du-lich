<?php
require_once __DIR__ . '/db_connection.php';

function getAllTours() {
    $conn = getDbConnection(); // ✅ gọi hàm để lấy kết nối
    $sql = "SELECT * FROM tours ORDER BY id DESC";
    $result = $conn->query($sql);

    $tours = [];
    while ($row = $result->fetch_assoc()) {
        $tours[] = $row;
    }
    return $tours;
}

function getTourById($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createTour($tour_name, $description, $price, $start_date, $end_date) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO tours (tour_name, description, price, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $tour_name, $description, $price, $start_date, $end_date);
    return $stmt->execute();
}

function updateTour($id, $tour_name, $description, $price, $start_date, $end_date) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE tours SET tour_name=?, description=?, price=?, start_date=?, end_date=? WHERE id=?");
    $stmt->bind_param("ssdssi", $tour_name, $description, $price, $start_date, $end_date, $id);
    return $stmt->execute();
}

function deleteTour($id) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM tours WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>