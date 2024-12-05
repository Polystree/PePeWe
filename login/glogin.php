<?php
require_once '../vendor/autoload.php';
require_once __DIR__ . '/../includes/Database.php';
session_start();

use Google\Client;
use Google\Service\Oauth2;

try {
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
        
        // Sanitize user data
        $name = $db->real_escape_string($userInfo->name);
        $email = $db->real_escape_string($userInfo->email);
        $picture = $db->real_escape_string($userInfo->picture ?? '');
        
        // Generate a secure random token for password
        $token = bin2hex(random_bytes(32));
        $hashed_token = password_hash($token, PASSWORD_DEFAULT);

        // Check if user exists
        $stmt = $db->prepare("SELECT id, username, profile_image FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            // Create new user with profile image and hashed token
            $stmt = $db->prepare("INSERT INTO users (username, email, password, profile_image, oauth_provider) VALUES (?, ?, ?, ?, 'google')");
            $stmt->bind_param("ssss", $name, $email, $hashed_token, $picture);
            if (!$stmt->execute()) {
                throw new Exception("Failed to create user account");
            }
            $_SESSION['username'] = $name;
            $_SESSION['userId'] = $db->insert_id;
            $_SESSION['profile_image'] = $picture;
        } else {
            // Update existing user's profile image if it has changed
            if ($picture && $picture !== $user['profile_image']) {
                $stmt = $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                $stmt->bind_param("si", $picture, $user['id']);
                $stmt->execute();
            }
            
            $_SESSION['username'] = $user['username'];
            $_SESSION['userId'] = $user['id'];
            $_SESSION['profile_image'] = $picture ?: $user['profile_image'];
        }

        header("Location: /");
        exit();
    } else {
        $authUrl = $client->createAuthUrl();
        header("Location: " . $authUrl);
        exit();
    }
} catch (Exception $e) {
    $_SESSION['errors']['login'] = 'Google login failed: ' . $e->getMessage();
    header("Location: /login");
    exit();
}
?>
