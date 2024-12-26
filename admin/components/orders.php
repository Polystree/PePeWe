<?php
$order = new Order();
$allOrders = $order->getAllOrders();
?>

<div id="orders" class="tab-content">
    <table class="cart-table">
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Shipping Address</th>
                <th>Total Amount</th>
                <th>Items</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($allOrders)): ?>
                <?php foreach ($allOrders as $orderItem): ?>
                    <tr>
                        <td><?= htmlspecialchars($orderItem['order_number']) ?></td>
                        <td>
                            <?= htmlspecialchars($orderItem['username']) ?><br>
                            <small><?= htmlspecialchars($orderItem['email']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($orderItem['shipping_address']) ?></td>
                        <td>
                            Rp <?= number_format($orderItem['total_amount'], 0, ',', '.') ?>
                            <?php if($orderItem['discount_amount'] > 0): ?>
                                <br><small class="text-success">-Rp <?= number_format($orderItem['discount_amount'], 0, ',', '.') ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="order-items-preview">
                                <?php foreach ($orderItem['items'] as $item): ?>
                                    <div class="order-item-mini" title="<?= $item['name'] ?>">
                                        <img src="/<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" />
                                        <span>×<?= $item['quantity'] ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td><?= date('d M Y H:i', strtotime($orderItem['created_at'])) ?></td>
                        <td>
                            <button onclick="viewOrderDetails('<?= $orderItem['order_number'] ?>')" 
                                    class="edit-button">View Details</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">No orders found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
