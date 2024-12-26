<?php
$stmt = $connect->prepare("SELECT productId, name, image_path, description, price, quantity, 
    category, sold_count, view_count, created_at, updated_at, is_featured, status 
    FROM products");
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div id="products" class="tab-content active">
    <button onclick="window.location.href='add-product.php'" class="add-product-btn">Add Product</button>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price (Rp)</th>
                <th>Quantity</th>
                <th>Category</th>
                <th>Stats</th>
                <th>Status</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" /></td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td class="description-cell"><?= htmlspecialchars($product['description']) ?></td>
                        <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                        <td><?= $product['quantity'] ?></td>
                        <td><?= htmlspecialchars($product['category']) ?></td>
                        <td>
                            <div>Sold: <?= $product['sold_count'] ?></div>
                            <div>Views: <?= $product['view_count'] ?></div>
                            <div class="timestamp">Added: <?= date('Y-m-d', strtotime($product['created_at'])) ?></div>
                        </td>
                        <td><span class="status-badge <?= strtolower($product['status']) ?>"><?= $product['status'] ?></span></td>
                        <td><span class="featured-badge <?= $product['is_featured'] ? 'yes' : 'no' ?>"><?= $product['is_featured'] ? 'Yes' : 'No' ?></span></td>
                        <td class="manage-product-btn">
                            <button onclick="editProduct(<?= $product['productId'] ?>)" class="edit-button">Edit</button>
                            <button onclick="deleteProduct(<?= $product['productId'] ?>)" class="delete-button">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="10">No products available.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
