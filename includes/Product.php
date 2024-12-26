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
        $sql = "SELECT * FROM products WHERE name LIKE ? OR description LIKE ?" . ($sort === 'price_asc' ? " ORDER BY price ASC" : ($sort === 'price_desc' ? " ORDER BY price DESC" : ""));
        $searchTerm = "%{$query}%";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getFlashSaleProducts($limit = 8) {
        $sql = "SELECT p.*, d.discount_percent as discount, p.price * (100 - d.discount_percent) / 100 as discounted_price, COALESCE(p.sold_count, 0) as sold_count FROM products p INNER JOIN discounts d ON p.productId = d.product_id WHERE d.is_flash_sale = 1 AND p.status = 'active' AND CURRENT_TIMESTAMP BETWEEN d.start_date AND d.end_date";
        $stmt = $this->db->prepare($sql);
        if (!$stmt->execute()) return [];
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getBestSelling($limit = 5) {
        $sql = "SELECT *, (sold_count * 2 + view_count) as popularity FROM products ORDER BY popularity DESC LIMIT ?";
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

    public function getNewArrivals($limit = 6) {
        $query = "SELECT p.*, CASE WHEN d.discount_percent > 0 THEN p.price * (100 - d.discount_percent) / 100 ELSE p.price END as discounted_price, COALESCE(d.discount_percent, 0) as discount FROM products p LEFT JOIN discounts d ON p.productId = d.product_id AND CURRENT_TIMESTAMP BETWEEN d.start_date AND d.end_date WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($query);
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

    public function getFeaturedProducts($limit = 8) {
        $sql = "SELECT p.*, CASE WHEN d.discount_percent > 0 THEN p.price * (100 - d.discount_percent) / 100 ELSE p.price END as discounted_price, COALESCE(d.discount_percent, 0) as discount FROM products p LEFT JOIN discounts d ON p.productId = d.product_id AND CURRENT_TIMESTAMP BETWEEN d.start_date AND d.end_date WHERE p.is_featured = 1 AND p.status = 'active' LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getTrendingProducts($limit = 8) {
        $query = "SELECT *, (sold_count * 2 + view_count) as popularity FROM products ORDER BY popularity DESC LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllProducts($limit = 20) {
        $sql = "SELECT p.*, CASE WHEN d.discount_percent > 0 THEN p.price * (100 - d.discount_percent) / 100 ELSE p.price END as discounted_price, COALESCE(d.discount_percent, 0) as discount FROM products p LEFT JOIN discounts d ON p.productId = d.product_id AND CURRENT_TIMESTAMP BETWEEN d.start_date AND d.end_date WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}