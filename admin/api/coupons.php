<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$config = include(__DIR__ . '/../../config/config.php');
$db_config = $config['db'];

$connect = new mysqli(
    $db_config['host'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

if ($connect->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        $stmt = $connect->prepare("INSERT INTO coupons (code, discount, expiry_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sis", $data['code'], $data['discount'], $data['expiry_date']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        break;

    case 'PUT':
        $stmt = $connect->prepare("UPDATE coupons SET code = ?, discount = ?, expiry_date = ? WHERE id = ?");
        $stmt->bind_param("sisi", $data['code'], $data['discount'], $data['expiry_date'], $data['couponId']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        break;

    case 'DELETE':
        $stmt = $connect->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->bind_param("i", $data['id']);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $stmt->error]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

$connect->close();
