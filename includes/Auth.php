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

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashedPassword);
        
        try {
            $stmt->execute();
            return ['success' => true];
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                return ['success' => false, 'error' => 'Username or email already exists'];
            }
            return ['success' => false, 'error' => 'Registration failed'];
        }
    }

    private function isAdmin($userId) {
        // Implement admin check logic here
        return false;
    }
}