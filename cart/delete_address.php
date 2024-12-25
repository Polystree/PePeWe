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
    $stmt = $db->prepare(
        "DELETE FROM user_addresses 
         WHERE id = ? AND user_id = ?"
    );
    $stmt->bind_param("ii", $addressId, $_SESSION['userId']);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to delete address');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
