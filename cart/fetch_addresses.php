<?php
require_once __DIR__ . '/../includes/Database.php'; session_start(); header('Content-Type: application/json');
if (!isset($_SESSION['userId'])) exit(json_encode(['success' => false]));
($stmt = Database::getInstance()->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC"))->bind_param("i", $_SESSION['userId']); $stmt->execute();
echo json_encode(['success' => true, 'addresses' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
