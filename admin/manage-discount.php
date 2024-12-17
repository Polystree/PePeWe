<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
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
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $discount_id = (int)$_POST['discount_id'];
        $stmt = $connect->prepare("DELETE FROM discounts WHERE id = ?");
        $stmt->bind_param("i", $discount_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete discount']);
        }
        exit;
    }

    $discount_id = isset($_POST['discount_id']) ? (int)$_POST['discount_id'] : null;
    $product_id = (int)$_POST['product_id'];
    $discount_percent = (int)$_POST['discount_percent'];
    $is_flash_sale = (int)$_POST['is_flash_sale'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if ($discount_id) {
        $stmt = $connect->prepare("UPDATE discounts SET product_id = ?, discount_percent = ?, 
            is_flash_sale = ?, start_date = ?, end_date = ? WHERE id = ?");
        $stmt->bind_param("iiissi", $product_id, $discount_percent, $is_flash_sale, 
            $start_date, $end_date, $discount_id);
    } else {
        $stmt = $connect->prepare("INSERT INTO discounts (product_id, discount_percent, 
            is_flash_sale, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $product_id, $discount_percent, $is_flash_sale, 
            $start_date, $end_date);
    }

    if ($stmt->execute()) {
        header('Location: index.php');
    } else {
        die("Error saving discount: " . $stmt->error);
    }
}
