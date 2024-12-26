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
                                
                                try {
                                    isPaymentInProgress = true;
                                    e.target.disabled = true;
                                    e.target.textContent = 'Processing...';
                                    
                                    const finalTotal = document.getElementById('finalTotal').textContent
                                        .replace('Rp ', '').replace(/\./g, '');
                                    
                                    const response = await fetch('/cart/payment_handler.php', {
                                        method: 'POST',
                                        headers: { 
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json'
                                        },
                                        body: JSON.stringify({ 
                                            amount: parseInt(finalTotal)
                                        })
                                    });
                                    
                                    if (!response.ok) {
                                        const errorData = await response.json().catch(() => null);
                                        throw new Error(errorData?.error || `Server error: ${response.status}`);
                                    }
                                    
                                    const data = await response.json().catch(() => {
                                        throw new Error('Invalid response from server');
                                    });
                                    
                                    if (!data.success || !data.token) {
                                        throw new Error(data.error || 'Failed to get payment token');
                                    }
                                    
                                    window.snap.pay(data.token, {
                                        onSuccess: function(result) {
                                            window.location.href = '/orders?order_id=' + result.order_id;
                                        },
                                        onPending: function(result) {
                                            alert('Payment pending. Please complete your payment.');
                                            resetPaymentButton();
                                        },
                                        onError: function() {
                                            alert('Payment failed. Please try again.');
                                            resetPaymentButton();
                                        },
                                        onClose: function() {
                                            resetPaymentButton();
                                        }
                                    });
                                } catch (error) {
                                    console.error('Payment error:', error);
                                    alert('Error: ' + (error.message || 'Failed to initialize payment'));
                                    resetPaymentButton();
                                }
                            });

                            function resetPaymentButton() {
                                isPaymentInProgress = false;
                                const button = document.getElementById('pay-button');
                                button.disabled = false;
                                button.textContent = 'Pay Rp ' + document.getElementById('finalTotal').textContent.replace('Rp ', '');
                            }
                        </script>
                    <?php else: ?>
                        <p>Add items to your cart to proceed with payment</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>