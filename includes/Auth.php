<?php
require_once __DIR__ . '/Database.php';

class Auth {
    private $db;
    private static $instance = null;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function login($identifier, $password, $captcha = null) {
        if (isset($_SESSION['captcha_answer']) && $captcha != $_SESSION['captcha_answer']) {
            return ['success' => false, 'error' => 'Invalid captcha'];
        }

        $stmt = $this->db->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                return ['success' => true, 'isAdmin' => $this->isAdmin($user['id'])];
            }
        }
        
        return ['success' => false, 'error' => 'Invalid credentials'];
    }

    public function register($username, $email, $password) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email format'];
        }

        $check = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            return ['success' => false, 'error' => 'Username or email already exists'];
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);
        $stmt->execute();
        
        return ['success' => true];
    }

    private function isAdmin($userId) {
        return false;
    }
}