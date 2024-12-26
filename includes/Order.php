<?php
require_once __DIR__ . '/Database.php';

class Order {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createOrder($userId, $orderNumber, $totalAmount, $shippingAddress, $items, $shippingCost = 0, $shippingMethod = '', $discountAmount = 0) {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_address, shipping_cost, shipping_method, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isdssdd", $userId, $orderNumber, $totalAmount, $shippingAddress, $shippingCost, $shippingMethod, $discountAmount);
            $stmt->execute();
            $orderId = $this->db->insert_id;

            $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $updateStmt = $this->db->prepare("UPDATE products SET quantity = quantity - ?, sold_count = sold_count + ? WHERE productId = ?");
            
            foreach ($items as $item) {
                $stmt->bind_param("iiid", $orderId, $item['productId'], $item['quantity'], $item['price']);
                $stmt->execute();
                $updateStmt->bind_param("iii", $item['quantity'], $item['quantity'], $item['productId']);
                if (!$updateStmt->execute()) throw new Exception();
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function getOrderByNumber($orderNumber) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->bind_param("s", $orderNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        return $order;
    }

    public function updatePaymentStatus($orderNumber, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET payment_status = ? WHERE order_number = ?");
        $stmt->bind_param("ss", $status, $orderNumber);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getUserOrders($userId) {
        $stmt = $this->db->prepare("SELECT o.*, oi.product_id, oi.quantity, oi.price as item_price, p.name, p.image_path FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id LEFT JOIN products p ON oi.product_id = p.productId WHERE o.user_id = ? ORDER BY o.created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        $currentOrder = null;
        while ($row = $result->fetch_assoc()) {
            if ($currentOrder === null || $currentOrder['order_number'] !== $row['order_number']) {
                if ($currentOrder !== null) $orders[] = $currentOrder;
                $currentOrder = ['id' => $row['id'], 'order_number' => $row['order_number'], 'total_amount' => $row['total_amount'], 'status' => 'success', 'created_at' => $row['created_at'], 'items' => []];
            }
            if ($row['product_id']) $currentOrder['items'][] = ['id' => $row['product_id'], 'name' => $row['name'], 'quantity' => $row['quantity'], 'price' => $row['item_price'], 'image_path' => $row['image_path']];
        }
        if ($currentOrder !== null) $orders[] = $currentOrder;
        $stmt->close();
        return $orders;
    }

    public function getOrderDetails($orderNumber) {
        $stmt = $this->db->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.order_number = ?");
        $stmt->bind_param("s", $orderNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        if ($order) {
            $itemsStmt = $this->db->prepare("SELECT p.name, p.description, oi.quantity, oi.price, p.image_path FROM order_items oi JOIN products p ON oi.product_id = p.productId WHERE oi.order_id = ?");
            $itemsStmt->bind_param("i", $order['id']);
            $itemsStmt->execute();
            $order['items'] = $itemsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $itemsStmt->close();
            
            $order['customer'] = ['username' => $order['username'], 'email' => $order['email']];
            unset($order['username'], $order['email']);
        }
        
        return $order;
    }

    public function getAllOrders() {
        $orders = [];
        $stmt = $this->db->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($order = $result->fetch_assoc()) {
            $itemsStmt = $this->db->prepare("SELECT p.name, oi.quantity, oi.price, p.image_path FROM order_items oi JOIN products p ON oi.product_id = p.productId WHERE oi.order_id = ?");
            $itemsStmt->bind_param("i", $order['id']);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();
            $order['items'] = $itemsResult->fetch_all(MYSQLI_ASSOC);
            $itemsStmt->close();
            $orders[] = $order;
        }
        
        $stmt->close();
        return $orders;
    }
}
