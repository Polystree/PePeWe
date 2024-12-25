<?php
function getActiveCoupons($connect) {
    $currentDate = date('Y-m-d');
    $stmt = $connect->prepare(
        "SELECT id, code, discount FROM coupons 
         WHERE expiry_date >= ? 
         ORDER BY discount DESC"
    );
    $stmt->bind_param("s", $currentDate);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$activeCoupons = getActiveCoupons($connect);
?>

<div class="coupon-box">
    <h3>Apply Coupon</h3>
    <div class="coupon">
        <select id="couponSelect">
            <option value="">Select a coupon</option>
            <?php foreach ($activeCoupons as $coupon): ?>
                <option value="<?php echo htmlspecialchars($coupon['code']); ?>" data-discount="<?php echo $coupon['discount']; ?>">
                    <?php echo htmlspecialchars($coupon['code'] . ' - ' . $coupon['discount'] . '% off'); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="button" id="applyCoupon">Apply</button>
        <button type="button" id="removeCoupon" style="display: none;">Remove</button>
    </div>
    <span id="couponMessage"></span>
</div>
<script>
function updateTotal(discountPercent = 0) {
    const subtotal = parseFloat(document.getElementById('subtotalAmount').value);
    const shippingSelect = document.getElementById('shipping');
    const shippingCost = shippingSelect.value ? parseFloat(shippingSelect.selectedOptions[0].dataset.price) : 0;
    
    const discountAmount = Math.round(subtotal * discountPercent / 100);
    
    const finalTotal = Math.max(0, subtotal - discountAmount + shippingCost);
    
    document.getElementById('currentDiscount').value = discountPercent;
    document.getElementById('discountRow').style.display = discountPercent > 0 ? 'flex' : 'none';
    document.getElementById('discountAmount').textContent = discountAmount.toLocaleString('id-ID');
    document.getElementById('finalTotal').textContent = 'Rp ' + finalTotal.toLocaleString('id-ID');
    
    const payButton = document.getElementById('pay-button');
    if (payButton) {
        payButton.textContent = 'Pay Rp ' + finalTotal.toLocaleString('id-ID');
        payButton.disabled = !shippingSelect.value;
    }
}

document.getElementById('applyCoupon').addEventListener('click', async function() {
    const select = document.getElementById('couponSelect');
    const code = select.value;
    const message = document.getElementById('couponMessage');
    
    if (!code) {
        message.textContent = 'Please select a coupon';
        message.style.color = 'red';
        return;
    }

    try {
        const response = await fetch('/cart/validate_coupon.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ code })
        });

        const data = await response.json();
        
        if (data.success) {
            updateTotal(parseFloat(data.discount));
            document.getElementById('applyCoupon').style.display = 'none';
            document.getElementById('removeCoupon').style.display = 'inline-block';
            document.getElementById('couponSelect').disabled = true;
            message.textContent = data.message;
            message.style.color = 'green';
        } else {
            throw new Error(data.message || 'Invalid coupon');
        }
    } catch (error) {
        message.textContent = error.message;
        message.style.color = 'red';
        
        document.getElementById('currentDiscount').value = 0;
        document.getElementById('discountRow').style.display = 'none';
        document.getElementById('discountAmount').textContent = '0';
    }
});

document.getElementById('removeCoupon').addEventListener('click', function() {
    document.getElementById('couponSelect').value = '';
    document.getElementById('couponSelect').disabled = false;
    document.getElementById('applyCoupon').style.display = 'inline-block';
    document.getElementById('removeCoupon').style.display = 'none';
    document.getElementById('couponMessage').textContent = 'Coupon removed';
    document.getElementById('couponMessage').style.color = 'blue';
    updateTotal(0);
});

document.getElementById('shipping').addEventListener('change', function() {
    const currentDiscount = parseFloat(document.getElementById('currentDiscount').value);
    updateTotal(currentDiscount);
});
</script>