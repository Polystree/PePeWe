<?php
include '../login/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image_path = '../assets/img/product/' . basename($_FILES['image']['name']);
    $description = $_POST['description'];

    // Move the uploaded file to the desired directory
    move_uploaded_file($_FILES['image']['tmp_name'], $image_path);

    $stmt = $connect->prepare("INSERT INTO products (name, price, image_path, description) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $name, $price, $image_path, $description);
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
        <div class="back">
            <a href="/">
                <- Back to shopping </a>
        </div>
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
                <input type="number" name="price" placeholder="Your product price..." required>
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