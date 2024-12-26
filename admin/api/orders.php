<?php
session_start();
require_once __DIR__ . '/../../includes/Order.php';
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') exit();
header('Content-Type: application/json');
$order = new Order();
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['order_number'])) {
    echo json_encode($order->getOrderDetails($_GET['order_number']));
}
