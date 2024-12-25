<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/Payment.php';

$config = require_once __DIR__ . '/../config/midtrans_config.php';
\Midtrans\Config::$serverKey = $config['server_key'];
\Midtrans\Config::$isProduction = $config['is_production'];
$notification = new \Midtrans\Notification();

$orderId = $notification->order_id;
$status = $notification->transaction_status;
$fraud = $notification->fraud_status;

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
