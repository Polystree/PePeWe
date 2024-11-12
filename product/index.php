<?php
include '../login/database.php';
function generateProductPage($name, $price, $image_path, $description) {
    $productPageContent = "
<?php
include '../login/database.php';

if (\$_SERVER['REQUEST_METHOD'] == 'POST' && isset(\$_POST['cart'])) {
    \$product_name = \$_POST['product_name'];
    \$price = \$_POST['price'];
    \$image_path = \$_POST['image_path'];
    \$quantity = \$_POST['quantity'];

    \$stmt = \$connect->prepare('INSERT INTO cart (product_name, price, image_path, quantity) VALUES (?, ?, ?, ?)');
    \$stmt->bind_param('sisi', \$product_name, \$price, \$image_path, \$quantity);
    \$stmt->execute();
    \$stmt->close();
}
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8' />
    <meta name='viewport' content='initial-scale=1, width=device-width' />
    <title>$name - Product Details</title>
    <link rel='stylesheet' href='../assets/css/style.css' />
    <link rel='stylesheet' href='../assets/css/product.css' />
</head>
<body>
<?php include '../header.php'; ?>
    <div class='product-details'>
        <h1>$name</h1>
        <img src='$image_path' alt='$name' />
        <p>Rp $price</p>
        <p>Description: $description</p>
        <?php if (isset(\$_SESSION['username']) && \$_SESSION['username'] === 'admin'): ?>
        <form method='POST' action='delete_product.php'>
            <input type='hidden' name='product_name' value='$name'>
            <input type='hidden' name='image_path' value='$image_path'>
            <button type='submit' class='delete-button'>Delete Product</button>
        </form>
        <?php endif; ?>
        <form method='POST'>
            <input type='hidden' name='product_name' value='$name'>
            <input type='hidden' name='price' value='$price'>
            <input type='hidden' name='image_path' value='$image_path'>
            <label for='quantity'>Quantity:</label>
            <input type='number' name='quantity' id='quantity' value='1' min='1'>
            <button type='submit' class='next-button' name='cart'>Add to Cart</button>
        </form>
    </div>
<?php include '../footer.php'; ?>
</body>
</html>
";

    $fileName = '../products/' . strtolower(str_replace(' ', '-', $name)) . '.php';
    file_put_contents($fileName, $productPageContent);
    return $fileName; // Return the generated file name for URL
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image_path = '../assets/img/product/' . basename($_FILES['image']['name']);
    $description = $_POST['description'];

    // Move the uploaded file to the desired directory
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

    $productPageUrl = generateProductPage($name, $price, $image_path, $description);

    $stmt = $connect->prepare("INSERT INTO products (name, price, image_path, description, url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsss", $name, $price, $image_path, $description, $productPageUrl);
    $stmt->execute();
    $stmt->close();
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>iniGadget</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo-light.svg" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" />
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="main">
        <div id="upload-product-title">
            <span>
                Add New Product
            </span>
        </div>
        <form method="POST" id="upload-product-form" enctype="multipart/form-data">
        <div class="upload-product-item">
            <div class="credential-form">
                <label for="image" id="upload-image-label" class="upload-label">Product Image</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>
            <div class="credential-form">
                <label for="name" class="upload-label">Product Name</label>
                <input type="text" name="name" placeholder="Your product name..." required>
            </div>
            <div class="credential-form">
                <label for="price" class="upload-label">Price</label>
                <div class="price-input">
                    <input type="number" name="price" id="price" placeholder="Your product price..." required>
                    <div class="currency-symbol-container">
                        <span class="currency-symbol">Rp</span>
                    </div>
                </div>
            </div>
            <div class="credential-form form-description">
                <label for="description" class="upload-label">Description</label>
                <textarea name="description" id="description" class="input-description" rows="4" required></textarea>
            </div>
        <button type="submit" class="next-button">Add Product</button>
        </div>
        </form>
    </div>
    <?php include '../footer.php'; ?>
</body>
</html>
