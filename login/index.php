<?php
session_start();

try {
    require_once '../includes/Auth.php';
    $auth = new Auth();
} catch (Exception $e) {
    die("Configuration error: " . $e->getMessage());
}

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $result = $auth->login(
            $_POST['username'], 
            $_POST['password'], 
            $_POST['captcha'],
            isset($_POST['remember_me'])
        );
        if ($result['success']) {
            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/");
                
                $stmt = $db->prepare("UPDATE users SET remember_token = ? WHERE username = ?");
                $stmt->bind_param("ss", $token, $_POST['username']);
                $stmt->execute();
            }
            
            header('Location: ' . ($result['isAdmin'] ? '/admin' : '/'));
            exit();
        }
        $errors['login'] = $result['error'];
    } elseif (isset($_POST['register'])) {
        if ($_POST['captcha'] != $_SESSION['captcha_answer']) {
            $errors['register'] = "Invalid captcha answer";
        } else {
            $result = $auth->register($_POST['username'], $_POST['email'], $_POST['password']);
            if ($result['success']) {
                $success = true;
            } else {
                $errors['register'] = $result['error'];
            }
        }
    }
}

$number1 = rand(1, 10);
$number2 = rand(1, 10);
$_SESSION['captcha_answer'] = $number1 + $number2;

if (!isset($_SESSION['username']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $db->prepare("SELECT username FROM users WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $_SESSION['username'] = $row['username'];
        header('Location: /');
        exit();
    }
}

$title = 'Login - iniGadget';
ob_start();
include '../templates/login-form.php';
$content = ob_get_clean();
include '../templates/base.php';