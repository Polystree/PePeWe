<?php
include '../login/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $image_path = 'img/' . basename($_FILES['image']['name']);
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
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
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
    <a href="index.php" class="link">Back to shopping</a>
</body>
</html>
