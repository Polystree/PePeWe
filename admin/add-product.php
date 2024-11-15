<?php
include '../login/database.php';

$product = [
    'name' => '',
    'price' => '',
    'image_path' => '',
    'description' => '',
    'quantity' => ''
];
$productId = null;

if (isset($_GET['productId'])) {
    $productId = (int)$_GET['productId'];
    $stmt = $connect->prepare("SELECT name, price, image_path, description, quantity FROM products WHERE productId = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($product['name'], $product['price'], $product['image_path'], $product['description'], $product['quantity']);
    $stmt->fetch();
    $stmt->close();
}

function generateProductPage($name, $price, $image_path, $description, $quantity, $productId) {
    $productPageContent = "
<?php
session_start();
include '../login/database.php';

if (\$_SERVER['REQUEST_METHOD'] == 'POST' && isset(\$_POST['cart'])) {
    \$product_name = \$_POST['product_name'];
    \$price = \$_POST['price'];
    \$image_path = \$_POST['image_path'];
    \$quantity = \$_POST['quantity'];
    \$userId = \$_SESSION['userId'];

    \$stmt = \$connect->prepare('SELECT productId FROM products WHERE name = ? AND price = ? AND image_path = ?');
    \$stmt->bind_param('sis', \$product_name, \$price, \$image_path);
    \$stmt->execute();
    \$stmt->bind_result(\$productId);
    \$stmt->fetch();
    \$stmt->close();

    \$stmt = \$connect->prepare('INSERT INTO cart (product_name, price, image_path, quantity, productId, userId) VALUES (?, ?, ?, ?, ?, ?)');
    \$stmt->bind_param('sisiii', \$product_name, \$price, \$image_path, \$quantity, \$productId, \$userId);
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
<div class='cart'>
    <?php include '../cart.php'; ?>
</div>
<div class='main'>
    <div class='product-details'>
        <h1>$name</h1>
        <img src='$image_path' alt='$name' />
        <p>Rp $price</p>
        <p>Description: $description</p>
        <form method='POST'>
            <input type='hidden' name='product_name' value='$name'>
            <input type='hidden' name='price' value='$price'>
            <input type='hidden' name='image_path' value='$image_path'>
            <input type='hidden' name='productId' value='<?php echo isset(\$productId) ? \$productId : ''; ?>'>
            <label for='quantity'>Quantity:</label>
            <input type='number' name='quantity' id='quantity' value='1' min='1'>
            <button type='submit' class='next-button' name='cart'>Add to Cart</button>
        </form>
    </div>
</div>
<?php include '../footer.php'; ?>
</body>
</html>
";

    $fileName = '../products/' . strtolower(str_replace(' ', '-', $name)) . '.php';
    file_put_contents($fileName, $productPageContent);
    return $fileName;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_path = '../assets/img/product/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    } else {
        $image_path = $product['image_path'];
    }

    $url = generateProductPage($name, $price, $image_path, $description, $quantity, $productId);

    if (isset($_POST['productId'])) {
        $productId = (int)$_POST['productId'];
        $stmt = $connect->prepare("UPDATE products SET name = ?, price = ?, image_path = ?, description = ?, quantity = ?, url = ? WHERE productId = ?");
        $stmt->bind_param("sissisi", $name, $price, $image_path, $description, $quantity, $url, $productId);
    } else {
        $stmt = $connect->prepare("INSERT INTO products (name, price, image_path, description, quantity, url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissis", $name, $price, $image_path, $description, $quantity, $url);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: index.php');
    exit();
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title><?php echo isset($productId) ? 'Edit Product' : 'Add New Product'; ?> - iniGadget</title>
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
                <?php echo isset($productId) ? 'Edit Product' : 'Add New Product'; ?>
            </span>
        </div>
        <form method="POST" id="upload-product-form" enctype="multipart/form-data">
            <?php if (isset($productId)): ?>
                <input type="hidden" name="productId" value="<?php echo htmlspecialchars($productId); ?>">
            <?php endif; ?>
            <div class="upload-product-item">
                <div class="credential-form">
                    <label for="image" id="upload-image-label" class="upload-label">Product Image</label>
                    <input type="file" name="image" id="image" accept="image/*" <?php echo isset($productId) ? '' : 'required'; ?>>
                </div>
                <div class="credential-form">
                    <label for="name" class="upload-label">Product Name</label>
                    <input type="text" name="name" placeholder="Your product name..." value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="credential-form">
                    <label for="price" class="upload-label">Price</label>
                    <div class="price-input">
                        <input type="number" name="price" id="price" placeholder="Your product price..." value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        <div class="currency-symbol-container">
                            <span class="currency-symbol">Rp</span>
                        </div>
                    </div>
                </div>
                <div class="credential-form form-description">
                    <label for="description" class="upload-label">Description</label>
                    <textarea name="description" id="description" class="input-description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <div class="credential-form">
                    <label for="quantity" class="upload-label">Quantity</label>
                    <input type="number" name="quantity" id="quantity" placeholder="Quantity of your product..." value="<?php echo htmlspecialchars($product['quantity']); ?>" min="1" required>
                </div>
                <button type="submit" class="next-button">
                    <?php echo isset($productId) ? 'Update Product' : 'Add Product'; ?>
                </button>
            </div>
        </form>
    </div>
    <?php include '../footer.php'; ?>
</body>
</html>
