<?php
session_start();
include '../login/database.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    echo "Access denied.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $image_path = $_POST['image_path'];

    $connect->begin_transaction();

    try {
        $stmt = $connect->prepare("DELETE FROM products WHERE name = ?");
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            throw new Exception("No product found with the given name.");
        }
        $stmt->close();

        $stmt = $connect->prepare("DELETE FROM cart WHERE product_name = ?");
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $stmt->close();

        $connect->commit();

        $fileName = '../products/' . strtolower(str_replace(' ', '-', $product_name)) . '.php';
        if (file_exists($fileName)) {
            unlink($fileName);
        }

        header("Location: /");
        exit();
    } catch (Exception $e) {
        $connect->rollback();
        echo "Error deleting product: " . $e->getMessage();
    }
}
?>