<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') exit();

$config = include(__DIR__ . '/../config/config.php');
$connect = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $discount_id = (int)$_POST['discount_id'];
        $stmt = $connect->prepare("DELETE FROM discounts WHERE id = ?");
        $stmt->bind_param("i", $discount_id);
        echo json_encode(['success' => $stmt->execute()]);
        exit;
    }

    $product_id = (int)$_POST['product_id'];
    $discount_percent = (int)$_POST['discount_percent'];
    $is_flash_sale = (int)$_POST['is_flash_sale'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $discount_id = isset($_POST['discount_id']) ? (int)$_POST['discount_id'] : null;

    if ($discount_id) {
        $stmt = $connect->prepare("UPDATE discounts SET product_id = ?, discount_percent = ?, is_flash_sale = ?, start_date = ?, end_date = ? WHERE id = ?");
        $stmt->bind_param("iiissi", $product_id, $discount_percent, $is_flash_sale, $start_date, $end_date, $discount_id);
    } else {
        $stmt = $connect->prepare("INSERT INTO discounts (product_id, discount_percent, is_flash_sale, start_date, end_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiss", $product_id, $discount_percent, $is_flash_sale, $start_date, $end_date);
    }
    
    $stmt->execute() ? header('Location: index.php') : die("Error saving discount");
}
