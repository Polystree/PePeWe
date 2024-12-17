<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/Payment.php';

$config = require_once __DIR__ . '/../config/midtrans_config.php';
\Midtrans\Config::$serverKey = $config['server_key'];
\Midtrans\Config::$isProduction = $config['is_production'];

try {
    $notification = new \Midtrans\Notification();
    
    $orderId = $notification->order_id;
    $status = $notification->transaction_status;
    $fraud = $notification->fraud_status;

    error_log("Processing notification for order $orderId with status $status");

    $payment = Payment::getInstance();
    
    if ($status == 'capture') {
        if ($fraud == 'challenge') {
            $payment->updateOrderStatus($orderId, 'challenge');
        } else if ($fraud == 'accept') {
            $payment->updateOrderStatus($orderId, 'success');
            $userId = $payment->getUserIdByOrderId($orderId);
            if ($userId) {
                require_once __DIR__ . '/../includes/Cart.php';
                $cart = new Cart();
                $cart->clearCart($userId);
            }
        }
    } else if ($status == 'settlement') {
        $payment->updateOrderStatus($orderId, 'success');
    } else if ($status == 'cancel' || $status == 'deny' || $status == 'expire') {
        $payment->updateOrderStatus($orderId, 'failure');
    } else if ($status == 'pending') {
        $payment->updateOrderStatus($orderId, 'pending');
    }

    error_log("Notification processed successfully");
    http_response_code(200);
} catch (Exception $e) {
    error_log('Notification error: ' . $e->getMessage());
    http_response_code(500);
}
