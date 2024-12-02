<?php
require_once 'midtrans_config.php';
include 'login/database.php';

try {
    $notificationBody = file_get_contents('php://input');
    error_log('Notification received: ' . $notificationBody);

    $notification = new \Midtrans\Notification();
    
    $order_id = $notification->order_id;
    $transaction_status = $notification->transaction_status;
    $payment_type = $notification->payment_type;
    
    error_log("Processing order: $order_id, status: $transaction_status");

    $connect->begin_transaction();

    $stmt = $connect->prepare("UPDATE orders SET status = ?, payment_type = ? WHERE order_id = ?");
    $stmt->bind_param("sss", $transaction_status, $payment_type, $order_id);
    $stmt->execute();

    if ($transaction_status == 'settlement' || $transaction_status == 'capture') {
        $stmt = $connect->prepare("DELETE FROM cart WHERE userId = (SELECT user_id FROM orders WHERE order_id = ?)");
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
    }

    $connect->commit();
    error_log("Transaction completed successfully");

    http_response_code(200);
} catch (\Exception $e) {
    $connect->rollback();
    error_log('Notification error: ' . $e->getMessage());
    http_response_code(500);
    exit;
}
?>