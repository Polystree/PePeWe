<?php 
$paypalConfig = $GLOBALS['paypalConfig'] ?? null;
$totalPrice = 0;
?>
<link rel="stylesheet" type="text/css" href="/assets/css/style.css">
<link rel="stylesheet" type="text/css" href="/assets/css/cart.css">

<div class='container cart-container'>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="/<?php echo htmlspecialchars($item['image_path'] ?? ''); ?>"
                                    alt="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>" />
                                <div>
                                    <p><?php echo htmlspecialchars($item['product_name'] ?? ''); ?></p>
                                </div>
                            </div>
                        </td>
                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                        <td>
                            <input type="number" 
                                class="quantity-input"
                                data-price="<?php echo $item['price']; ?>"
                                data-product="<?php echo htmlspecialchars($item['productId'] ?? ''); ?>" 
                                value="<?php echo $item['quantity']; ?>" 
                                min="0" />
                        </td>
                        <td class="subtotal">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php $totalPrice += $item['price'] * $item['quantity']; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Your cart is empty.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="cart-actions">
        <div class="cart-forms">
            <div class="shipping-box">
                <h3>Shipping Details</h3>
                <div class="form-group">
                    <label for="savedAddress">Shipping Address</label>
                    <select id="savedAddress" class="address-select" required>
                        <option value="">Select a saved address</option>
                        <option value="new">+ Add New Address</option>
                    </select>
                    <small class="help-text">Select a saved address or add a new one</small>
                </div>
                
                <div class="address-details" style="display: none;">
                    <div class="form-group">
                        <label for="recipientName">Recipient Name</label>
                        <input type="text" id="recipientName" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="phoneNumber">Phone Number</label>
                        <input type="tel" id="phoneNumber" required />
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Full Address</label>
                        <textarea id="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" required />
                    </div>

                    <div class="form-group">
                        <label for="postalCode">Postal Code</label>
                        <input type="text" id="postalCode" required />
                    </div>
                </div>

                <div class="form-group">
                    <label for="shipping">Shipping Method</label>
                    <select id="shipping" class="shipping-select">
                        <option value="">Select shipping method</option>
                        <optgroup label="JNE">
                            <option value="jne_reg" data-price="12000">JNE Reguler (2-3 days) - Rp 12.000</option>
                            <option value="jne_yes" data-price="24000">JNE YES (1 day) - Rp 24.000</option>
                        </optgroup>
                        <optgroup label="J&T Express">
                            <option value="jnt_reg" data-price="11000">J&T Reguler (2-3 days) - Rp 11.000</option>
                            <option value="jnt_exp" data-price="22000">J&T Express (1 day) - Rp 22.000</option>
                        </optgroup>
                        <optgroup label="SiCepat">
                            <option value="sicepat_reg" data-price="11000">SiCepat Reguler (2-3 days) - Rp 11.000</option>
                            <option value="sicepat_best" data-price="23000">SiCepat BEST (1 day) - Rp 23.000</option>
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="cart-summary">
            <div class="coupon-box">
                <h3>Apply Coupon</h3>
                <div class="coupon">
                    <input type="text" id="couponInput" placeholder="Enter your coupon code" />
                    <button type="button" id="applyCoupon">Apply</button>
                </div>
                <span id="couponMessage"></span>
            </div>
            
            <div class="cart-total">
                <h3>Cart Total</h3>
                <p><span>Subtotal:</span> <span>Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span></p>
                <p id="shippingRow"><span>Shipping:</span> <span>-</span></p>
                <p id="discountRow" style="display: none;"><span>Discount:</span> <span>-Rp 0</span></p>
                <p><span>Total:</span> <span id="finalTotal">Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span></p>
                
                <div class="payment-methods" style="margin-top: 20px; min-height: 150px;">
                    <?php if ($totalPrice > 0): ?>
                        <button id="pay-button" class="midtrans-button">Pay Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></button>
                        <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?php echo $midtransConfig['client_key']; ?>"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const payBtn = document.getElementById('pay-button');
                                let isPaymentInProgress = false;

                                function validateForm() {
                                    const shippingSelect = document.getElementById('shipping');
                                    const recipientName = document.getElementById('recipientName');
                                    const address = document.getElementById('address');
                                    const phoneNumber = document.getElementById('phoneNumber');
                                    const city = document.getElementById('city');
                                    const postalCode = document.getElementById('postalCode');

                                    if (!shippingSelect.value) throw new Error('Please select a shipping method');
                                    if (!recipientName.value) throw new Error('Please enter recipient name');
                                    if (!address.value) throw new Error('Please enter shipping address');
                                    if (!phoneNumber.value) throw new Error('Please enter phone number');
                                    if (!city.value) throw new Error('Please enter city');
                                    if (!postalCode.value) throw new Error('Please enter postal code');

                                    return {
                                        shipping_method: shippingSelect.value,
                                        shipping_cost: parseInt(shippingSelect.options[shippingSelect.selectedIndex].dataset.price) || 0,
                                        recipient_name: recipientName.value,
                                        address: address.value,
                                        phone: phoneNumber.value,
                                        city: city.value,
                                        postal_code: postalCode.value
                                    };
                                }

                                async function processPayment() {
                                    if (isPaymentInProgress) return;
                                    
                                    try {
                                        isPaymentInProgress = true;
                                        payBtn.disabled = true;
                                        payBtn.textContent = 'Processing...';

                                        const formData = validateForm();
                                        const finalAmountText = document.getElementById('finalTotal').textContent;
                                        const finalAmount = parseInt(finalAmountText.replace(/[^0-9]/g, ''));

                                        if (elements.savedAddress.value === 'new') {
                                            // Save new address before processing payment
                                            const saved = await saveNewAddress(formData);
                                            if (!saved) return;
                                        }

                                        const response = await fetch('/cart/create_payment.php', {
                                            method: 'POST',
                                            headers: { 
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                amount: finalAmount,
                                                shipping_method: formData.shipping_method,
                                                shipping_cost: formData.shipping_cost,
                                                recipient_name: formData.recipient_name,
                                                address: formData.address,
                                                phone: formData.phone,
                                                city: formData.city,
                                                postal_code: formData.postal_code
                                            })
                                        });

                                        const data = await response.json();
                                        if (data.error) throw new Error(data.error);

                                        window.snap.pay(data.token, {
                                            onSuccess: function(result) {
                                                window.location.href = '/cart/payment_success.php';
                                            },
                                            onPending: function(result) {
                                                window.location.href = '/cart/payment_pending.php';
                                            },
                                            onError: function(result) {
                                                window.location.href = '/cart/payment_error.php';
                                            },
                                            onClose: function() {
                                                isPaymentInProgress = false;
                                                payBtn.disabled = false;
                                                payBtn.textContent = 'Pay Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?>';
                                            }
                                        });
                                    } catch (error) {
                                        console.error('Payment error:', error);
                                        alert(error.message || 'Payment failed. Please try again.');
                                        isPaymentInProgress = false;
                                        payBtn.disabled = false;
                                        payBtn.textContent = 'Pay Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?>';
                                    }
                                }

                                if (payBtn) {
                                    payBtn.addEventListener('click', processPayment);
                                }
                            });
                        </script>
                    <?php else: ?>
                        <p>Add items to your cart to proceed with payment</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const elements = {
            quantityInputs: document.querySelectorAll('.quantity-input'),
            cartTotal: document.querySelector('.cart-total'),
            couponInput: document.getElementById('couponInput'),
            applyCouponBtn: document.getElementById('applyCoupon'),
            couponMessage: document.getElementById('couponMessage'),
            discountRow: document.getElementById('discountRow'),
            finalTotal: document.getElementById('finalTotal'),
            subtotalElement: document.querySelector('.cart-total p:first-child span:last-child'),
            shippingSelect: document.getElementById('shipping'),
            shippingRow: document.getElementById('shippingRow'),
            address: document.getElementById('address'),
            savedAddress: document.getElementById('savedAddress'),
            recipientName: document.getElementById('recipientName'),
            phoneNumber: document.getElementById('phoneNumber'),
            city: document.getElementById('city'),
            postalCode: document.getElementById('postalCode')
        };

        let currentDiscount = 0;
        let currentShippingCost = 0;

        function getSubtotal() {
            let total = 0;
            const cells = document.querySelectorAll('.subtotal');
            cells.forEach(cell => {
                if (cell && cell.textContent) {
                    const value = parseInt(cell.textContent.replace(/[^0-9]/g, '')) || 0;
                    total += value;
                }
            });
            return total;
        }

        function updateCartTotal() {
            const total = getSubtotal();
            const discountAmount = Math.round(total * (currentDiscount / 100));
            const finalAmount = total + currentShippingCost - discountAmount;

            if (elements.subtotalElement) {
                elements.subtotalElement.textContent = 'Rp ' + numberFormat(total);
            }
            
            if (elements.shippingRow) {
                elements.shippingRow.querySelector('span:last-child').textContent = 
                    currentShippingCost ? 'Rp ' + numberFormat(currentShippingCost) : '-';
            }
            
            if (elements.discountRow && currentDiscount > 0) {
                elements.discountRow.style.display = 'flex';
                const discountSpan = elements.discountRow.querySelector('span:last-child');
                if (discountSpan) {
                    discountSpan.textContent = `-Rp ${numberFormat(discountAmount)}`;
                }
            }
            
            if (elements.finalTotal) {
                elements.finalTotal.textContent = 'Rp ' + numberFormat(finalAmount);
                
                const payBtn = document.getElementById('pay-button');
                if (payBtn) {
                    payBtn.textContent = 'Pay Rp ' + numberFormat(finalAmount);
                }
            }

            return { total, finalAmount };
        }
        if (elements.quantityInputs) {
            elements.quantityInputs.forEach(input => {
                input.addEventListener('change', debounce(function(e) {
                    const productId = this.dataset.product;
                    const quantity = this.value;
                    const price = parseFloat(this.dataset.price);
                    const subtotalCell = this.closest('tr').querySelector('.subtotal');
                    
                    fetch('/cart/update_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            quantity: quantity
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const subtotal = price * quantity;
                            subtotalCell.textContent = 'Rp ' + numberFormat(subtotal);
                            
                            updateCartTotal();
                            if (quantity <= 0) {
                                this.closest('tr').remove();
                            }
                        } else {
                            alert('Error updating cart');
                        }
                    });
                }, 500));
            });
        }

        if (elements.applyCouponBtn && elements.couponInput && elements.couponMessage) {
            elements.applyCouponBtn.addEventListener('click', async function() {
                const code = elements.couponInput.value.trim();
                if (!code) {
                    elements.couponMessage.textContent = 'Please enter a coupon code';
                    elements.couponMessage.style.color = 'red';
                    return;
                }

                try {
                    console.log('Sending coupon request:', code);
                    const response = await fetch('/cart/validate_coupon.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ code: code })
                    });

                    const data = await response.json();
                    console.log('Coupon response:', data);
                    
                    if (data.success) {
                        currentDiscount = data.discount;
                        const { finalAmount } = updateCartTotal();
                        
                        if (elements.couponMessage) {
                            elements.couponMessage.textContent = `Coupon applied! ${currentDiscount}% discount`;
                            elements.couponMessage.style.color = 'green';
                        }

                        if (typeof paypal !== 'undefined') {
                            const paypalAmount = (finalAmount / <?php echo $paypalConfig['exchange_rate']; ?>).toFixed(2);
                            console.log('Updating PayPal amount:', paypalAmount);
                        }
                    } else {
                        throw new Error(data.error || 'Failed to apply coupon');
                    }
                } catch (error) {
                    console.error('Coupon error:', error);
                    if (elements.couponMessage) {
                        elements.couponMessage.textContent = error.message || 'Error applying coupon';
                        elements.couponMessage.style.color = 'red';
                    }
                    if (elements.discountRow) {
                        elements.discountRow.style.display = 'none';
                    }
                    currentDiscount = 0;
                    updateCartTotal();
                }
            });
        }

        if (elements.shippingSelect) {
            elements.shippingSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                currentShippingCost = parseInt(selected.dataset.price) || 0;
                updateCartTotal();
            });
        }

        function numberFormat(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        async function loadSavedAddresses() {
            try {
                const response = await fetch('/cart/fetch_addresses.php');
                const data = await response.json();
                
                const select = elements.savedAddress;
                select.innerHTML = `
                    <option value="">Select a saved address</option>
                    <option value="new">+ Add New Address</option>
                `;
                
                if (data.success && data.addresses.length > 0) {
                    data.addresses.forEach(addr => {
                        const option = document.createElement('option');
                        option.value = addr.id;
                        option.textContent = `${addr.address_label} - ${addr.recipient_name} (${addr.city})`;
                        option.dataset.address = JSON.stringify({
                            recipient_name: addr.recipient_name,
                            phone: addr.phone,
                            address: addr.address,
                            city: addr.city,
                            postal_code: addr.postal_code
                        });
                        if (addr.is_default) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });

                    const defaultOption = select.querySelector('option[selected]');
                    if (defaultOption) {
                        const addressData = JSON.parse(defaultOption.dataset.address);
                        elements.recipientName.value = addressData.recipient_name;
                        elements.phoneNumber.value = addressData.phone;
                        elements.address.value = addressData.address;
                        elements.city.value = addressData.city;
                        elements.postalCode.value = addressData.postal_code;
                    }
                } else {
                    select.value = 'new';
                    document.querySelector('.address-details').style.display = 'block';
                }
            } catch (error) {
                console.error('Error loading addresses:', error);
                document.querySelector('.address-details').style.display = 'block';
            }
        }

        if (elements.savedAddress) {
            elements.savedAddress.addEventListener('change', function() {
                const addressDetails = document.querySelector('.address-details');
                if (this.value === '+ Add New Address') {
                    addressDetails.style.display = 'block';
                    elements.recipientName.value = '';
                    elements.phoneNumber.value = '';
                    elements.address.value = '';
                    elements.city.value = '';
                    elements.postalCode.value = '';
                } else if (this.value) {
                    addressDetails.style.display = 'none';
                    const option = this.options[this.selectedIndex];
                    const addressData = JSON.parse(option.dataset.address);
                    elements.recipientName.value = addressData.recipient_name;
                    elements.phoneNumber.value = addressData.phone;
                    elements.address.value = addressData.address;
                    elements.city.value = addressData.city;
                    elements.postalCode.value = addressData.postal_code;
                }
            });
        }

        async function saveNewAddress(formData) {
            try {
                const response = await fetch('/cart/save_address.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                if (data.success) {
                    const option = document.createElement('option');
                    option.value = data.address_id;
                    option.textContent = data.address_label;
                    option.dataset.address = JSON.stringify(formData);
                    option.selected = true;
                    elements.savedAddress.insertBefore(option, elements.savedAddress.children[1]);
                    
                    document.querySelector('.address-details').style.display = 'none';
                    
                    return true;
                } else {
                    throw new Error(data.error);
                }
            } catch (error) {
                console.error('Error saving address:', error);
                alert('Failed to save address: ' + error.message);
                return false;
            }
        }

        loadSavedAddresses();
    });
</script>