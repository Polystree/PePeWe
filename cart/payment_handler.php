<?php
header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/Cart.php';
require_once __DIR__ . '/../includes/Order.php';

try {
    $midtransConfig = require_once __DIR__ . '/../config/midtrans_config.php';
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($_SESSION['userId']) || !isset($input['amount'])) {
        throw new Exception('Invalid request parameters');
    }

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

    $stmt = $connect->prepare("SELECT a.* FROM user_addresses a WHERE a.user_id = ? AND a.is_default = 1");
    $stmt->bind_param("i", $_SESSION['userId']);
    $stmt->execute();
    $result = $stmt->get_result();
    $address = $result->fetch_assoc();

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
    $amount = (int)$input['amount'];

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
        $cartItems
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

    $transaction = [
        'transaction_details' => [
            'order_id' => $orderNumber,
            'gross_amount' => $amount
        ],
        'customer_details' => [
            'first_name' => $user['username'] ?? 'Customer',
            'email' => $user['email'] ?? 'customer@example.com'
        ],
        'item_details' => $items
    ];

    try {
        $snapToken = \Midtrans\Snap::getSnapToken($transaction);
        echo json_encode(['token' => $snapToken, 'order_id' => $orderNumber]);
    } catch (Exception $e) {
        throw new Exception('Failed to get Midtrans token: ' . $e->getMessage());
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'detail' => 'An error occurred while processing your request'
    ]);
}
