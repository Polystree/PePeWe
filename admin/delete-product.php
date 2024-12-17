<?php
session_start();

$config = include(__DIR__ . '/../config/config.php');
$db_config = $config['db'];

$connect = new mysqli(
    $db_config['host'],
    $db_config['username'], 
    $db_config['password'],
    $db_config['database']
);

if ($connect->connect_error) {
    error_log("Database connection failed: " . $connect->connect_error);
    die("Connection failed. Please try again later.");
}

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

        if ($image_path) {
            $image_path = ltrim($image_path, './');
            $absolute_path = __DIR__ . '/../' . $image_path;
            $absolute_path = realpath($absolute_path);
            
            error_log("Attempting to delete image at: " . $absolute_path);
            
            if ($absolute_path && file_exists($absolute_path)) {
                unlink($absolute_path);
                error_log("Image deleted successfully: " . $absolute_path);
            } else {
                error_log("Image file not found at: " . $absolute_path);
            }
        }

        $stmt = $connect->prepare("DELETE FROM products WHERE productId = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            throw new Exception("Failed to delete the product.");
        }
        $stmt->close();

        $stmt = $connect->prepare("DELETE FROM cart WHERE productId = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->close();

        $productDir = __DIR__ . '/../products/' . strtolower(str_replace(' ', '-', $product_name));
        if (is_dir($productDir)) {
            array_map('unlink', glob("$productDir/*.*"));
            rmdir($productDir);
        }

        $connect->commit();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    } catch (Exception $e) {
        $connect->rollback();
        error_log("Error deleting product: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Error deleting product: ' . $e->getMessage()
        ]);
        exit();
    }
}

$connect->close();
?>