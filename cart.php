<?php
include './login/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $productName => $quantity) {
        $quantity = (int) $quantity;
        if ($quantity > 0) {
            $stmt = $connect->prepare("UPDATE cart SET quantity = ? WHERE product_name = ?");
            $stmt->bind_param("is", $quantity, $productName);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $connect->prepare("DELETE FROM cart WHERE product_name = ?");
            $stmt->bind_param("s", $productName);
            $stmt->execute();
            $stmt->close();
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$query = "SELECT product_name, price, image_path, quantity FROM cart";
$result = $connect->query($query);
$totalPrice = 0;
?>
<link rel='stylesheet' href='/assets/css/cart.css' />

<div class="breadcrumb">
    <a href="#">Home</a> / <span>Cart List</span>
</div>
<div class='container'>
    <form method="post" action="">
        <button class="btn" type="button" onclick="window.location.href='/'">Return To Shop</button>
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
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <img src="../<?php echo htmlspecialchars($row['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($row['product_name']); ?>" />
                                    <div>
                                        <p><?php echo htmlspecialchars($row['product_name']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                            <td>
                            <form method="post" action="">
                                <label for="quantity_<?php echo $row['product_name']; ?>">Quantity:</label>
                                <input type="number" id="quantity_<?php echo $row['product_name']; ?>" name="quantity[<?php echo $row['product_name']; ?>]" value="<?php echo $row['quantity']; ?>" min="0" />
                                <input type="submit" value="Update" />
                            </form>
                            </td>
                            <td>Rp <?php echo number_format($row['price'] * $row['quantity'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php $totalPrice += $row['price'] * $row['quantity']; ?>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Your cart is empty.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
    <div class="coupon">
        <input type="text" placeholder="Coupon Code" />
        <button type="button">Apply Coupon</button>
    </div>
    <div class="cart-total">
        <h3>Cart Total</h3>
        <p><span>Subtotal:</span> <span>Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span></p>
        <p><span>Shipping:</span> <span>Free</span></p>
        <p><span>Total:</span> <span>Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span></p>
        <button class="btn" type="button" onclick="window.location.href='/'">Process to Checkout</button>
    </div>
</div>