<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new Exception('Invalid request method');
}

$data = json_decode(file_get_contents('php://input'), true);
$couponCode = $data['code'] ?? '';

if (empty($couponCode)) {
    throw new Exception('Coupon code is required');
}

$db = Database::getInstance();

$stmt = $db->prepare("SELECT * FROM coupons WHERE code = ?");
if (!$stmt) {
    throw new Exception('Database prepare error: ' . $db->error);
}

$stmt->bind_param("s", $couponCode);
if (!$stmt->execute()) {
    throw new Exception('Database execute error: ' . $stmt->error);
}

$result = $stmt->get_result();
$coupon = $result->fetch_assoc();

if (!$coupon) {
    throw new Exception('Invalid coupon code');
}

if ($coupon['expiry_date'] && strtotime($coupon['expiry_date']) < time()) {
    throw new Exception('Coupon has expired');
}

echo json_encode([
    'success' => true,
    'discount' => (int)$coupon['discount'],
    'message' => 'Coupon applied successfully'
]);
