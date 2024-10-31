<?php
session_start();
include '../login/database.php';

$query = "SELECT product_name, price, image_path, quantity FROM cart";
$result = $connect->query($query);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $productName => $quantity) {
        $quantity = (int)$quantity;
        if ($quantity > 0) {
            $updateQuery = "UPDATE cart SET quantity = ? WHERE product_name = ?";
            $stmt = $connect->prepare($updateQuery);
            $stmt->bind_param("is", $quantity, $productName);
            $stmt->execute();
        } else {
            $deleteQuery = "DELETE FROM cart WHERE product_name = ?";
            $stmt = $connect->prepare($deleteQuery);
            $stmt->bind_param("s", $productName);
            $stmt->execute();
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8' />
    <meta name='viewport' content='initial-scale=1, width=device-width' />
    <title>Shopping Cart</title>
    <link rel='stylesheet' href='../assets/css/style.css' />
</head>
<body>
<?php include '../header.php'; ?>
    <div class='cart'>
        <h1>Your Shopping Cart</h1>
        <ul>
            <?php if ($result->num_rows > 0): ?>
                <?php 
                $totalPrice = 0;
                while($row = $result->fetch_assoc()): 
                    $totalPrice += $row['price'] * $row['quantity'];
                ?>
                    <li>
                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['product_name']); ?>" />
                        <h2><?php echo htmlspecialchars($row['product_name']); ?></h2>
                        <p>Price: Rp<?php echo number_format($row['price']); ?></p>
                        <form method="post" action="">
                            <label for="quantity_<?php echo $row['product_name']; ?>">Quantity:</label>
                            <input type="number" id="quantity_<?php echo $row['product_name']; ?>" name="quantity[<?php echo $row['product_name']; ?>]" value="<?php echo $row['quantity']; ?>" min="0" />
                            <input type="submit" value="Update" />
                        </form>
                    </li>
                <?php endwhile; ?>
                <h3>Total Price: Rp<?php echo number_format($totalPrice); ?></h3>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </ul>
    </div>
<?php include '../footer.php'; ?>
</body>
</html>
