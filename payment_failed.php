
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
    <title>Payment Failed - PePeWe</title>
    <link rel='stylesheet' href='/assets/css/style.css' />
    <style>
        .failed-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .failed-header {
            text-align: center;
            color: #dc3545;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="failed-container">
        <div class="failed-header">
            <h1>❌ Payment Failed</h1>
            <p>There was a problem with your payment</p>
        </div>
        
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
            <p><strong>Amount:</strong> Rp <?php echo number_format($order['amount'], 0, ',', '.'); ?></p>
            <p><strong>Status:</strong> Failed</p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
        </div>

        <div class="continue-shopping">
            <p>Please try again or contact customer support if the problem persists.</p>
            <a href="/" class="btn">Return to Home</a>
            <a href="/cart.php" class="btn">Try Again</a>
        </div>
    </div>
</body>
</html>