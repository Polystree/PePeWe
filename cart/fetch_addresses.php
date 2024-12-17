<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['userId'])) {
        throw new Exception('Not logged in');
    }

    $db = Database::getInstance();
    $stmt = $db->prepare(
        "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC"
    );
    $stmt->bind_param("i", $_SESSION['userId']);
    $stmt->execute();
    $result = $stmt->get_result();
    $addresses = $result->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'success' => true,
        'addresses' => $addresses,
        'hasAddresses' => count($addresses) > 0
    ]);

} catch (Exception $e) {
    error_log('Address fetch error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'hasAddresses' => false
    ]);
}
