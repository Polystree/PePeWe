
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = Database::getInstance();
    
    $captcha = $_POST['captcha'] ?? '';
    $expected_sum = $_SESSION['captcha_numbers']['sum'] ?? 0;
    
    if ((int)$captcha !== $expected_sum) {
        $_SESSION['errors']['login'] = 'Invalid captcha answer';
        header('Location: /login');
        exit;
    }

    $username = $db->real_escape_string($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $_SESSION['errors']['login'] = 'Username and password are required';
        header('Location: /login');
        exit;
    }

    $stmt = $db->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['userId'] = $user['id'];
        
        if (isset($_POST['remember_me'])) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/');
            
            $stmt = $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->bind_param("si", $token, $user['id']);
            $stmt->execute();
        }
        
        header('Location: /');
    } else {
        $_SESSION['errors']['login'] = 'Invalid username or password';
        header('Location: /login');
    }
    exit;
} else {
    header('Location: /login');
    exit;
}