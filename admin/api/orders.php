<?php
session_start();
require_once __DIR__ . '/../../includes/Order.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$order = new Order();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['order_number'])) {
        $orderDetails = $order->getOrderDetails($_GET['order_number']);
        echo json_encode($orderDetails);
    }
}
