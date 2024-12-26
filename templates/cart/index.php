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
                        <script type="text/javascript" src="http://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-XvNfPDRV6aHEMfWG"></script>
                        <script>
                            let isPaymentInProgress = false;
                            document.getElementById('pay-button').addEventListener('click', async function(e) {
                                if (isPaymentInProgress) return;
                                isPaymentInProgress = true;
                                e.target.disabled = true;
                                e.target.textContent = 'Processing...';
                                const finalTotal = document.getElementById('finalTotal').textContent.replace('Rp ', '').replace(/\./g, '');
                                const selectedShipping = document.getElementById('shipping').selectedOptions[0];
                                const shippingCost = selectedShipping ? parseInt(selectedShipping.dataset.price) : 0;
                                const shippingService = selectedShipping ? selectedShipping.dataset.service : '';
                                const discountAmount = parseInt(document.getElementById('discountAmount').textContent.replace(/\./g, '')) || 0;
                                const response = await fetch('/cart/payment_handler.php', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                                    body: JSON.stringify({ amount: parseInt(finalTotal), shipping_cost: shippingCost, shipping_service: shippingService, discount_amount: discountAmount })
                                });
                                const data = await response.json();
                                window.snap.pay(data.token, {
                                    onSuccess: function(result) { window.location.href = '/orders?order_id=' + result.order_id; },
                                    onPending: function() { alert('Payment pending. Please complete your payment.'); resetPaymentButton(); },
                                    onError: function() { alert('Payment failed. Please try again.'); resetPaymentButton(); },
                                    onClose: function() { resetPaymentButton(); }
                                });
                            });

                            function resetPaymentButton() {
                                isPaymentInProgress = false;
                                const button = document.getElementById('pay-button');
                                button.disabled = false;
                                button.textContent = 'Pay Rp ' + document.getElementById('finalTotal').textContent.replace('Rp ', '');
                            }
                        </script>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>