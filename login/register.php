
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
        $_SESSION['errors']['register'] = 'Invalid captcha answer';
        header('Location: /login');
        exit;
    }

    $username = $db->real_escape_string($_POST['username'] ?? '');
    $email = $db->real_escape_string($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['errors']['register'] = 'All fields are required';
        header('Location: /login');
        exit;
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['errors']['register'] = 'Username already exists';
        header('Location: /login');
        exit;
    }

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['errors']['register'] = 'Email already exists';
        header('Location: /login');
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = true;
        header('Location: /login');
    } else {
        $_SESSION['errors']['register'] = 'Registration failed. Please try again.';
        header('Location: /login');
    }
    exit;
} else {
    header('Location: /login');
    exit;
}