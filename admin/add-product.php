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
    // Remove dots and convert to float before formatting
    $price_clean = (float)str_replace('.', '', $price);
    
    $productPageContent = "
<?php
session_start();

// Get database connection using correct config keys
\$config = include(__DIR__ . '/../config/config.php');
\$db_config = \$config['db'];

\$connect = new mysqli(
    \$db_config['host'],
    \$db_config['username'], 
    \$db_config['password'],
    \$db_config['database']
);

if (\$connect->connect_error) {
    error_log(\"Database connection failed: \" . \$connect->connect_error);
    die(\"Connection failed. Please try again later.\");
}

function getProductId(\$product_name, \$connect) {
    \$stmt = \$connect->prepare('SELECT productId FROM products WHERE name = ? LIMIT 1');
    \$stmt->bind_param('s', \$product_name);
    \$stmt->execute();
    \$stmt->bind_result(\$productId);
    \$stmt->fetch();
    \$stmt->close();
    return \$productId;
}

if (\$_SERVER['REQUEST_METHOD'] == 'POST' && isset(\$_POST['cart'])) {
    \$product_name = \$_POST['product_name'];
    \$price = \$_POST['price'];
    \$image_path = \$_POST['image_path'];
    \$quantity = \$_POST['quantity'];
    \$userId = \$_SESSION['userId'];

    \$stmt = \$connect->prepare('SELECT productId FROM products WHERE name = ? AND price = ? AND image_path = ?');
    \$stmt->bind_param('sis', \$product_name, \$price, \$image_path);
    \$stmt->execute();
    \$stmt->bind_result(\$productId);
    \$stmt->fetch();
    \$stmt->close();

    \$stmt = \$connect->prepare('INSERT INTO cart (product_name, price, image_path, quantity, productId, userId) VALUES (?, ?, ?, ?, ?, ?)');
    \$stmt->bind_param('sisiii', \$product_name, \$price, \$image_path, \$quantity, \$productId, \$userId);
    \$stmt->execute();
    \$stmt->close();
}

// Handle adding, editing, or deleting reviews
if (\$_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset(\$_POST['add_review'])) {
        \$userId = \$_SESSION['userId'];
        \$product_name = '$name';
        \$productId = getProductId(\$product_name, \$connect);

        \$review = \$_POST['review'];
        
        \$stmt = \$connect->prepare(\"INSERT INTO reviews (userId, productId, review) VALUES (?, ?, ?)\");
        \$stmt->bind_param(\"iis\", \$userId, \$productId, \$review);
        \$stmt->execute();
        \$stmt->close();
    } elseif (isset(\$_POST['edit_review'])) {
        \$reviewId = \$_POST['review_id'];
        \$review = \$_POST['review'];
        
        \$stmt = \$connect->prepare(\"UPDATE reviews SET review = ? WHERE id = ? AND userId = ?\");
        \$stmt->bind_param(\"sii\", \$review, \$reviewId, \$_SESSION['userId']);
        \$stmt->execute();
        \$stmt->close();
    } elseif (isset(\$_POST['delete_review'])) {
        \$reviewId = \$_POST['review_id'];
        
        \$stmt = \$connect->prepare(\"DELETE FROM reviews WHERE id = ? AND userId = ?\");
        \$stmt->bind_param(\"ii\", \$reviewId, \$_SESSION['userId']);
        \$stmt->execute();
        \$stmt->close();
    }
}

// Fetch reviews for this product
\$product_name = '$name';
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
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8' />
    <meta name='viewport' content='initial-scale=1, width=device-width' />
    <title>$name - Product Details - iniGadget</title>
    <link rel='icon' type='image/x-icon' href='../assets/img/logo-light.svg' />
    <link rel='stylesheet' href='../assets/css/style.css' />
    <link rel='stylesheet' href='../assets/css/cart.css' />
    <link rel='stylesheet' href='../assets/css/product.css' />
    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap' />
    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap' />
</head>
<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class='main-content'>
        <div class='product-details'>
            <h1>$name</h1>
            <img src='$image_path' alt='$name' style='width: 300px; border-radius: 5px;' />
            <p class='price'>Rp " . number_format($price_clean, 0, ',', '.') . "</p>
            <p class='description'>$description</p>
            <form method='POST' class='cart-form'>
                <input type='hidden' name='product_name' value='$name'>
                <input type='hidden' name='price' value='$price'>
                <input type='hidden' name='image_path' value='$image_path'>
                <input type='hidden' name='productId' value='<?php echo isset(\$productId) ? \$productId : ''; ?>'>
                <div class='credential-form'>
                    <label for='quantity' class='upload-label'>Quantity:</label>
                    <input type='number' name='quantity' id='quantity' value='1' min='1' required>
                </div>
                <button type='submit' class='next-button' name='cart'>Add to Cart</button>
            </form>

            <div class='review-section'>
                <h2>Reviews</h2>
                <?php foreach (\$reviews as \$review): ?>
                    <div class='review'>
                        <img src='<?php echo \$review[\"profile_image\"]; ?>' alt='<?php echo \$review[\"username\"]; ?>' class='profile-image'>
                        <div class='review-content'>
                            <h3><?php echo \$review[\"username\"]; ?></h3>
                            <p><?php echo \$review[\"review\"]; ?></p>
                            <?php if (isset(\$_SESSION[\"userId\"]) && \$_SESSION[\"userId\"] == \$review[\"userId\"]): ?>
                                <form method='POST' class='review-form'>
                                    <input type='hidden' name='review_id' value='<?php echo \$review[\"id\"]; ?>'>
                                    <textarea name='review' class='input-description'><?php echo \$review[\"review\"]; ?></textarea>
                                    <div class='review-buttons'>
                                        <button type='submit' name='edit_review' class='edit-button'>Edit</button>
                                        <button type='submit' name='delete_review' class='delete-button'>Delete</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if (isset(\$_SESSION[\"userId\"])): ?>
                    <?php if (!\$userHasReviewed): ?>
                        <form method='POST' class='review-form'>
                            <textarea name='review' class='input-description' placeholder='Write your review here...'></textarea>
                            <button type='submit' name='add_review' class='next-button'>Submit Review</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>
";

    // Create directory and file with new structure
    $dirName = '../products/' . strtolower(str_replace(' ', '-', $name));
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
