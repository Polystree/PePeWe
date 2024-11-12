<?php
session_start();
include '../login/database.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    echo "Access denied.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['productId'])) {
        echo "Invalid request.";
        exit();
    }

    $productId = $_POST['productId'];

    $connect->begin_transaction();

    try {
        $stmt = $connect->prepare("SELECT name, image_path FROM products WHERE productId = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->bind_result($product_name, $image_path);
        if (!$stmt->fetch()) {
            throw new Exception("No product found with the given ID.");
        }
        $stmt->close();

        $stmt = $connect->prepare("DELETE FROM products WHERE productId = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            throw new Exception("Failed to delete the product.");
        }
        $stmt->close();

        $stmt = $connect->prepare("DELETE FROM cart WHERE product_name = ?");
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $stmt->close();

        $fileName = '../products/' . strtolower(str_replace(' ', '-', $product_name)) . '.php';
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        $connect->commit();

        header("Location: /admin/index.php");
        exit();
    } catch (Exception $e) {
        $connect->rollback();
        echo "Error deleting product: " . $e->getMessage();
    }
}
?>