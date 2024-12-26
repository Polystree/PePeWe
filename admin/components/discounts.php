<?php
$stmt = $connect->prepare("SELECT d.*, p.name as product_name, p.image_path 
    FROM discounts d 
    JOIN products p ON d.product_id = p.productId
    ORDER BY d.start_date DESC");
$stmt->execute();
$discounts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div id="discounts" class="tab-content">
    <button onclick="showAddDiscountModal()" class="add-product-btn">Add Discount</button>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Name</th>
                <th>Discount %</th>
                <th>Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($discounts as $discount): ?>
                <tr>
                    <td><img src="/<?= htmlspecialchars($discount['image_path']) ?>" alt="<?= htmlspecialchars($discount['product_name']) ?>" /></td>
                    <td><?= htmlspecialchars($discount['product_name']) ?></td>
                    <td><?= $discount['discount_percent'] ?>%</td>
                    <td><?= $discount['is_flash_sale'] ? 'Flash Sale' : 'Regular Discount' ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($discount['start_date'])) ?></td>
                    <td><?= date('Y-m-d H:i', strtotime($discount['end_date'])) ?></td>
                    <td>
                        <?php 
                        $now = new DateTime();
                        $start = new DateTime($discount['start_date']);
                        $end = new DateTime($discount['end_date']);
                        
                        if ($now < $start) {
                            echo '<span class="status-badge scheduled">Scheduled</span>';
                        } elseif ($now > $end) {
                            echo '<span class="status-badge expired">Expired</span>';
                        } else {
                            echo '<span class="status-badge active">Active</span>';
                        }
                        ?>
                    </td>
                    <td class="manage-product-btn">
                        <button onclick="editDiscount(<?= htmlspecialchars(json_encode($discount)) ?>)" 
                                class="edit-button">Edit</button>
                        <button onclick="deleteDiscount(<?= $discount['id'] ?>)" 
                                class="delete-button">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
