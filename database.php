<?php
$config = parse_ini_file('/login/auth.txt');
$servername = $config['db_server_name'];
$username = $config['db_username'];
$password = $config['db_password'];
$dbname = $config['db_name'];

$connect = new mysqli($servername, $username, $password, $dbname);

if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}
?>