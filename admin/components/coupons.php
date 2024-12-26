<?php
$stmt = $connect->prepare("SELECT * FROM coupons ORDER BY expiry_date DESC");
$stmt->execute();
$coupons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div id="coupons" class="tab-content">
    <button onclick="showAddCouponModal()" class="add-product-btn">Add Coupon</button>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Discount (%)</th>
                <th>Expiry Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coupons as $coupon): ?>
                <tr>
                    <td><span class="coupon-code"><?= htmlspecialchars($coupon['code']) ?></span></td>
                    <td><?= $coupon['discount'] ?>%</td>
                    <td><?= date('Y-m-d', strtotime($coupon['expiry_date'])) ?></td>
                    <td><?= new DateTime() > new DateTime($coupon['expiry_date']) ? '<span class="status-badge expired">Expired</span>' : '<span class="status-badge active">Active</span>' ?></td>
                    <td class="manage-product-btn">
                        <button onclick="editCoupon(<?= htmlspecialchars(json_encode($coupon)) ?>)" class="edit-button">Edit</button>
                        <button onclick="deleteCoupon(<?= $coupon['id'] ?>)" class="delete-button">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
