<?php
require_once 'login/database.php';
session_start();

if (!isset($_GET['order_id'])) {
    header('Location: /');
    exit();
}

$orderId = $_GET['order_id'];
$stmt = $connect->prepare("SELECT o.*, u.username, u.email 
                          FROM orders o 
                          JOIN users u ON o.user_id = u.id 
                          WHERE o.order_id = ?");
$stmt->bind_param("s", $orderId);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: /');
    exit();
}

if ($order['status'] === 'pending') {
    header('Location: /payment_pending.php?order_id=' . $orderId);
    exit();
} else if ($order['status'] !== 'settlement' && $order['status'] !== 'capture') {
    header('Location: /payment_failed.php?order_id=' . $orderId);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Success - PePeWe</title>
    <link rel='stylesheet' href='/assets/css/style.css' />
    <style>
        .success-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .success-header {
            text-align: center;
            color: #28a745;
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
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-header">
            <h1>🎉 Payment Successful!</h1>
            <p>Thank you for your purchase</p>
        </div>
        
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
            <p><strong>Amount:</strong> Rp <?php echo number_format($order['amount'], 0, ',', '.'); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
            <p><strong>Payment Type:</strong> <?php echo $order['payment_type'] ? htmlspecialchars($order['payment_type']) : 'Not specified'; ?></p>
        </div>

        <div class="continue-shopping">
            <p>Your order confirmation has been sent to your email.</p>
            <a href="/" class="btn">Continue Shopping</a>
        </div>
    </div>
</body>
</html>