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
