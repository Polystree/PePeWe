<?php
session_start();
require_once __DIR__ . '/../includes/Cart.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? null;
$quantity = $input['quantity'] ?? null;

if ($productId === null || $quantity === null) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit();
}

$cart = new Cart();
$success = $cart->updateQuantity($_SESSION['userId'], $productId, (int)$quantity);

echo json_encode(['success' => $success]);
