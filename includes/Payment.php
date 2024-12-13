
<?php
require_once __DIR__ . '/../config/midtrans_config.php';
require_once __DIR__ . '/Database.php';

class Payment {
    private $db;
    private static $instance = null;

    private function __construct() {
        $this->db = Database::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function createTransaction($userId, $amount) {
        try {
            $orderId = 'ORDER-' . time();
            
            // Get user details
            $stmt = $this->db->prepare("SELECT username, email FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            // Create transaction details
            $transaction = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $amount
                ],
                'customer_details' => [
                    'first_name' => $user['username'],
                    'email' => $user['email']
                ]
            ];

            // Get Midtrans token
            $snapToken = \Midtrans\Snap::getSnapToken($transaction);

            // Save order to database
            $stmt = $this->db->prepare(
                "INSERT INTO orders (order_id, user_id, amount, status, payment_type) 
                 VALUES (?, ?, ?, 'pending', '')"
            );
            $stmt->bind_param("sid", $orderId, $userId, $amount);
            $stmt->execute();

            return [
                'success' => true,
                'token' => $snapToken,
                'order_id' => $orderId
            ];

        } catch (\Exception $e) {
            error_log("Payment error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getOrderStatus($orderId) {
        $stmt = $this->db->prepare(
            "SELECT o.*, u.username, u.email 
             FROM orders o 
             JOIN users u ON o.user_id = u.id 
             WHERE o.order_id = ?"
        );
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updateOrderStatus($orderId, $status, $paymentType = '') {
        $stmt = $this->db->prepare(
            "UPDATE orders 
             SET status = ?, payment_type = ?, updated_at = CURRENT_TIMESTAMP 
             WHERE order_id = ?"
        );
        $stmt->bind_param("sss", $status, $paymentType, $orderId);
        return $stmt->execute();
    }
}