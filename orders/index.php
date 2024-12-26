<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Cart.php';
require_once __DIR__ . '/../includes/Order.php';

if (!isset($_SESSION['userId'])) {
    header('Location: /login');
    exit();
}

$order = new Order();

// Handle specific order view
if (isset($_GET['order_id'])) {
    $orderId = $_GET['order_id'];
    $orderDetails = $order->getOrderDetails($orderId);
    
    // Clear cart on successful order
    if ($orderDetails) {
        $cart = new Cart();
        $cart->clearCart($_SESSION['userId']);
    }
}

// Get order history
$orderHistory = $order->getUserOrders($_SESSION['userId']);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/orders.css">
</head>
<body>
    <?php include '../templates/header.php'; ?>
    
    <main class="container">
        <?php if (isset($_GET['order_id']) && $orderDetails): ?>
            <div class="order-success-message">
                <div class="success-icon">✓</div>
                <h1>Order Details</h1>
                
                <div class="order-details">
                    <div class="order-header-info">
                        <h2>Order Information</h2>
                        <div class="info-group">
                            <label>Order Number:</label>
                            <span><?= htmlspecialchars($orderId) ?></span>
                        </div>
                        <div class="info-group">
                            <label>Order Date:</label>
                            <span><?= date('d M Y H:i', strtotime($orderDetails['created_at'])) ?></span>
                        </div>
                        <div class="info-group">
                            <label>Status:</label>
                            <span class="status success">Completed</span>
                        </div>
                    </div>

                    <div class="order-customer-info">
                        <h2>Customer Information</h2>
                        <div class="info-group">
                            <label>Name:</label>
                            <span><?= htmlspecialchars($orderDetails['customer']['username']) ?></span>
                        </div>
                        <div class="info-group">
                            <label>Email:</label>
                            <span><?= htmlspecialchars($orderDetails['customer']['email']) ?></span>
                        </div>
                        <div class="info-group address">
                            <label>Shipping To:</label>
                            <span><?= htmlspecialchars($orderDetails['shipping_address']) ?></span>
                        </div>
                    </div>

                    <div class="order-items-details">
                        <h2>Order Items</h2>
                        <?php foreach ($orderDetails['items'] as $item): ?>
                            <div class="order-detail-item">
                                <div class="item-image">
                                    <img src="/<?= htmlspecialchars($item['image_path']) ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>" />
                                </div>
                                <div class="item-info">
                                    <h3><?= htmlspecialchars($item['name']) ?></h3>
                                    <div class="item-meta">
                                        <span class="item-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                                        <span class="item-quantity">Qty: <?= $item['quantity'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-summary">
                        <h2>Order Summary</h2>
                        <div class="info-group">
                            <label>Subtotal:</label>
                            <span>Rp <?= number_format($orderDetails['total_amount'] - $orderDetails['shipping_cost'] + $orderDetails['discount_amount'], 0, ',', '.') ?></span>
                        </div>
                        <?php if ($orderDetails['shipping_cost'] > 0): ?>
                        <div class="info-group">
                            <label>Shipping:</label>
                            <span>Rp <?= number_format($orderDetails['shipping_cost'], 0, ',', '.') ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($orderDetails['discount_amount'] > 0): ?>
                        <div class="info-group">
                            <label>Discount:</label>
                            <span>-Rp <?= number_format($orderDetails['discount_amount'], 0, ',', '.') ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="info-group total">
                            <label>Total Amount:</label>
                            <span class="total-amount">Rp <?= number_format($orderDetails['total_amount'], 0, ',', '.') ?></span>
                        </div>
                    </div>
                
                <div class="action-buttons">
                    <a href="/orders" class="order-btn order-btn-secondary">Back to Orders</a>
                    <a href="/" class="order-btn order-btn-primary">Continue Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <div class="orders-history">
                <h1>My Orders</h1>
                
                <?php if (!empty($orderHistory)): ?>
                    <div class="orders-grid">
                        <?php foreach ($orderHistory as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <h3>Order #<?= htmlspecialchars($order['order_number']) ?></h3>
                                    <span class="status success">Completed</span>
                                </div>
                                <div class="order-body">
                                    <div class="info-group">
                                        <label>Date:</label>
                                        <span><?= date('d M Y', strtotime($order['created_at'])) ?></span>
                                    </div>
                                    <div class="info-group">
                                        <label>Total Amount:</label>
                                        <span>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></span>
                                    </div>
                                </div>
                                <div class="order-items">
                                    <?php foreach ($order['items'] as $item): ?>
                                        <div class="order-item">
                                            <img src="/<?= htmlspecialchars($item['image_path']) ?>" 
                                                 alt="<?= htmlspecialchars($item['name']) ?>" />
                                            <div class="order-item-details">
                                                <div class="order-item-name"><?= htmlspecialchars($item['name']) ?></div>
                                                <div class="order-item-meta">
                                                    <span class="order-item-price">Rp <?= number_format($item['price'], 0, ',', '.') ?></span>
                                                    <span class="order-item-quantity">x<?= $item['quantity'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="/orders?order_id=<?= $order['order_number'] ?>" class="order-btn order-btn-outline">View Details</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-orders">
                        <p>You haven't placed any orders yet.</p>
                        <a href="/" class="order-btn order-btn-primary">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include '../templates/footer.php'; ?>
</body>
</html>
