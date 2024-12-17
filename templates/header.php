<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/Cart.php';
$db = Database::getInstance();
$userId = null;
$profileImage = '/assets/img/Generic avatar.svg';

if (isset($_SESSION['username'])) {
    try {
        $username = $db->real_escape_string($_SESSION['username']);
        $stmt = $db->prepare("SELECT id, profile_image FROM users WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $userData = $result->fetch_assoc()) {
                $_SESSION['userId'] = $userData['id'];
                if (!empty($userData['profile_image'])) {
                    $profileImage = $userData['profile_image'];
                }
                $userId = $userData['id'];
            } else {
                error_log("User {$username} not found in database");
                unset($_SESSION['username']);
            }
        }
    } catch (Exception $e) {
        error_log("Error fetching user data: " . $e->getMessage());
    }
}

$current_uri = $_SERVER['REQUEST_URI'];
$is_login_page = (strpos($current_uri, '/login') === 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'iniGadget'; ?></title>
    <link rel="icon" type="image/x-icon" href="/assets/img/logo-light.svg" />
    <link rel="stylesheet" href="/assets/css/header.css" />
</head>
<input type="checkbox" name="cart-switch" id="cart-switch">
<header>
    <div class="frame">
        <a class="logo" href="/"><img src="/assets/img/logo-landscape.svg" height="100%" alt="logo"></a>
        <input type="checkbox" name="profile" id="profile">
        <div class="header-right">

            <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin'): ?>
                <a href="/admin" class="header-item">Admin</a>
            <?php endif; ?>

            <?php if (!$is_login_page): ?>
            <div class="search-input">
                <form action="/index.php" method="GET" id="header-search">
                    <div class="search">
                        <input type="search" name="query" class="search-text" placeholder="What are you looking for?"
                            value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
                            maxlength="100" />
                        <button type="submit" class="search-button">
                            <img class="search-icon" alt="Search" src="/assets/img/Search.svg" />
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['username']) && $_SESSION['username'] !== 'admin'): ?>
                <div class="frame1">
                    <label for="cart-switch">
                        <img class="cart-icon" alt="" src="/assets/img/cart.svg" />
                    </label>
                </div>
            <?php endif; ?>

            <?php if (!$is_login_page): ?>
                <?php
                echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
                ?>
            <?php endif; ?>

            <label for="profile">
                <img class="generic-avatar-icon" alt="Profile Image"
                    src="<?php echo htmlspecialchars($profileImage); ?>" />
            </label>

            <div class="profile-dropdown">
                <?php if (!isset($_SESSION['username'])): ?>
                    <a href="/login" class="profile-item" id="profile-login">Login / Register</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="/account" class="profile-item" id="my-account">My Account</a>
                    <a href="/login/logout.php" class="profile-item" id="profile-login">Logout</a>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</header>

<div class="cart-overlay"></div>
<div class="cart-sidebar">
    <h2>
        Shopping Cart
        <span class="close-cart" onclick="document.getElementById('cart-switch').checked = false">&times;</span>
    </h2>
    <div id="cart-items">
        <?php
        if (isset($_SESSION['username'])) {
            $cart = new Cart();
            $cartItems = $cart->getCartItems($_SESSION['userId']);
            
            if (!empty($cartItems)) {
                foreach ($cartItems as $item) {
                    echo '<div class="cart-item">';
                    echo '<div class="cart-item-header">';
                    echo '<img src="/' . htmlspecialchars($item['image_path'] ?? '') . '" 
                               alt="' . htmlspecialchars($item['product_name'] ?? 'Product') . '" />';
                    echo '<div class="cart-item-details">';
                    echo '<h4>' . htmlspecialchars($item['product_name'] ?? 'Product') . '</h4>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="cart-item-price-row">';
                    echo '<span>Rp ' . number_format((float)($item['price'] ?? 0), 0, ',', '.') . ' × </span>';
                    echo '<input type="number" class="cart-quantity" 
                            data-product-id="' . $item['productId'] . '" 
                            value="' . ((int)$item['quantity'] ?? 1) . '" min="1">';
                    echo '<span class="item-total">= Rp ' . 
                         number_format((float)($item['price'] ?? 0) * ((int)$item['quantity'] ?? 1), 0, ',', '.') . 
                         '</span>';
                    echo '<button class="delete-cart-item" 
                            data-product-id="' . $item['productId'] . '">&times;</button>';
                    echo '</div>';
                    echo '</div>';
                }
                echo '<a href="/cart/" class="view-cart-btn">View Cart</a>';
            } else {
                echo '<p>Your cart is empty</p>';
            }
        } else {
            echo '<p>Please login to view your cart</p>';
        }
        ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.querySelector('.cart-overlay');

    overlay.addEventListener('click', function() {
        document.getElementById('cart-switch').checked = false;
    });

    document.querySelector('.cart-sidebar').addEventListener('click', function(e) {
        e.stopPropagation();
    });

    document.querySelectorAll('.cart-quantity').forEach(input => {
        input.addEventListener('change', function() {
            const productId = this.dataset.productId;
            const quantity = this.value;
            
            fetch('/includes/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    productId: productId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });

    document.querySelectorAll('.delete-cart-item').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            fetch('/includes/update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    productId: productId,
                    quantity: 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.closest('.cart-item').remove();
                    if (document.querySelectorAll('.cart-item').length === 0) {
                        document.getElementById('cart-items').innerHTML = '<p>Your cart is empty</p>';
                    }
                }
            });
        });
    });
});
</script>
</body>
</html>
