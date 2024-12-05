<?php
session_start();

// Get database connection using correct config keys
$config = include(__DIR__ . '/../config/config.php');
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

// Add this after the database connection code
$product_image_dir = __DIR__ . '/../assets/img/product';
if (!is_dir($product_image_dir)) {
    mkdir($product_image_dir, 0777, true);
}

// Strict admin check with redirect
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header('Location: /login');
    exit();
}

$product = [
    'name' => '',
    'price' => '',
    'image_path' => '',
    'description' => '',
    'quantity' => ''
];
$productId = null;

if (isset($_GET['productId'])) {
    $productId = (int)$_GET['productId'];
    $stmt = $connect->prepare("SELECT name, price, image_path, description, quantity FROM products WHERE productId = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $stmt->bind_result($product['name'], $product['price'], $product['image_path'], $product['description'], $product['quantity']);
    $stmt->fetch();
    $stmt->close();
}

// Modify the product page generation function
function generateProductPage($name, $price, $image_path, $description, $quantity, $productId) {
    $price_clean = (float)str_replace('.', '', $price);
    $image_path = str_replace('../', '/', $image_path);
    
    $productPageContent = <<<PHP
<?php
session_start();

\$config = include(__DIR__ . '/../../config/config.php');
\$db_config = \$config['db'];

\$connect = new mysqli(
    \$db_config['host'],
    \$db_config['username'], 
    \$db_config['password'],
    \$db_config['database']
);

if (\$connect->connect_error) {
    error_log("Database connection failed: " . \$connect->connect_error);
    die("Connection failed. Please try again later.");
}

// Initialize product data
\$product = [
    'name' => '$name',
    'price' => $price_clean,
    'image_path' => '$image_path',
    'description' => '$description',
    'quantity' => $quantity
];

function getProductId(\$product_name, \$connect) {
    \$stmt = \$connect->prepare('SELECT productId FROM products WHERE name = ? LIMIT 1');
    \$stmt->bind_param('s', \$product_name);
    \$stmt->execute();
    \$stmt->bind_result(\$productId);
    \$stmt->fetch();
    \$stmt->close();
    return \$productId;
}

// Fetch full product details
\$stmt = \$connect->prepare('SELECT * FROM products WHERE name = ?');
\$stmt->bind_param('s', \$product['name']);
\$stmt->execute();
\$result = \$stmt->get_result();
if (\$prod = \$result->fetch_assoc()) {
    \$product = \$prod;
}
\$stmt->close();

// Handle review operations
if (\$_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset(\$_POST['add_review'])) {
        \$userId = \$_SESSION['userId'];
        \$productId = getProductId('$name', \$connect);
        \$review = \$_POST['review'];
        
        \$stmt = \$connect->prepare("INSERT INTO reviews (userId, productId, review) VALUES (?, ?, ?)");
        \$stmt->bind_param("iis", \$userId, \$productId, \$review);
        \$stmt->execute();
        \$stmt->close();
        header("Location: " . \$_SERVER['PHP_SELF']);
        exit();
    } elseif (isset(\$_POST['edit_review'])) {
        \$reviewId = \$_POST['review_id'];
        \$review = \$_POST['review'];
        
        \$stmt = \$connect->prepare("UPDATE reviews SET review = ? WHERE id = ? AND userId = ?");
        \$stmt->bind_param("sii", \$review, \$reviewId, \$_SESSION['userId']);
        \$stmt->execute();
        \$stmt->close();
        header("Location: " . \$_SERVER['PHP_SELF']);
        exit();
    } elseif (isset(\$_POST['delete_review'])) {
        \$reviewId = \$_POST['review_id'];
        
        \$stmt = \$connect->prepare("DELETE FROM reviews WHERE id = ? AND userId = ?");
        \$stmt->bind_param("ii", \$reviewId, \$_SESSION['userId']);
        \$stmt->execute();
        \$stmt->close();
        header("Location: " . \$_SERVER['PHP_SELF']);
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
    <title>{$name} - Product Details - iniGadget</title>
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
                        <img src='{$image_path}' alt='{$name} Main Image' id='mainImage'>
                    </div>
                    <div class='thumbnail-grid'>
                        <div class='thumbnail active'>
                            <img src='{$image_path}' alt='{$name} Thumbnail 1'>
                        </div>
                    </div>
                </div>

                <div class='product-info'>
                    <h1 class='product-title'><?php echo htmlspecialchars(\$product['name']); ?></h1>
                    
                    <div class='price-section'>
                        <span class='current-price'>Rp <?php echo number_format(\$product['price'], 0, ',', '.'); ?></span>
                    </div>

                    <div class='description'>
                        <p><?php echo nl2br(htmlspecialchars(\$product['description'])); ?></p>
                        <span class='description-toggle'>Show More</span>
                    </div>

                    <form method='POST' class='cart-form'>
                        <input type='hidden' name='product_name' value='<?php echo htmlspecialchars(\$product["name"]); ?>'>
                        <input type='hidden' name='price' value='<?php echo \$product["price"]; ?>'>
                        <input type='hidden' name='image_path' value='<?php echo htmlspecialchars(\$product["image_path"]); ?>'>
                        
                        <div class='quantity-input'>
                            <label for='quantity'>Quantity</label>
                            <input type='number' name='quantity' id='quantity' value='1' min='1' required>
                        </div>
                        
                        <div class='total-price'>
                            <span class='total-price-label'>Total Price:</span>
                            <span class='total-price-amount'>Rp <?php echo number_format(\$product["price"], 0, ',', '.'); ?></span>
                        </div>
                        
                        <button type='submit' class='add-to-cart-btn' name='cart'>Add to Cart</button>
                    </form>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class='reviews-section'>
                <div class='reviews-header'>
                    <h2 class='reviews-title'>Customer Reviews</h2>
                </div>

                <?php
                // Fetch reviews for this product
                \$product_name = '{$name}';
                \$productId = getProductId(\$product_name, \$connect);
                \$stmt = \$connect->prepare('SELECT r.id, r.review, r.userId, u.username, u.profile_image 
                                           FROM reviews r 
                                           JOIN users u ON r.userId = u.id 
                                           WHERE r.productId = ?');
                \$stmt->bind_param('i', \$productId);
                \$stmt->execute();
                \$result = \$stmt->get_result();
                \$reviews = \$result->fetch_all(MYSQLI_ASSOC);
                \$stmt->close();

                // Check if user has already reviewed
                \$userHasReviewed = false;
                if (isset(\$_SESSION['userId'])) {
                    foreach (\$reviews as \$review) {
                        if (\$review['userId'] == \$_SESSION['userId']) {
                            \$userHasReviewed = true;
                            break;
                        }
                    }
                }
                ?>

                <?php foreach (\$reviews as \$review): ?>
                    <div class='review-item'>
                        <div class='review-header'>
                            <img src='<?php echo htmlspecialchars(\$review["profile_image"]); ?>' 
                                 alt='<?php echo htmlspecialchars(\$review["username"]); ?>' 
                                 class='reviewer-image'>
                            <span class='reviewer-name'><?php echo htmlspecialchars(\$review["username"]); ?></span>
                        </div>
                        <div class='review-content'>
                            <p><?php echo nl2br(htmlspecialchars(\$review["review"])); ?></p>
                            
                            <?php if (isset(\$_SESSION["userId"]) && \$_SESSION["userId"] == \$review["userId"]): ?>
                                <form method='POST' class='review-form'>
                                    <input type='hidden' name='review_id' value='<?php echo \$review["id"]; ?>'>
                                    <textarea name='review' class='input-description'><?php echo htmlspecialchars(\$review["review"]); ?></textarea>
                                    <div class='review-actions'>
                                        <button type='submit' name='edit_review' class='edit-review-btn'>Edit</button>
                                        <button type='submit' name='delete_review' class='delete-review-btn'>Delete</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (isset(\$_SESSION["userId"]) && !\$userHasReviewed): ?>
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
        const basePrice = <?php echo \$product['price']; ?>;

        quantity.addEventListener('input', () => {
            const total = basePrice * quantity.value;
            totalPrice.textContent = `Rp \${total.toLocaleString('id-ID')}`;
        });
    </script>
</body>
</html>
PHP;

    // Create directory and file with new structure
    $dirName = __DIR__ . '/../products/' . strtolower(str_replace(' ', '-', $name));
    if (!is_dir($dirName)) {
        mkdir($dirName, 0777, true);
    }
    $fileName = $dirName . '/index.php';
    file_put_contents($fileName, $productPageContent);
    return $fileName;
}

// Modify the POST handling section
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $price = str_replace('.', '', $_POST['price']); // Remove dots from price
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_dir = __DIR__ . '/../assets/img/product/';
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $random_number = mt_rand(1000, 9999);
        $safe_product_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $image_filename = $random_number . '-' . $safe_product_name . '.' . $file_extension;
        $image_path = '../assets/img/product/' . $image_filename;
        $upload_path = $image_dir . $image_filename;

        // Delete old image if exists in edit mode
        if (isset($productId) && !empty($product['image_path'])) {
            $old_image = __DIR__ . '/..' . $product['image_path'];
            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            // Image uploaded successfully
        } else {
            error_log("Failed to move uploaded file to $upload_path");
            die("Failed to upload image. Please try again.");
        }
    } else {
        $image_path = $product['image_path'];
    }

    $url = generateProductPage($name, $price, $image_path, $description, $quantity, $productId);

    if (isset($_POST['productId'])) {
        $productId = (int)$_POST['productId'];
        $stmt = $connect->prepare("UPDATE products SET name = ?, price = ?, image_path = ?, description = ?, quantity = ?, url = ? WHERE productId = ?");
        $stmt->bind_param("sissisi", $name, $price, $image_path, $description, $quantity, $url, $productId);
    } else {
        $stmt = $connect->prepare("INSERT INTO products (name, price, image_path, description, quantity, url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissis", $name, $price, $image_path, $description, $quantity, $url);
    }
    $stmt->execute();
    $stmt->close();
    header('Location: index.php');
    exit();
}

$connect->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title><?php echo isset($productId) ? 'Edit Product' : 'Add New Product'; ?> - iniGadget</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo-light.svg" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/add-product.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" />
</head>

<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class="main-content">
        <div class="product-form-container">
            <h1 class="page-title"><?php echo isset($productId) ? 'Edit Product' : 'Add New Product'; ?></h1>
            
            <form method="POST" class="product-form" enctype="multipart/form-data">
                <?php if (isset($productId)): ?>
                    <input type="hidden" name="productId" value="<?php echo htmlspecialchars($productId); ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <!-- Image Preview Section -->
                    <div class="image-section">
                        <div class="image-preview" id="imagePreview">
                            <?php if (!empty($product['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="Product preview">
                            <?php else: ?>
                                <div class="placeholder">
                                    <i class="fas fa-image"></i>
                                    <span>Preview Image</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <input type="file" name="image" id="image" accept="image/*" class="file-input" 
                                   <?php echo isset($productId) ? '' : 'required'; ?>>
                            <label for="image" class="btn-upload">Choose Image</label>
                        </div>
                    </div>

                    <!-- Product Details Section -->
                    <div class="details-section">
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" name="name" id="name" 
                                   value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="price">Price (Rp)</label>
                                <input type="text" name="price" id="price" 
                                       value="<?php echo htmlspecialchars($product['price']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="quantity">Stock Quantity</label>
                                <input type="number" name="quantity" id="quantity" 
                                       value="<?php echo htmlspecialchars($product['quantity']); ?>" min="1" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="6" required><?php 
                                echo htmlspecialchars($product['description']); 
                            ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <?php echo isset($productId) ? 'Update Product' : 'Add Product'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>

    <script>
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Product preview">`;
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Price formatting - updated version
        const priceInput = document.getElementById('price');
        
        // Format initial value if it exists
        if (priceInput.value) {
            priceInput.value = parseInt(priceInput.value).toLocaleString('id-ID').replace(/,/g, '.');
        }

        priceInput.addEventListener('input', function(e) {
            // Get cursor position
            let cursorPos = this.selectionStart;
            
            // Get unformatted value
            let value = this.value.replace(/\./g, '');
            
            // Remove any non-digits
            value = value.replace(/\D/g, '');
            
            if (value) {
                // Get current length before formatting
                const beforeLen = this.value.length;
                
                // Format with Indonesian thousand separators
                const formatted = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                this.value = formatted;
                
                // Adjust cursor position based on whether we added a separator
                cursorPos += this.value.length - beforeLen;
                
                // Make sure cursor position is within bounds
                cursorPos = Math.max(0, Math.min(cursorPos, this.value.length));
                
                // Set cursor position
                this.setSelectionRange(cursorPos, cursorPos);
            }
        });

        // Remove formatting before form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            priceInput.value = priceInput.value.replace(/\./g, '');
        });
    </script>
</body>
</html> 
