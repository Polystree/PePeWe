
<?php
require_once 'login/database.php';
session_start();

if (!isset($_GET['order_id'])) {
    echo json_encode(['error' => 'No order ID provided']);
    exit();
}

$orderId = $_GET['order_id'];
$stmt = $connect->prepare("SELECT status FROM orders WHERE order_id = ?");
$stmt->bind_param("s", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

echo json_encode([
    'status' => $order['status'] ?? 'unknown',
    'success' => in_array($order['status'], ['settlement', 'capture'])
]);
?>