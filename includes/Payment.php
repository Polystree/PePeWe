<?php

class Payment {
    private static $instance = null;
    private $db;

    private function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance();
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Payment();
        }
        return self::$instance;
    }

    private function sanitizeEmail($email) {
        $email = trim($email ?? '');
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'customer@example.com';
    }

    private function sanitizeCustomerName($name) {
        $name = trim($name ?? '');
        return $name ?: 'Customer';
    }

    public function createMidtransPayment($userId, $amount, $items, $shippingMethod = '', $shippingCost = 0) {
        $config = require __DIR__ . '/../config/midtrans_config.php';
        $stmt = $this->db->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $customer = $stmt->get_result()->fetch_assoc();
        $orderId = 'ORDER-' . time() . '-' . $userId;
        
        $itemDetails = array_map(function($item) {
            return [
                'id' => $item['productId'],
                'price' => (int)$item['price'],
                'quantity' => (int)$item['quantity'],
                'name' => substr($item['product_name'] ?? 'Product', 0, 50)
            ];
        }, $items);

        if ($shippingCost > 0 && $shippingMethod) {
            $itemDetails[] = [
                'id' => 'SHIPPING',
                'price' => (int)$shippingCost,
                'quantity' => 1,
                'name' => 'Shipping - ' . $shippingMethod
            ];
        }

        $customerDetails = [
            'first_name' => $this->sanitizeCustomerName($customer['username'] ?? $_SESSION['username']),
            'email' => $this->sanitizeEmail($customer['email'] ?? $_SESSION['email'])
        ];

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int)$amount
            ],
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails,
            'enable_payments' => [
                'credit_card', 'gopay', 'shopeepay',
                'bca_va', 'bni_va', 'bri_va'
            ],
            'credit_card' => ['secure' => true]
        ];

        \Midtrans\Config::$serverKey = $config['server_key'];
        \Midtrans\Config::$isProduction = $config['is_production'];
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            if (empty($snapToken)) {
                throw new Exception('Failed to generate Midtrans token');
            }

            $this->createTransaction($userId, $amount, 'midtrans', $orderId, 'pending');

            return [
                'token' => $snapToken,
                'order_id' => $orderId
            ];
        } catch (\Exception $e) {
            error_log('Midtrans Error: ' . $e->getMessage());
            throw new Exception('Midtrans payment creation failed: ' . $e->getMessage());
        }
    }

    public function createTransaction($userId, $amount, $paymentMethod = 'bank_transfer') {
        try {
            $orderId = 'ORDER-' . time();
            
            $stmt = $this->db->prepare("SELECT username, email FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            $vaNumber = null;
            if ($paymentMethod === 'bank_transfer') {
                $vaNumber = '88' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
            }

            $stmt = $this->db->prepare(
                "INSERT INTO orders (order_id, user_id, amount, status, payment_type, va_number) 
                 VALUES (?, ?, ?, 'pending', ?, ?)"
            );
            $stmt->bind_param("sidss", $orderId, $userId, $amount, $paymentMethod, $vaNumber);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to save order');
            }

            return [
                'success' => true,
                'order_id' => $orderId,
                'va_number' => $vaNumber,
                'amount' => $amount
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
