<?php
session_start();
require_once 'login/database.php';

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = $_SESSION['userId'];
$productName = $input['product_name'];
$quantity = (int) $input['quantity'];

try {
    if ($quantity > 0) {
        $stmt = $connect->prepare("UPDATE cart SET quantity = ? WHERE product_name = ? AND userId = ?");
        $stmt->bind_param("isi", $quantity, $productName, $userId);
    } else {
        $stmt = $connect->prepare("DELETE FROM cart WHERE product_name = ? AND userId = ?");
        $stmt->bind_param("si", $productName, $userId);
    }
    
    $result = $stmt->execute();
    
    $stmt = $connect->prepare("SELECT SUM(price * quantity) as total FROM cart WHERE userId = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $totalResult = $stmt->get_result();
    $total = $totalResult->fetch_assoc()['total'];
    
    echo json_encode([
        'success' => $result,
        'total' => $total
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>