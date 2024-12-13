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

// Strict admin check with redirect
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header('Location: /login');
    exit();
}

// Use prepared statement for better security
$stmt = $connect->prepare("SELECT productId, name, image_path, description, price, quantity, 
    category, sold_count, view_count, created_at, updated_at, is_featured, status 
    FROM products");
if (!$stmt) {
    error_log("Failed to prepare statement: " . $connect->error);
    die("An error occurred. Please try again later.");
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>iniGadget</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo-light.svg" />
    <title>Manage Products - iniGadget</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/cart.css" />
    <link rel="stylesheet" href="../assets/css/admin.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" />
</head>

<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class="main-content">
        <h2>Manage Products</h2>
        <button onclick="window.location.href='add-product.php'" class="add-product-btn">Add Product</button>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price (Rp)</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Stats</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            style='border-radius: 5px;' />
                    </td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td class="description-cell" data-full-text="<?php echo htmlspecialchars($product['description']); ?>">
                        <?php echo htmlspecialchars($product['description']); ?>
                    </td>
                    <td><?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                    <td>
                        <div>Sold: <?php echo $product['sold_count']; ?></div>
                        <div>Views: <?php echo $product['view_count']; ?></div>
                        <div class="timestamp">Added: <?php echo date('Y-m-d', strtotime($product['created_at'])); ?></div>
                    </td>
                    <td>
                        <span class="status-badge <?php echo strtolower($product['status']); ?>">
                            <?php echo htmlspecialchars($product['status']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="featured-badge <?php echo $product['is_featured'] ? 'yes' : 'no'; ?>">
                            <?php echo $product['is_featured'] ? 'Yes' : 'No'; ?>
                        </span>
                    </td>
                    <td class='manage-product-btn'>
                        <button
                            onclick="window.location.href='add-product.php?productId=<?php echo $product['productId']; ?>'"
                            class="edit-button">Edit</button>
                        <form class="delete-form">
                            <input type="hidden" name="productId" value="<?php echo $product['productId']; ?>">
                            <button type="button" class="delete-button">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="5">No products available.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
    
    <script>
    // Delete product handler
    document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', async function(e) {
            if (!confirm('Are you sure you want to delete this product?')) return;

            const form = this.closest('form');
            const productId = form.querySelector('input[name="productId"]').value;
            
            try {
                const response = await fetch('delete-product.php', {
                    method: 'POST',
                    body: new FormData(form)
                });
                
                const result = await response.json();
                if (result.success) {
                    // Remove the row from the table
                    const row = this.closest('tr');
                    row.remove();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while deleting the product');
            }
        });
    });

    // Add new row helper function
    function addProductRow(product) {
        const tbody = document.querySelector('.cart-table tbody');
        const row = document.createElement('tr');
        
        row.innerHTML = `
            <td><img src="${product.image_path}" alt="${product.name}" style='border-radius: 5px;' /></td>
            <td>${product.name}</td>
            <td class="description-cell">${product.description}</td>
            <td>${Number(product.price).toLocaleString('id-ID')}</td>
            <td>${product.quantity}</td>
            <td>${product.category || ''}</td>
            <td>
                <div>Sold: ${product.sold_count || 0}</div>
                <div>Views: ${product.view_count || 0}</div>
                <div class="timestamp">Added: ${new Date().toISOString().split('T')[0]}</div>
            </td>
            <td>
                <span class="status-badge ${product.status || 'active'}">${product.status || 'active'}</span>
            </td>
            <td>
                <span class="featured-badge ${product.is_featured ? 'yes' : 'no'}">${product.is_featured ? 'Yes' : 'No'}</span>
            </td>
            <td class='manage-product-btn'>
                <button onclick="window.location.href='add-product.php?productId=${product.productId}'" class="edit-button">Edit</button>
                <form class="delete-form">
                    <input type="hidden" name="productId" value="${product.productId}">
                    <button type="button" class="delete-button">Delete</button>
                </form>
            </td>
        `;
        
        tbody.insertBefore(row, tbody.firstChild);
        
        // Attach delete handler to new row
        const deleteButton = row.querySelector('.delete-button');
        deleteButton.addEventListener('click', function(e) {
            // ... existing delete handler code ...
        });
    }
    </script>
</body>

</html>