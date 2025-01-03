<?php
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../includes/Database.php';
session_start();

function downloadAndSaveProfileImage($imageUrl, $username) {
    $targetDir = dirname(__DIR__) . "/assets/img/profile/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $randomNumber = mt_rand(1000, 9999);
    $extension = 'jpg';
    $newFileName = $randomNumber . '-' . $username . '.' . $extension;
    $targetPath = $targetDir . $newFileName;

    $imageContent = file_get_contents($imageUrl);
    if ($imageContent !== false) {
        file_put_contents($targetPath, $imageContent);
        return "/assets/img/profile/" . $newFileName;
    }
    return false;
}

use Google\Client;
use Google\Service\Oauth2;

$config = include(__DIR__ . '/../config/config.php');
$google_config = $config['google'];

$client = new Client();
$client->setClientId($google_config['client_id']);
$client->setClientSecret($google_config['client_secret']);
$client->setRedirectUri($google_config['redirect_uri']);
$client->addScope(['email', 'profile']);

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $oauth2 = new Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $db = Database::getInstance();
    
    $name = $db->real_escape_string($userInfo->name);
    $email = $db->real_escape_string($userInfo->email);
    
    $localImagePath = '';
    if ($userInfo->picture) {
        $localImagePath = downloadAndSaveProfileImage($userInfo->picture, $name);
    }
    if (!$localImagePath) {
        $localImagePath = '/assets/img/Generic avatar.svg';
    }
    $localImagePath = $db->real_escape_string($localImagePath);

    $token = bin2hex(random_bytes(32));
    $hashed_token = password_hash($token, PASSWORD_DEFAULT);

    $stmt = $db->prepare("SELECT id, username, profile_image FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $stmt = $db->prepare("INSERT INTO users (username, email, password, profile_image, oauth_provider) VALUES (?, ?, ?, ?, 'google')");
        $stmt->bind_param("ssss", $name, $email, $hashed_token, $localImagePath);
        $stmt->execute();
        $_SESSION['username'] = $name;
        $_SESSION['userId'] = $db->insert_id;
        $_SESSION['profile_image'] = $localImagePath;
    } else {
        $stmt = $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
        $stmt->bind_param("si", $localImagePath, $user['id']);
        $stmt->execute();
        
        $_SESSION['username'] = $user['username'];
        $_SESSION['userId'] = $user['id'];
        $_SESSION['profile_image'] = $localImagePath;
    }

    header("Location: /");
    exit();
} else {
    $authUrl = $client->createAuthUrl();
    header("Location: " . $authUrl);
    exit();
}
?>
