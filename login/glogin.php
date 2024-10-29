<?php
require_once '../vendor/autoload.php';
include 'database.php';
session_start();

$config = parse_ini_file('auth.txt');
$client = new Google_Client();
$client->setClientId($config['client_id']);
$client->setClientSecret($config['client_secret']);
$client->setRedirectUri($config['redirect_uri']);
$client->addScope(['email', 'profile']);

if (isset($_GET['code'])) {
    handleGoogleCallback($client);
} else {
    redirectToGoogleLogin($client);
}

function handleGoogleCallback($client) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    saveUserInfo($userInfo);

    header("Location: ../index.php");
    exit();
}

function redirectToGoogleLogin($client) {
    if (!isset($_SESSION['access_token'])) {
        $authUrl = $client->createAuthUrl();
        header("Location: $authUrl");
        exit();
    }
}

function saveUserInfo($userInfo) {
    global $connect; 
    $username = $userInfo->name;
    $email = $userInfo->email;

    checkUserExists($username, $email);
}

function checkUserExists($username, $email) {
    global $connect; 

    $stmt = $connect->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $stmt = $connect->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, '')");
        $stmt->execute([$username, $email]);
        $_SESSION['user_id'] = $username;
        $_SESSION['username'] = $username;
    } else {
        $_SESSION['user_id'] = $username;
        $_SESSION['username'] = $username;
    }
}
?>
