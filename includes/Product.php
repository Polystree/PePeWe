<?php
require_once __DIR__ . '/Database.php';

class Product {
    private $db;
    private static $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getRecentlyUpdated($limit = 10) {
        $sql = "SELECT * FROM products ORDER BY updated_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function search($query, $sort = '') {
        $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?";
        if ($sort === 'price_asc') {
            $sql .= " ORDER BY price ASC";
        } elseif ($sort === 'price_desc') {
            $sql .= " ORDER BY price DESC";
        }
        
        $searchTerm = "%{$query}%";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getFlashSaleProducts($limit = 10) {
        $sql = "SELECT * FROM products WHERE discount > 0 ORDER BY discount DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getBestSelling($limit = 5) {
        $sql = "SELECT *, (sold_count * 2 + view_count) as popularity 
                FROM products 
                ORDER BY popularity DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByCategory($category, $limit = 8) {
        $sql = "SELECT * FROM products WHERE category = ? LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $category, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getNewArrivals($limit = 4) {
        $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getExploredProducts($limit = 8) {
        $sql = "SELECT * FROM products ORDER BY view_count DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}