<?php
require_once '../vendor/autoload.php';
include '../database.php';
session_start();

$client = new Google_Client();
$client->setClientId('52407408904-p3j9v8tce3mogo6qul7mt0nboade0smk.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/pepewe-main/login/glogin.php');
$client->addScope(['email', 'profile']);

if (isset($_GET['code'])) {
    handleGoogleCallback($client);
} else {
    redirectToGoogleLogin($client);
}

function handleGoogleCallback($client) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token['access_token']);

    // Get user info
    $oauth2 = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    // Save user info to your database
    saveUserInfo($userInfo);

    // Redirect to home page
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
    global $conn; 
    $username = $userInfo->name;
    $email = $userInfo->email;

    // Check if user exists in your database
    checkUserExists($username, $email);
}

function checkUserExists($username, $email) {
    global $conn; 

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        // User does not exist, create a new user
        $stmt = $conn->prepare("INSERT INTO users (username, email) VALUES (?, ?)");
        $stmt->execute([$username, $email]);
        $_SESSION['user_id'] = $username;
    } else {
        // Set session variable for logged-in user
        $_SESSION['user_id'] = $username;
    }
}
?>
