<?php
require_once __DIR__ . '/../includes/Database.php'; session_start(); header('Content-Type: application/json');
if (!isset($_SESSION['userId']) || !($data = json_decode(file_get_contents('php://input'), true))['address_id']) exit(json_encode(['success' => false]));
$db = Database::getInstance()->prepare("UPDATE user_addresses SET is_default = FALSE WHERE user_id = ?")->bind_param("i", $_SESSION['userId'])->execute();
echo json_encode(['success' => $db->prepare("UPDATE user_addresses SET is_default = TRUE WHERE id = ? AND user_id = ?")->bind_param("ii", $data['address_id'], $_SESSION['userId'])->execute()]);
