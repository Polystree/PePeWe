<?php
session_start();
$config = include __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/Order.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header('Location: /login');
    exit();
}

$db_config = $config['db'];
$connect = new mysqli(
    $db_config['host'],
    $db_config['username'], 
    $db_config['password'],
    $db_config['database']
);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>Admin Dashboard - iniGadget</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/cart.css" />
    <link rel="stylesheet" href="../assets/css/admin.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" />
</head>
<body>
    <?php include '../templates/header.php'; ?>
    <div class="main-content">
        <h2>Admin Dashboard</h2>
        <div class="admin-tabs">
            <button class="tab-button active" data-tab="products">Products</button>
            <button class="tab-button" data-tab="orders">Orders</button>
            <button class="tab-button" data-tab="discounts">Discounts</button>
            <button class="tab-button" data-tab="coupons">Coupons</button>
        </div>

        <?php 
        include 'components/products.php';
        include 'components/orders.php';
        include 'components/discounts.php';
        include 'components/coupons.php';
        
        // Include modals
        include 'components/modals/discount_modal.php';
        include 'components/modals/coupon_modal.php';
        include 'components/modals/order_details_modal.php';
        ?>
    </div>
    <?php include '../templates/footer.php'; ?>
    
    <script src="js/admin.js"></script>
    <script src="js/products.js"></script>
    <script src="js/orders.js"></script>
    <script src="js/discounts.js"></script>
    <script src="js/coupons.js"></script>
</body>
</html>
