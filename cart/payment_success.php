<?php
session_start();
require_once __DIR__ . '/../includes/Payment.php';

if (!isset($_GET['order_id'])) {
    header('Location: /cart');
    exit();
}

$orderId = $_GET['order_id'];
$payment = Payment::getInstance();
$orderDetails = $payment->getOrderDetails($orderId);

if (!$orderDetails || $orderDetails['user_id'] !== $_SESSION['userId']) {
    header('Location: /cart');
    exit();
}

$title = 'Payment Success - iniGadget';
include __DIR__ . '/../templates/header.php';
?>
<link rel="stylesheet" href="/assets/css/payment.css">
<div class="container payment-result">
    <div class="success-box">
        <div class="icon">✓</div>
        <h1>Payment Successful!</h1>
        <p>Thank you for your purchase. Your order has been confirmed.</p>
        
        <div class="order-details">
            <h2>Order Details</h2>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($orderId); ?></p>
            <p><strong>Amount:</strong> Rp <?php echo number_format($orderDetails['amount'], 0, ',', '.'); ?></p>
            <p><strong>Status:</strong> <span class="status-<?php echo strtolower($orderDetails['status']); ?>"><?php echo ucfirst($orderDetails['status']); ?></span></p>
        </div>

        <div class="next-steps">
            <h3>What's Next?</h3>
            <p>We'll send you an email confirmation with your order details and tracking information once your items have been shipped.</p>
        </div>

        <div class="actions">
            <a href="/account/orders" class="btn">View Order</a>
            <a href="/products" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../templates/footer.php'; ?>
