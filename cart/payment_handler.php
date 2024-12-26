<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Create a log function
function logError($message, $context = []) {
    error_log(sprintf(
        "[Payment Error] %s | Context: %s", 
        $message, 
        json_encode($context)
    ));
}

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/Cart.php';
require_once __DIR__ . '/../includes/Order.php';

try {
    $input = file_get_contents('php://input');
    if (!$input) {
        throw new Exception('No input received');
    }

    $decoded = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    if (!isset($_SESSION['userId']) || !isset($decoded['amount']) || 
        !isset($decoded['shipping_cost']) || !isset($decoded['shipping_service']) || 
        !isset($decoded['discount_amount'])) {
        throw new Exception('Missing required parameters');
    }

    $midtransConfig = require_once __DIR__ . '/../config/midtrans_config.php';

    $config = require_once __DIR__ . '/../config/config.php';
    $db_config = $config['db'];
    $connect = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

    if ($connect->connect_error) {
        throw new Exception('Database connection failed');
    }

    $stmt = $connect->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['userId']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!isset($user) || !$user) {
        throw new Exception('User not found');
    }

    $stmt = $connect->prepare("SELECT a.* FROM user_addresses a WHERE a.user_id = ? AND a.is_default = 1");
    $stmt->bind_param("i", $_SESSION['userId']);
    $stmt->execute();
    $result = $stmt->get_result();
    $address = $result->fetch_assoc();

    if (!isset($address) || !$address) {
        throw new Exception('No default shipping address found');
    }

    $shippingAddress = $address ? 
        $address['recipient_name'] . ' - ' . 
        $address['phone'] . ' | ' . 
        $address['address'] . ', ' . 
        $address['city'] . ' ' . 
        $address['postal_code']
        : 'No address provided';

    \Midtrans\Config::$serverKey = $midtransConfig['server_key'];
    \Midtrans\Config::$isProduction = $midtransConfig['is_production'];
    \Midtrans\Config::$isSanitized = true;
    \Midtrans\Config::$is3ds = true;

    $userId = $_SESSION['userId'];
    $orderId = 'ORDER-' . time() . '-' . $userId;
    $amount = (int)$decoded['amount'];
    $shippingCost = (int)$decoded['shipping_cost'];
    $shippingService = $decoded['shipping_service'];
    $discountAmount = (int)$decoded['discount_amount'];

    $cart = new Cart();
    $cartItems = $cart->getCartItems($userId);

    if (empty($cartItems)) {
        throw new Exception('Cart is empty');
    }

    $order = new Order();
    $orderNumber = 'ORD-' . time() . '-' . $_SESSION['userId'];
    
    $orderId = $order->createOrder(
        $_SESSION['userId'],
        $orderNumber,
        $amount,
        $shippingAddress,
        $cartItems,
        $shippingCost,
        $shippingService,
        $discountAmount
    );

    $items = [];
    foreach ($cartItems as $item) {
        $items[] = [
            'id' => $item['productId'],
            'price' => $item['price'],
            'quantity' => $item['quantity'],
            'name' => $item['product_name']
        ];
    }

    // Add shipping cost as an item if present
    if ($shippingCost > 0) {
        $items[] = [
            'id' => 'SHIPPING',
            'price' => $shippingCost,
            'quantity' => 1,
            'name' => 'Shipping Cost (' . $shippingService . ')'
        ];
    }

    // Add discount as a negative item if present
    if ($discountAmount > 0) {
        $items[] = [
            'id' => 'DISCOUNT',
            'price' => -$discountAmount,
            'quantity' => 1,
            'name' => 'Discount'
        ];
    }

    $transaction = [
        'transaction_details' => [
            'order_id' => $orderNumber,
            'gross_amount' => $amount // This should now match items total
        ],
        'customer_details' => [
            'first_name' => $user['username'] ?? 'Customer',
            'email' => $user['email'] ?? 'customer@example.com',
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

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($transaction);
        if (!$snapToken) {
            throw new Exception('Empty token received from Midtrans');
        }
        
        $response = [
            'success' => true,
            'token' => $snapToken,
            'order_id' => $orderNumber
        ];
        
        echo json_encode($response);
        exit;
        
    } catch (Exception $e) {
        logError('Midtrans token error', [
            'error' => $e->getMessage(),
            'transaction' => $transaction
        ]);
        throw new Exception('Payment gateway error: ' . $e->getMessage());
    }

} catch (Exception $e) {
    logError($e->getMessage(), [
        'input' => $input ?? null,
        'userId' => $_SESSION['userId'] ?? null
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'detail' => 'An error occurred while processing your request'
    ], JSON_PARTIAL_OUTPUT_ON_ERROR);
    exit;
}
