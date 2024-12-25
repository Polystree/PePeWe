<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/Payment.php';
require_once __DIR__ . '/../includes/Cart.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Not logged in']));
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    throw new Exception('Invalid request data');
}

$amount = $input['amount'] ?? 0;
if ($amount <= 0) {
    throw new Exception('Invalid amount');
}

$cart = new Cart();
$cartItems = $cart->getCartItems($_SESSION['userId']);
if (empty($cartItems)) {
    throw new Exception('Cart is empty');
}

$items = array_map(function($item) {
    return [
        'id' => $item['productId'],
        'price' => $item['price'],
        'quantity' => $item['quantity'],
        'name' => $item['product_name']
    ];
}, $cartItems);

if ($input['shipping_cost'] > 0) {
    $items[] = [
        'id' => 'SHIPPING',
        'price' => $input['shipping_cost'],
        'quantity' => 1,
        'name' => 'Shipping Cost (' . $input['shipping_method'] . ')'
    ];
}

$payment = Payment::getInstance();
$orderId = 'ORD-' . time() . '-' . $_SESSION['userId'];

$transactionDetails = [
    'order_id' => $orderId,
    'gross_amount' => $amount,
    'shipping_address' => [
        'first_name' => $input['recipient_name'],
        'address' => $input['address'],
        'city' => $input['city'],
        'postal_code' => $input['postal_code'],
        'phone' => $input['phone']
    ],
    'item_details' => $items
];

$result = $payment->createMidtransPayment(
    $_SESSION['userId'],
    $transactionDetails,
    $input['shipping_method'],
    $input['shipping_cost']
);

echo json_encode([
    'success' => true,
    'token' => $result['token'],
    'order_id' => $result['order_id']
]);
