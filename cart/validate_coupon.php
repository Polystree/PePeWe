<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['userId'])) {
        throw new Exception('Please login first');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $couponCode = $data['code'] ?? '';

    if (empty($couponCode)) {
        throw new Exception('Coupon code is required');
    }

    $db = Database::getInstance();
    $currentDate = date('Y-m-d');

    $stmt = $db->prepare(
        "SELECT id, code, discount FROM coupons 
         WHERE code = ? AND expiry_date >= ?"
    );
    
    if (!$stmt) {
        throw new Exception('Database error');
    }

    $stmt->bind_param("ss", $couponCode, $currentDate);
    
    if (!$stmt->execute()) {
        throw new Exception('Database error');
    }

    $result = $stmt->get_result();
    $coupon = $result->fetch_assoc();

    if (!$coupon) {
        throw new Exception('Invalid or expired coupon code');
    }

    echo json_encode([
        'success' => true,
        'discount' => (float)$coupon['discount'],
        'message' => 'Coupon applied successfully!'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
