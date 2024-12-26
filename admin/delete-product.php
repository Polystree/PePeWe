<?php
session_start();
$config = include(__DIR__ . '/../config/config.php');
$connect = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') exit();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['productId'])) {
    $productId = $_POST['productId'];
    $connect->begin_transaction();

    try {
        $stmt = $connect->prepare("SELECT name, image_path FROM products WHERE productId = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $stmt->bind_result($product_name, $image_path);
        $stmt->fetch();
        $stmt->close();

        if ($image_path) {
            $absolute_path = realpath(__DIR__ . '/../' . ltrim($image_path, './'));
            if ($absolute_path && file_exists($absolute_path)) unlink($absolute_path);
        }

        $stmt = $connect->prepare("DELETE FROM products WHERE productId = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
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
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $connect->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
$connect->close();
?>