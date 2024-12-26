<?php
require_once __DIR__ . '/../includes/Cart.php'; session_start(); header('Content-Type: application/json');
if (!isset($_SESSION['userId'])) exit(json_encode(['success' => false]));
$input = json_decode(file_get_contents('php://input'), true);
echo json_encode(['success' => (new Cart())->updateQuantity($_SESSION['userId'], $input['product_id'] ?? 0, (int)($input['quantity'] ?? 0))]);
