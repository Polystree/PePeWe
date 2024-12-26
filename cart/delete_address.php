<?php
require_once __DIR__ . '/../includes/Database.php'; session_start(); header('Content-Type: application/json');
if (!isset($_SESSION['userId'])) exit(json_encode(['success' => false]));
$data = json_decode(file_get_contents('php://input'), true);
echo json_encode(['success' => Database::getInstance()->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?")->bind_param("ii", $data['address_id'], $_SESSION['userId'])->execute()]);
