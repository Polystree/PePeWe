<?php
session_start();
require_once __DIR__ . '/../includes/Cart.php';
$config = require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['userId'])) {
    header('Location: /login');
    exit();
}

$userId = $_SESSION['userId'];
$cart = new Cart();
$cartItems = $cart->getCartItems($userId);
$totalPrice = 0;

foreach ($cartItems as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

$title = 'Shopping Cart - iniGadget';

include __DIR__ . '/../templates/header.php';
include __DIR__ . '/../templates/cart/index.php';
include __DIR__ . '/../templates/footer.php';