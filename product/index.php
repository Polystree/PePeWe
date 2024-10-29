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
    <link rel="icon" type="image/x-icon" href="/assets/img/logo-light.svg" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" />
</head>
<body>
    <?php include '../header.php'; ?>
    <h1>Add New Product</h1>
    <form method="POST" enctype="multipart/form-data">
        <label for="name">Product Name:</label>
        <input type="text" name="name" required>
        <label for="price">Price:</label>
        <input type="number" name="price" step="0.01" required>
        <label for="image">Product Image:</label>
        <input type="file" name="image" accept="image/*" required>
        <label for="description">Description: </label>
        <input type="text" name="description"><br>
        <button type="submit">Add Product</button>
    </form>
    <a href="/" class="link">Back to shopping</a>
    <?php include '../footer.php'; ?>
</body>
</html>
