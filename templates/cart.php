
<?php
require_once __DIR__ . '/../includes/Database.php';

class Cart {
    private $db;
    private static $cache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getCartItems($userId) {
        $cacheKey = "cart_$userId";
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $stmt = $this->db->prepare(
            "SELECT c.*, p.name, p.price, p.image_path 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.user_id = ?"
        );
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        self::$cache[$cacheKey] = $result;
        return $result;
    }

    public function updateQuantity($userId, $productId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($userId, $productId);
        }

        $stmt = $this->db->prepare(
            "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?"
        );
        $stmt->bind_param("iii", $quantity, $userId, $productId);
        $success = $stmt->execute();
        
        unset(self::$cache["cart_$userId"]);
        return $success;
    }
}

$userId = $_SESSION['userId'] ?? null;
if (!$userId) {
    header('Location: /login');
    exit();
}

$cart = new Cart();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantity'])) {
    foreach ($_POST['quantity'] as $productId => $quantity) {
        $cart->updateQuantity($userId, $productId, (int)$quantity);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$cartItems = $cart->getCartItems($userId);
$totalPrice = array_reduce($cartItems, function($carry, $item) {
    return $carry + ($item['price'] * $item['quantity']);
}, 0);
?>
<link rel='stylesheet' href='/assets/css/cart.css' />
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-XvNfPDRV6aHEMfWG"></script>

<div class="breadcrumb">
    <a href="#">Home</a> / <span>Cart List</span>
</div>
<div class='container'>
    <form method="post" action="">
        <button class="btn" type="button" onclick="window.location.href='/'">Return To Shop</button>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($cartItems)): ?>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <div class="product-info">
                                    <img src="/<?php echo htmlspecialchars($item['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>" />
                                    <div>
                                        <p><?php echo htmlspecialchars($item['name']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td>
                                <input type="number" 
                                    class="quantity-input"
                                    data-price="<?php echo $item['price']; ?>"
                                    data-product="<?php echo htmlspecialchars($item['product_id']); ?>" 
                                    value="<?php echo $item['quantity']; ?>" 
                                    min="0" />
                            </td>
                            <td class="subtotal">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php $totalPrice += $item['price'] * $item['quantity']; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Your cart is empty.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
    <div class="coupon">
        <input type="text" placeholder="Coupon Code" />
        <button type="button">Apply Coupon</button>
    </div>
    <div class="cart-total">
        <h3>Cart Total</h3>
        <p><span>Subtotal:</span> <span>Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span></p>
        <p><span>Shipping:</span> <span>Free</span></p>
        <p><span>Total:</span> <span>Rp <?php echo number_format($totalPrice, 0, ',', '.'); ?></span></p>
        <button class="btn" type="button" id="pay-button">Process to Payment</button>
    </div>
</div>

<script type="text/javascript">
document.getElementById('pay-button').onclick = function() {
    fetch('process_payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            amount: <?php echo $totalPrice; ?>,
            user_id: <?php echo $userId; ?>
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.token) {
            snap.pay(data.token, {
                onSuccess: function(result) {
                    window.location.href = 'payment_success.php?order_id=' + result.order_id;
                },
                onPending: function(result) {
                    window.location.href = 'payment_pending.php?order_id=' + result.order_id;
                },
                onError: function(result) {
                    alert('Payment failed: ' + result.status_message);
                }
            });
        } else {
            alert('Error: ' + data.error);
        }
    });
};

document.addEventListener('DOMContentLoaded', function() {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const cartTotal = document.querySelector('.cart-total');
    
    quantityInputs.forEach(input => {
        input.addEventListener('change', debounce(function(e) {
            const productId = this.dataset.product;
            const quantity = this.value;
            const price = parseFloat(this.dataset.price);
            const subtotalCell = this.closest('tr').querySelector('.subtotal');
            
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update subtotal for this row
                    const subtotal = price * quantity;
                    subtotalCell.textContent = 'Rp ' + numberFormat(subtotal);
                    
                    // Update cart total
                    updateCartTotal();
                    
                    // Remove row if quantity is 0
                    if (quantity <= 0) {
                        this.closest('tr').remove();
                    }
                } else {
                    alert('Error updating cart');
                }
            });
        }, 500));
    });

    function updateCartTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(cell => {
            const value = parseInt(cell.textContent.replace(/[^0-9]/g, ''));
            total += value;
        });
        document.querySelector('.cart-total p:first-child span:last-child').textContent = 'Rp ' + numberFormat(total);
        document.querySelector('.cart-total p:last-child span:last-child').textContent = 'Rp ' + numberFormat(total);
    }

    function numberFormat(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    // Debounce function to prevent too many requests
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});
</script>