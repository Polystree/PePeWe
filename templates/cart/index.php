<?php 
$totalPrice = 0;
$config = include(__DIR__ . '/../../config/config.php');
$db_config = $config['db'];
$connect = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
?>

<link rel="stylesheet" type="text/css" href="/assets/css/style.css">
<link rel="stylesheet" type="text/css" href="/assets/css/cart.css">

<div class='container cart-container'>
    <?php include 'components/table.php'; ?>

    <div class="cart-actions">
        <div class="cart-forms">
            <?php include 'components/shipping-form.php'; ?>
        </div>
        
        <div class="cart-summary">
            <?php include 'components/coupon-form.php'; ?>
            
            <div class="cart-total">
                <h3>Cart Total</h3>
                <p><span>Subtotal:</span> <span>Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span></p>
                <p id="shippingRow"><span>Shipping:</span> <span>-</span></p>
                <p id="discountRow" style="display: none;"><span>Discount:</span> <span>-Rp <span id="discountAmount">0</span></span></p>
                <p><span>Total:</span> <span id="finalTotal">Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span></p>
                
                <input type="hidden" id="subtotalAmount" value="<?php echo $totalPrice; ?>">
                <input type="hidden" id="currentDiscount" value="0">
                
                <div class="payment-methods">
                    <?php if ($totalPrice > 0): ?>
                        <button id="pay-button" class="midtrans-button" disabled>Pay Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></button>
                        <small class="help-text" id="paymentHelp">Please select a shipping method to continue</small>
                        <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?php echo $midtransConfig['client_key']; ?>"></script>
                    <?php else: ?>
                        <p>Add items to your cart to proceed with payment</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>