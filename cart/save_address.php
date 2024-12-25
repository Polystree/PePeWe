<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $db = Database::getInstance();

    $stmt = $db->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['userId']);
    $stmt->execute();

    if (isset($data['address_id']) && $data['address_id'] !== 'new') {
        $stmt = $db->prepare(
            "UPDATE user_addresses SET 
                recipient_name = ?, 
                phone = ?,
                address = ?,
                city = ?,
                postal_code = ?,
                is_default = TRUE
            WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param("sssssii", 
            $data['recipient_name'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['postal_code'],
            $data['address_id'],
            $_SESSION['userId']
        );
    } else {
        $stmt = $db->prepare(
            "INSERT INTO user_addresses (
                user_id, recipient_name, phone, address, city, 
                postal_code, is_default, address_label
            ) VALUES (?, ?, ?, ?, ?, ?, TRUE, ?)"
        );
        $addressLabel = $data['city'] . ' - ' . substr($data['address'], 0, 30) . '...';
        $stmt->bind_param("issssss", 
            $_SESSION['userId'],
            $data['recipient_name'],
            $data['phone'],
            $data['address'],
            $data['city'],
            $data['postal_code'],
            $addressLabel
        );
    }

    if ($stmt->execute()) {
        $addressId = $data['address_id'] ?? $stmt->insert_id;
        echo json_encode([
            'success' => true,
            'address_id' => $addressId
        ]);
    } else {
        throw new Exception('Failed to save address');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}