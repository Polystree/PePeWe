<?php
$config = include(__DIR__ . '/../config/config.php');
$db_config = $config['db'];

$conn = new mysqli(
    $db_config['db_server_name'],
    $db_config['db_username'],
    $db_config['db_password'],
    $db_config['db_name']
);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

return $conn;
?>