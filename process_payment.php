<?php
require_once 'midtrans_config.php';
session_start();

$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['user_id'];
$amount = $input['amount'];

try {
    $order_id = 'ORDER-' . time();
    
    include 'login/database.php';
    $stmt = $connect->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $transaction_details = array(
        'order_id' => $order_id,
        'gross_amount' => $amount
    );

    $customer_details = array(
        'first_name' => $user['username'],
        'email' => $user['email']
    );

    $transaction = array(
        'transaction_details' => $transaction_details,
        'customer_details' => $customer_details
    );

    $snapToken = \Midtrans\Snap::getSnapToken($transaction);
    echo json_encode(['token' => $snapToken]);
    
    $stmt = $connect->prepare("INSERT INTO orders (order_id, user_id, amount, status, payment_type) VALUES (?, ?, ?, 'pending', '')");
    $stmt->bind_param("sid", $order_id, $userId, $amount);
    $stmt->execute();

} catch (\Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>