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
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Pending - PePeWe</title>
    <link rel='stylesheet' href='/assets/css/style.css' />
    <style>
        .pending-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .pending-header {
            text-align: center;
            color: #ffc107;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="pending-container">
        <div class="pending-header">
            <h1>⏳ Payment Pending</h1>
            <p>Please complete your payment</p>
        </div>
        
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
            <p><strong>Amount:</strong> Rp <?php echo number_format($order['amount'], 0, ',', '.'); ?></p>
            <p><strong>Status:</strong> Pending</p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
        </div>

        <div class="continue-shopping">
            <p>Please check your email for payment instructions.</p>
            <p id="status-message">Checking payment status...</p>
            <a href="/" class="btn">Return to Home</a>
        </div>
    </div>

    <script>
    function checkPaymentStatus() {
        fetch('check_payment_status.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'payment_success.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>';
                } else if (data.status === 'pending') {
                    setTimeout(checkPaymentStatus, 3000);
                } else if (data.status === 'deny' || data.status === 'cancel' || data.status === 'expire') {
                    window.location.href = 'payment_failed.php?order_id=<?php echo htmlspecialchars($order['order_id']); ?>';
                }
            });
    }

    checkPaymentStatus();
    </script>
</body>
</html>