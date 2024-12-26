<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') exit();

$config = include(__DIR__ . '/../../config/config.php');
$connect = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        $stmt = $connect->prepare("INSERT INTO coupons (code, discount, expiry_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $data['code'], $data['discount'], $data['expiry_date']);
        echo json_encode(['success' => $stmt->execute()]);
        break;
    case 'PUT':
        $stmt = $connect->prepare("UPDATE coupons SET code = ?, discount = ?, expiry_date = ? WHERE id = ?");
        $stmt->bind_param("sisi", $data['code'], $data['discount'], $data['expiry_date'], $data['couponId']);
        echo json_encode(['success' => $stmt->execute()]);
        break;
    case 'DELETE':
        $stmt = $connect->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param("i", $data['id']);
        echo json_encode(['success' => $stmt->execute()]);
        break;
}
$connect->close();
