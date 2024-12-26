<?php
class Order {
    private $db;

    public function __construct() {
        $config = include(__DIR__ . '/../config/config.php');
        $db_config = $config['db'];
        $this->db = new mysqli(
            $db_config['host'],
            $db_config['username'],
            $db_config['password'],
            $db_config['database']
        );
    }

    public function createOrder($userId, $orderNumber, $totalAmount, $shippingAddress, $items, $shippingCost = 0, $shippingMethod = '', $discountAmount = 0) {
        $this->db->begin_transaction();

        try {
            // Insert order with shipping and discount info
            $stmt = $this->db->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_address, shipping_cost, shipping_method, discount_amount) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isdssdd", $userId, $orderNumber, $totalAmount, $shippingAddress, $shippingCost, $shippingMethod, $discountAmount);
            $stmt->execute();
            $orderId = $this->db->insert_id;

            // Insert order items and update product quantities
            $stmt = $this->db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $updateStmt = $this->db->prepare("UPDATE products SET quantity = quantity - ?, sold_count = sold_count + ? WHERE productId = ?");
            
            foreach ($items as $item) {
                // Insert order item
                $stmt->bind_param("iiid", $orderId, $item['productId'], $item['quantity'], $item['price']);
                $stmt->execute();

                // Update product quantity and sold count
                $updateStmt->bind_param("iii", $item['quantity'], $item['quantity'], $item['productId']);
                $updateStmt->execute();

                if ($updateStmt->affected_rows === 0) {
                    throw new Exception('Failed to update product quantity');
                }
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
        return $result->fetch_assoc();
    }

    public function updatePaymentStatus($orderNumber, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET payment_status = ? WHERE order_number = ?");
        $stmt->bind_param("ss", $status, $orderNumber);
        return $stmt->execute();
    }

    public function getUserOrders($userId) {
        $orders = [];
        $stmt = $this->db->prepare("
            SELECT o.*, oi.product_id, oi.quantity, oi.price as item_price, 
                   p.name, p.image_path 
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.productId
            WHERE o.user_id = ?
            ORDER BY o.created_at DESC
        ");
        
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $currentOrder = null;
        while ($row = $result->fetch_assoc()) {
            if ($currentOrder === null || $currentOrder['order_number'] !== $row['order_number']) {
                if ($currentOrder !== null) {
                    $orders[] = $currentOrder;
                }
                $currentOrder = [
                    'id' => $row['id'],
                    'order_number' => $row['order_number'],
                    'total_amount' => $row['total_amount'],
                    'status' => 'success',
                    'created_at' => $row['created_at'],
                    'items' => []
                ];
            }
            
            if ($row['product_id']) {
                $currentOrder['items'][] = [
                    'id' => $row['product_id'],
                    'name' => $row['name'],
                    'quantity' => $row['quantity'],
                    'price' => $row['item_price'],
                    'image_path' => $row['image_path']
                ];
            }
        }
        
        if ($currentOrder !== null) {
            $orders[] = $currentOrder;
        }
        
        return $orders;
    }

    public function getOrderDetails($orderNumber) {
        $stmt = $this->db->prepare("
            SELECT o.*, oi.quantity, oi.price as item_price, 
                   p.name, p.image_path, p.description,
                   u.username, u.email,
                   o.shipping_cost, o.shipping_method, o.discount_amount
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            LEFT JOIN products p ON oi.product_id = p.productId
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.order_number = ?
        ");
        
        $stmt->bind_param("s", $orderNumber);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orderDetails = null;
        while ($row = $result->fetch_assoc()) {
            if ($orderDetails === null) {
                $orderDetails = [
                    'order_number' => $row['order_number'],
                    'total_amount' => $row['total_amount'],
                    'shipping_address' => $row['shipping_address'],
                    'shipping_cost' => $row['shipping_cost'],
                    'shipping_method' => $row['shipping_method'],
                    'discount_amount' => $row['discount_amount'],
                    'created_at' => $row['created_at'],
                    'status' => 'success',
                    'customer' => [
                        'username' => $row['username'],
                        'email' => $row['email']
                    ],
                    'items' => []
                ];
            }
            
            if ($row['name']) {
                $orderDetails['items'][] = [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'quantity' => $row['quantity'],
                    'price' => $row['item_price'],
                    'image_path' => $row['image_path']
                ];
            }
        }
        
        return $orderDetails;
    }

    public function getAllOrders() {
        $stmt = $this->db->prepare("
            SELECT o.*, u.username, u.email,
                   GROUP_CONCAT(p.name) as product_names,
                   GROUP_CONCAT(oi.quantity) as quantities,
                   GROUP_CONCAT(oi.price) as prices,
                   GROUP_CONCAT(p.image_path) as image_paths
            FROM orders o
            JOIN users u ON o.user_id = u.id
            JOIN order_items oi ON o.id = oi.order_id
            JOIN products p ON oi.product_id = p.productId
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        $orders = [];
        
        while ($row = $result->fetch_assoc()) {
            $names = explode(',', $row['product_names']);
            $quantities = explode(',', $row['quantities']);
            $prices = explode(',', $row['prices']);
            $images = explode(',', $row['image_paths']);
            
            $items = [];
            for ($i = 0; $i < count($names); $i++) {
                $items[] = [
                    'name' => $names[$i],
                    'quantity' => $quantities[$i],
                    'price' => $prices[$i],
                    'image_path' => $images[$i]
                ];
            }
            
            $row['items'] = $items;
            unset($row['product_names'], $row['quantities'], $row['prices'], $row['image_paths']);
            $orders[] = $row;
        }
        
        return $orders;
    }
}
