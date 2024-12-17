<?php
session_start();

if (!isset($_SESSION['userId'])) {
    header('Location: /login');
    exit();
}

$config = include(__DIR__ . '/../config/config.php');
$db_config = $config['db'];

$connect = new mysqli(
    $db_config['host'],
    $db_config['username'], 
    $db_config['password'],
    $db_config['database']
);

if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['userId'];
    $productId = $_POST['productId'];
    $quantity = (int)$_POST['quantity'];
    
    $stmt = $connect->prepare("SELECT name as product_name, price, image_path FROM products WHERE productId = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    $redirect_url = $_SERVER['HTTP_REFERER'] ?? '/cart';

    $stmt = $connect->prepare("SELECT cartId, quantity FROM cart WHERE userId = ? AND productId = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $newQuantity = $row['quantity'] + $quantity;
        $stmt = $connect->prepare("UPDATE cart SET quantity = ? WHERE cartId = ?");
        $stmt->bind_param("ii", $newQuantity, $row['cartId']);
    } else {
        $stmt = $connect->prepare("INSERT INTO cart (userId, productId, product_name, price, quantity, image_path) 
                                 VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissis", $userId, $productId, $product['product_name'], 
                         $product['price'], $quantity, $product['image_path']);
    }

    if ($stmt->execute()) {
        header('Location: ' . $redirect_url);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}