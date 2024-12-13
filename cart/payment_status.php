
<?php
require_once __DIR__ . '/../includes/Payment.php';
session_start();

if (!isset($_GET['order_id'])) {
    header('Location: /');
    exit();
}

$payment = Payment::getInstance();
$order = $payment->getOrderStatus($_GET['order_id']);

if (!$order) {
    header('Location: /');
    exit();
}

// Define status configurations
$statusConfig = [
    'pending' => [
        'icon' => '⏳',
        'title' => 'Payment Pending',
        'message' => 'Please complete your payment',
        'color' => '#ffc107',
        'containerClass' => 'pending-container'
    ],
    'settlement' => [
        'icon' => '🎉',
        'title' => 'Payment Successful',
        'message' => 'Thank you for your purchase',
        'color' => '#28a745',
        'containerClass' => 'success-container'
    ],
    'capture' => [
        'icon' => '🎉',
        'title' => 'Payment Successful',
        'message' => 'Thank you for your purchase',
        'color' => '#28a745',
        'containerClass' => 'success-container'
    ],
    'deny' => [
        'icon' => '❌',
        'title' => 'Payment Failed',
        'message' => 'There was a problem with your payment',
        'color' => '#dc3545',
        'containerClass' => 'failed-container'
    ],
    'cancel' => [
        'icon' => '❌',
        'title' => 'Payment Failed',
        'message' => 'There was a problem with your payment',
        'color' => '#dc3545',
        'containerClass' => 'failed-container'
    ],
    'expire' => [
        'icon' => '❌',
        'title' => 'Payment Failed',
        'message' => 'Payment has expired',
        'color' => '#dc3545',
        'containerClass' => 'failed-container'
    ]
];

$status = $order['status'];
$config = $statusConfig[$status] ?? $statusConfig['pending'];
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $config['title'] ?> - PePeWe</title>
    <link rel='stylesheet' href='/assets/css/style.css' />
    <style>
        .status-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .status-header {
            text-align: center;
            color: <?= $config['color'] ?>;
            margin-bottom: 30px;
        }
        .order-details {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .order-details p {
            margin: 10px 0;
        }
        .continue-shopping {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            margin: 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="status-container <?= $config['containerClass'] ?>">
        <div class="status-header">
            <h1><?= $config['icon'] ?> <?= $config['title'] ?></h1>
            <p><?= $config['message'] ?></p>
        </div>
        
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order ID:</strong> <?= htmlspecialchars($order['order_id']) ?></p>
            <p><strong>Amount:</strong> Rp <?= number_format($order['amount'], 0, ',', '.') ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($status) ?></p>
            <p><strong>Customer:</strong> <?= htmlspecialchars($order['username']) ?></p>
            <?php if (in_array($status, ['settlement', 'capture'])): ?>
                <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
                <p><strong>Payment Type:</strong> <?= $order['payment_type'] ? htmlspecialchars($order['payment_type']) : 'Not specified' ?></p>
            <?php endif; ?>
        </div>

        <div class="continue-shopping">
            <?php if ($status === 'pending'): ?>
                <p>Please check your email for payment instructions.</p>
                <p id="status-message">Checking payment status...</p>
                <script>
                function checkPaymentStatus() {
                    fetch('../cart/check_payment_status.php?order_id=<?= htmlspecialchars($order['order_id']) ?>')
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else if (data.status === 'pending') {
                                setTimeout(checkPaymentStatus, 3000);
                            }
                        });
                }
                checkPaymentStatus();
                </script>
            <?php elseif (in_array($status, ['settlement', 'capture'])): ?>
                <p>Your order confirmation has been sent to your email.</p>
            <?php else: ?>
                <p>Please try again or contact customer support if the problem persists.</p>
                <a href="/cart" class="btn">Try Again</a>
            <?php endif; ?>
            <a href="/" class="btn">Return to Home</a>
        </div>
    </div>
</body>
</html>