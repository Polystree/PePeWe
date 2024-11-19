<?php
session_start();
include '../login/database.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    echo "Access denied.";
    exit();
}

$query = "SELECT productId, name, image_path, description, price, quantity FROM products";
$result = $connect->query($query);

if (!$result) {
    die("Query failed: " . $connect->error);
}

$products = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>iniGadget</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo-light.svg" />
    <title>Manage Products - iniGadget</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/cart.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" />
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="main-content" style="margin: 0 2rem;">
        <h2>Manage Products</h2>
        <button onclick="window.location.href='add-product.php'" class="add-product-btn">Add Product</button>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price (Rp)</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            style='width: 100px; border-radius: 5px;' />
                    </td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td style='font-size: 0.7rem;'><?php echo htmlspecialchars($product['description']); ?></td>
                    <td><?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                    <td class='manage-product-btn'>
                        <button
                            onclick="window.location.href='add-product.php?productId=<?php echo $product['productId']; ?>'"
                            class="edit-button">Edit</button>
                        <form method="POST" action="../products/delete_product.php" style="display:inline;">
                            <input type="hidden" name="productId" value="<?php echo $product['productId']; ?>">
                            <button type="submit" class="delete-button"
                                onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5">No products available.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include '../footer.php'; ?>
</body>

</html>