<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(0);
session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/Cart.php';
require_once __DIR__ . '/../includes/Order.php';

try {
    $input = file_get_contents('php://input');
    $decoded = json_decode($input, true);
    
    if (!$input || json_last_error() !== JSON_ERROR_NONE || 
        !isset($_SESSION['userId'], 
               $decoded['amount'],
               $decoded['shipping_cost'],
               $decoded['shipping_service'],
               $decoded['discount_amount'])) {
        exit(json_encode(['success' => false]));
    }

    $midtransConfig = require_once __DIR__ . '/../config/midtrans_config.php';
    $config = require_once __DIR__ . '/../config/config.php';
    
    $connect = new mysqli(
        $config['db']['host'], 
        $config['db']['username'], 
        $config['db']['password'], 
        $config['db']['database']
    );

    if ($connect->connect_error) {
        exit(json_encode(['success' => false]));
    }

    // Get user and address
    ($stmt = $connect->prepare("SELECT username, email FROM users WHERE id = ?"))->bind_param("i", $_SESSION['userId']); $stmt->execute();
    if (!($user = $stmt->get_result()->fetch_assoc())) exit(json_encode(['success' => false]));

    ($stmt = $connect->prepare("SELECT * FROM user_addresses WHERE user_id = ? AND is_default = 1"))->bind_param("i", $_SESSION['userId']); $stmt->execute();
    if (!($address = $stmt->get_result()->fetch_assoc())) exit(json_encode(['success' => false]));

    $shippingAddress = sprintf(
        "%s - %s | %s, %s %s",
        $address['recipient_name'],
        $address['phone'],
        $address['address'],
        $address['city'],
        $address['postal_code']
    );

    // Setup Midtrans
    \Midtrans\Config::$serverKey = $midtransConfig['server_key'];
    \Midtrans\Config::$isProduction = false;
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    // Process order
    $cart = new Cart();
    $cartItems = $cart->getCartItems($_SESSION['userId']);
    if (empty($cartItems)) {
        exit(json_encode(['success' => false]));
    }

    $orderNumber = 'ORD-' . time() . '-' . $_SESSION['userId'];
    
    // Calculate total before creating order
    $total = 0;
    $items = [];
    
    foreach ($cartItems as $item) {
        $total += (int)$item['price'] * (int)$item['quantity'];
        $items[] = [
            'id' => $item['productId'],
            'price' => (int)$item['price'],
            'quantity' => (int)$item['quantity'],
            'name' => $item['product_name']
        ];
    }

    // Add shipping cost
    if ((int)$decoded['shipping_cost'] > 0) {
        $total += (int)$decoded['shipping_cost'];
        $items[] = [
            'id' => 'SHIPPING',
            'price' => (int)$decoded['shipping_cost'],
            'quantity' => 1,
            'name' => 'Shipping Cost (' . $decoded['shipping_service'] . ')'
        ];
    }

    // Subtract discount
    if ((int)$decoded['discount_amount'] > 0) {
        $total -= (int)$decoded['discount_amount'];
        $items[] = [
            'id' => 'DISCOUNT',
            'price' => -(int)$decoded['discount_amount'],
            'quantity' => 1,
            'name' => 'Discount'
        ];
    }

    // Create order after calculating final total
    $order = new Order();
    $order->createOrder(
        $_SESSION['userId'],
        $orderNumber,
        $total,
        $shippingAddress,
        $cartItems,
        (int)$decoded['shipping_cost'],
        $decoded['shipping_service'],
        (int)$decoded['discount_amount']
    );

    $transaction_data = [
        'transaction_details' => [
            'order_id' => $orderNumber,
            'gross_amount' => $total
        ],
        'customer_details' => [
            'first_name' => $user['username'],
            'email' => $user['email'],
            'shipping_address' => [
                'first_name' => $address['recipient_name'],
                'phone' => $address['phone'],
                'address' => $address['address'],
                'city' => $address['city'],
                'postal_code' => $address['postal_code']
            ]
        ],
        'item_details' => $items
    ];

    $snapToken = \Midtrans\Snap::getSnapToken($transaction_data);

    if (!$snapToken) {
        throw new Exception('Failed to get payment token');
    }

    echo json_encode([
        'success' => true,
        'token' => $snapToken,
        'order_id' => $orderNumber
    ]);

} catch (\Throwable $th) {
    echo json_encode(['success' => false]);
}
