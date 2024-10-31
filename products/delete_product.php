<?php
include '../login/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['product_name'];
    $imagePath = $_POST['image_path'];

    $stmt = $connect->prepare("DELETE FROM products WHERE name = ?");
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $stmt = $connect->prepare("DELETE FROM cart WHERE product_name = ?");
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $stmt->close();

    $fileName = '../products/' . strtolower(str_replace(' ', '-', $productName)) . '.php';
    if (file_exists($fileName)) {
        unlink($fileName);
    }

    // Delete the product image
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

    $connect->close();
    header("Location: /");
    exit();
}
?>