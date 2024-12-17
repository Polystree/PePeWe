<?php
require_once __DIR__ . '/Database.php';

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
            "SELECT c.cartId, c.product_name, c.price, c.quantity, c.image_path, c.userId, c.productId 
             FROM cart c 
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
            "UPDATE cart SET quantity = ? WHERE userId = ? AND productId = ?"
        );
        $stmt->bind_param("iii", $quantity, $userId, $productId);
        $success = $stmt->execute();
        
        unset(self::$cache["cart_$userId"]);
        return $success;
    }

    public function removeItem($userId, $productId) {
        $stmt = $this->db->prepare(
            "DELETE FROM cart WHERE userId = ? AND productId = ?"
        );
        $stmt->bind_param("ii", $userId, $productId);
        $success = $stmt->execute();
        
        unset(self::$cache["cart_$userId"]);
        return $success;
    }

    public function addToCart($userId, $productId, $quantity) {
        $stmt = $this->db->prepare(
            "SELECT cartId, quantity FROM cart WHERE userId = ? AND productId = ?"
        );
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $newQuantity = $row['quantity'] + $quantity;
            return $this->updateQuantity($userId, $productId, $newQuantity);
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO cart (userId, productId, quantity, product_name, price, image_path) 
                 SELECT ?, ?, ?, name, price, image_path 
                 FROM products WHERE productId = ?"
            );
            $stmt->bind_param("iiii", $userId, $productId, $quantity, $productId);
            $success = $stmt->execute();
            
            unset(self::$cache["cart_$userId"]);
            return $success;
        }
    }

    public function clearCart($userId) {
        $stmt = $this->db->prepare("DELETE FROM cart WHERE userId = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
}