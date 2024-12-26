<?php
session_start();
$config = include(__DIR__ . '/../config/config.php');
$connect = new mysqli($config['db']['host'], $config['db']['username'], $config['db']['password'], $config['db']['database']);

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header('Location: /login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['discountId'])) {
    $discountId = (int)$_POST['discountId'];
    $stmt = $connect->prepare("DELETE FROM discounts WHERE id = ?");
    $stmt->bind_param("i", $discountId);
    $stmt->execute();
    $stmt->close();
}

$connect->close();
header('Location: index.php');
exit();
?>
