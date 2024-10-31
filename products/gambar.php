
<?php
include '../login/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cart'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $image_path = $_POST['image_path'];
    $quantity = $_POST['quantity'];

    $stmt = $connect->prepare('INSERT INTO cart (product_name, price, image_path, quantity) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sisi', $product_name, $price, $image_path, $quantity);

    if ($stmt->execute()) {
        echo 'Product added to cart successfully.';
    } else {
        echo 'Error: ' . $stmt->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8' />
    <meta name='viewport' content='initial-scale=1, width=device-width' />
    <title>gambar - Product Details</title>
    <link rel='stylesheet' href='../assets/css/style.css' />
</head>
<body>
<?php include '../header.php'; ?>
    <div class='product-details'>
        <h1>gambar</h1>
        <img src='../assets/img/product/DUMBell.webp' alt='gambar' />
        <p>Rp 23123</p>
        <p>Description: 412rwr sasdf</p>
        <form method='POST' action='delete_product.php'>
            <input type='hidden' name='product_name' value='gambar'>
            <input type='hidden' name='image_path' value='../assets/img/product/DUMBell.webp'>
            <button type='submit' class='delete-button'>Delete Product</button>
        </form>
        <form method='POST'>
            <input type='hidden' name='product_name' value='gambar'>
            <input type='hidden' name='price' value='23123'>
            <input type='hidden' name='image_path' value='../assets/img/product/DUMBell.webp'>
            <label for='quantity'>Quantity:</label>
            <input type='number' name='quantity' id='quantity' value='1' min='1'>
            <button type='submit' class='next-button' name='cart'>Add to Cart</button>
        </form>
    </div>
<?php include '../footer.php'; ?>
</body>
</html>
