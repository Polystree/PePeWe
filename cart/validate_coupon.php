<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    exit(json_encode(['success' => false]));
}

$data = json_decode(file_get_contents('php://input'), true);
$db = Database::getInstance();
$currentDate = date('Y-m-d');
$stmt = $db->prepare("SELECT discount FROM coupons WHERE code = ? AND expiry_date >= ?");
$stmt->bind_param("ss", $data['code'], $currentDate);
$stmt->execute();
$coupon = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'success' => (bool)$coupon,
    'discount' => $coupon ? (float)$coupon['discount'] : 0,
    'message' => 'Coupon applied successfully'
]);
