<?php
require_once __DIR__ . '/../includes/Database.php';

class Cart {
    private $db;
    private static $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getCartItems($userId) {
        $cacheKey = "cart_$userId";
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $stmt = $this->db->prepare(
            "SELECT c.*, p.name as product_name, p.price, p.image_path 
             FROM cart c 
             JOIN products p ON c.productId = p.productId 
             WHERE c.userId = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        self::$cache[$cacheKey] = $result;
        return $result;
    }

    public function updateQuantity($userId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $productId);
        }

        $stmt = $this->db->prepare(
            "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?"
        );
        $stmt->bind_param("iii", $quantity, $userId, $productId);
        $success = $stmt->execute();
        
        unset(self::$cache["cart_$userId"]);
        return $success;
    }
}

$userId = $_SESSION['userId'] ?? null;
if (!$userId) {
    header('Location: /login');
    exit();
}

$cart = new Cart();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $productId => $quantity) {
        $cart->updateQuantity($userId, $productId, (int)$quantity);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$cartItems = $cart->getCartItems($userId);
$totalPrice = array_reduce($cartItems, function($carry, $item) {
    return $carry + ($item['price'] * $item['quantity']);
}, 0);
?>

<php 
include __DIR__ . '/header.php';
include __DIR__ . '/../templates/cart/index.php'; 
include __DIR__ . '/footer.php';
?>