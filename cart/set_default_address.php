<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $addressId = $data['address_id'] ?? null;
    
    if (!$addressId) {
        throw new Exception('Invalid address ID');
    }

    $db = Database::getInstance();
    
    $stmt = $db->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['userId']);
    $stmt->execute();
    
    $stmt = $db->prepare(
        "UPDATE user_addresses 
         SET is_default = TRUE 
         WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $addressId, $_SESSION['userId']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to set default address');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
