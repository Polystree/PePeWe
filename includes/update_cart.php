
<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$productId = $data['productId'] ?? null;
$quantity = (int)($data['quantity'] ?? 0);

require_once __DIR__ . '/../includes/Database.php';
$db = Database::getInstance();

if ($quantity <= 0) {
    // Delete item
    $stmt = $db->prepare("DELETE FROM cart WHERE userId = ? AND productId = ?");
    $stmt->bind_param("ii", $_SESSION['userId'], $productId);
} else {
    // Update quantity
    $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE userId = ? AND productId = ?");
    $stmt->bind_param("iii", $quantity, $_SESSION['userId'], $productId);
}

$success = $stmt->execute();
echo json_encode(['success' => $success]);