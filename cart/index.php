<?php
session_start();

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Cart.php';

// Check both session variables to ensure user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['userId'])) {
    header('Location: /login');
    exit();
}

$userId = $_SESSION['userId'];
$cart = new Cart();

// Handle POST requests for quantity updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $productId => $quantity) {
        $cart->updateQuantity($userId, $productId, (int)$quantity);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$cartItems = $cart->getCartItems($userId);
$totalPrice = array_reduce($cartItems, function($carry, $item) {
    return $carry + ($item['price'] * $item['quantity']);
}, 0);

require_once __DIR__ . '/../templates/cart/index.php';