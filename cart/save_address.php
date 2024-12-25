<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    die(json_encode(['success' => false, 'error' => 'Not logged in']));
}

$input = json_decode(file_get_contents('php://input'), true);
$addressLabel = $input['city'] . ' - ' . substr($input['address'], 0, 30) . '...';

$db = Database::getInstance();
$stmt = $db->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['userId']);
$stmt->execute();

// Insert new address as default
$stmt = $db->prepare(
    "INSERT INTO user_addresses (
        user_id, address_label, recipient_name, phone, 
        address, city, postal_code, is_default
    ) VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)"
);

$stmt->bind_param("issssss", 
    $_SESSION['userId'],
    $addressLabel,
    $input['recipient_name'],
    $input['phone'],
    $input['address'],
    $input['city'],
    $input['postal_code']
);

$stmt->execute();
$newAddressId = $stmt->insert_id;

echo json_encode([
    'success' => true,
    'address_id' => $newAddressId,
    'address_label' => $addressLabel
]);