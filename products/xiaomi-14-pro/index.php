<?php
session_start();

$config = include(__DIR__ . '/../../config/config.php');
$db_config = $config['db'];

$connect = new mysqli(
    $db_config['host'],
    $db_config['username'], 
    $db_config['password'],
    $db_config['database']
);

if ($connect->connect_error) {
    error_log("Database connection failed: " . $connect->connect_error);
    die("Connection failed. Please try again later.");
}

// Initialize product data
$product = [
    'name' => 'Xiaomi 14 Pro',
    'price' => 12999000,
    'image_path' => '/assets/img/product/1733487379-xiaomi-14-pro.jpg',
    'description' => 'Leica optics, Snapdragon 8 Gen 3, and 120W fast charging',
    'quantity' => 40
];

function getProductId($product_name, $connect) {
    $stmt = $connect->prepare('SELECT productId FROM products WHERE name = ? LIMIT 1');
    $stmt->bind_param('s', $product_name);
    $stmt->execute();
    $stmt->bind_result($productId);
    $stmt->fetch();
    $stmt->close();
    return $productId;
}

// Fetch full product details
$stmt = $connect->prepare('SELECT * FROM products WHERE name = ?');
$stmt->bind_param('s', $product['name']);
$stmt->execute();
$result = $stmt->get_result();
if ($prod = $result->fetch_assoc()) {
    $product = $prod;
}
$stmt->close();

// Handle review operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_review'])) {
        $userId = $_SESSION['userId'];
        $productId = getProductId('Xiaomi 14 Pro', $connect);
        $review = $_POST['review'];
        
        $stmt = $connect->prepare("INSERT INTO reviews (userId, productId, review) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $userId, $productId, $review);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['edit_review'])) {
        $reviewId = $_POST['review_id'];
        $review = $_POST['review'];
        
        $stmt = $connect->prepare("UPDATE reviews SET review = ? WHERE id = ? AND userId = ?");
        $stmt->bind_param("sii", $review, $reviewId, $_SESSION['userId']);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } elseif (isset($_POST['delete_review'])) {
        $reviewId = $_POST['review_id'];
        
        $stmt = $connect->prepare("DELETE FROM reviews WHERE id = ? AND userId = ?");
        $stmt->bind_param("ii", $reviewId, $_SESSION['userId']);
        $stmt->execute();
        $stmt->close();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// ...existing code for reviews...
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8' />
    <meta name='viewport' content='initial-scale=1, width=device-width' />
    <title>Xiaomi 14 Pro - Product Details - iniGadget</title>
    <link rel='icon' type='image/x-icon' href='/assets/img/logo-light.svg' />
    <link rel='stylesheet' href='/assets/css/style.css' />
    <link rel='stylesheet' href='/assets/css/product-details.css' />
    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap' />
    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap' />
</head>
<body>
    <?php include __DIR__ . '/../../templates/header.php'; ?>
    
    <div class='main-content'>
        <div class='product-details-container'>
            <div class='product-grid'>
                <div class='gallery-section'>
                    <div class='main-image'>
                        <img src='/assets/img/product/1733487379-xiaomi-14-pro.jpg' alt='Xiaomi 14 Pro Main Image' id='mainImage'>
                    </div>
                    <div class='thumbnail-grid'>
                        <div class='thumbnail active'>
                            <img src='/assets/img/product/1733487379-xiaomi-14-pro.jpg' alt='Xiaomi 14 Pro Thumbnail 1'>
                        </div>
                    </div>
                </div>

                <div class='product-info'>
                    <h1 class='product-title'><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class='price-section'>
                        <span class='current-price'>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                    </div>

                    <div class='description'>
                        <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        <span class='description-toggle'>Show More</span>
                    </div>

                    <?php if (isset($_SESSION['username']) && $_SESSION['username'] !== 'admin'): ?>
                    <form method='POST' action='/includes/add_to_cart.php' class='cart-form'>
                        <input type='hidden' name='productId' value='<?php echo $product["productId"]; ?>'>
                        
                        <div class='quantity-input'>
                            <label for='quantity'>Quantity</label>
                            <input type='number' name='quantity' id='quantity' value='1' min='1' required>
                        </div>
                        
                        <div class='total-price'>
                            <span class='total-price-label'>Total Price:</span>
                            <span class='total-price-amount'>Rp <?php echo number_format($product["price"], 0, ',', '.'); ?></span>
                        </div>
                        
                        <button type='submit' class='add-to-cart-btn'>Add to Cart</button>
                    </form>
                    <?php else: ?>
                    <div class='login-prompt'>
                        <p>Please <a href="/login">login</a> to add items to your cart.</p>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Reviews Section -->
            <div class='reviews-section'>
                <div class='reviews-header'>
                    <h2 class='reviews-title'>Customer Reviews</h2>
                </div>

                <?php
                // Fetch reviews for this product
                $product_name = 'Xiaomi 14 Pro';
                $productId = getProductId($product_name, $connect);
                $stmt = $connect->prepare('SELECT r.id, r.review, r.userId, u.username, u.profile_image 
                                           FROM reviews r 
                                           JOIN users u ON r.userId = u.id 
                                           WHERE r.productId = ?');
                $stmt->bind_param('i', $productId);
                $stmt->execute();
                $result = $stmt->get_result();
                $reviews = $result->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                // Check if user has already reviewed
                $userHasReviewed = false;
                if (isset($_SESSION['userId'])) {
                    foreach ($reviews as $review) {
                        if ($review['userId'] == $_SESSION['userId']) {
                            $userHasReviewed = true;
                            break;
                        }
                    }
                }
                ?>

                <?php foreach ($reviews as $review): ?>
                    <div class='review-item'>
                        <div class='review-header'>
                            <img src='<?php echo htmlspecialchars($review["profile_image"]); ?>' 
                                 alt='<?php echo htmlspecialchars($review["username"]); ?>' 
                                 class='reviewer-image'>
                            <span class='reviewer-name'><?php echo htmlspecialchars($review["username"]); ?></span>
                        </div>
                        <div class='review-content'>
                            <p><?php echo nl2br(htmlspecialchars($review["review"])); ?></p>
                            
                            <?php if (isset($_SESSION["userId"]) && $_SESSION["userId"] == $review["userId"]): ?>
                                <div class='review-actions'>
                                    <button type='button' onclick='toggleEditForm(this)' class='edit-review-btn'>Edit</button>
                                    <form method='POST' style='display: inline;'>
                                        <input type='hidden' name='review_id' value='<?php echo $review["id"]; ?>'>
                                        <button type='submit' name='delete_review' class='delete-review-btn'>Delete</button>
                                    </form>
                                </div>
                                <form method='POST' class='review-form'>
                                    <input type='hidden' name='review_id' value='<?php echo $review["id"]; ?>'>
                                    <textarea name='review' class='input-description'><?php echo htmlspecialchars($review["review"]); ?></textarea>
                                    <div class='review-actions'>
                                        <button type='submit' name='edit_review' class='edit-review-btn'>Save</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (isset($_SESSION["userId"]) && !$userHasReviewed): ?>
                    <form method='POST' class='review-form'>
                        <textarea name='review' placeholder='Write your review here...'></textarea>
                        <button type='submit' name='add_review' class='add-to-cart-btn'>Submit Review</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../templates/footer.php'; ?>

    <script>
        // Image gallery functionality
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('mainImage').src = this.querySelector('img').src;
            });
        });

        // Description toggle
        const description = document.querySelector('.description');
        const toggle = document.querySelector('.description-toggle');
        
        toggle.addEventListener('click', () => {
            description.classList.toggle('expanded');
            toggle.textContent = description.classList.contains('expanded') ? 'Show Less' : 'Show More';
        });

        // Dynamic price calculation
        const quantity = document.getElementById('quantity');
        const totalPrice = document.querySelector('.total-price-amount');
        const basePrice = <?php echo $product['price']; ?>;

        quantity.addEventListener('input', () => {
            const total = basePrice * quantity.value;
            totalPrice.textContent = `Rp ${total.toLocaleString('id-ID')}`;
        });
    </script>
</body>
</html>