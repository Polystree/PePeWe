<?php
session_start();

if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    require_once '../includes/Database.php';
    $db = Database::getInstance();
    $stmt = $db->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    setcookie('remember_token', '', time() - 3600, '/');
}

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("Location: /login"); 
exit();
?>