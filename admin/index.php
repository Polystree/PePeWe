<?php
session_start();

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

$stmt = $connect->prepare("SELECT d.*, p.name as product_name 
    FROM discounts d 
    JOIN products p ON d.product_id = p.productId
    ORDER BY d.start_date DESC");
$stmt->execute();
$discounts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$stmt = $connect->prepare("SELECT * FROM coupons ORDER BY expiry_date DESC");
$stmt->execute();
$coupons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
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
        <div class="admin-tabs">
            <button class="tab-button active" data-tab="products">Products</button>
            <button class="tab-button" data-tab="discounts">Discounts</button>
            <button class="tab-button" data-tab="coupons">Coupons</button>
        </div>

        <div id="products" class="tab-content active">
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

        <div id="discounts" class="tab-content">
            <button onclick="showAddDiscountModal()" class="add-product-btn">Add Discount</button>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Name</th>
                        <th>Discount %</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($discounts as $discount): ?>
                    <tr>
                        <?php
                        $stmt = $connect->prepare("SELECT image_path FROM products WHERE productId = ?");
                        $stmt->bind_param("i", $discount['product_id']);
                        $stmt->execute();
                        $image = $stmt->get_result()->fetch_assoc()['image_path'];
                        $stmt->close();
                        ?>
                        <td>
                            <img src="<?php echo htmlspecialchars($image); ?>"
                                alt="<?php echo htmlspecialchars($discount['product_name']); ?>"
                                style='border-radius: 5px;' />
                        </td>
                        <td><?php echo htmlspecialchars($discount['product_name']); ?></td>
                        <td><?php echo $discount['discount_percent']; ?>%</td>
                        <td><?php echo $discount['is_flash_sale'] ? 'Flash Sale' : 'Regular Discount'; ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($discount['start_date'])); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($discount['end_date'])); ?></td>
                        <td>
                            <?php 
                            $now = new DateTime();
                            $start = new DateTime($discount['start_date']);
                            $end = new DateTime($discount['end_date']);
                            
                            if ($now < $start) {
                                echo '<span class="status-badge scheduled">Scheduled</span>';
                            } elseif ($now > $end) {
                                echo '<span class="status-badge expired">Expired</span>';
                            } else {
                                echo '<span class="status-badge active">Active</span>';
                            }
                            ?>
                        </td>
                        <td class="manage-product-btn">
                            <button onclick="editDiscount(<?php echo htmlspecialchars(json_encode($discount)); ?>)" 
                                    class="edit-button">Edit</button>
                            <button onclick="deleteDiscount(<?php echo $discount['id']; ?>)" 
                                    class="delete-button">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="coupons" class="tab-content">
            <button onclick="showAddCouponModal()" class="add-product-btn">Add Coupon</button>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Discount (%)</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coupons as $coupon): ?>
                    <tr>
                        <td><span class="coupon-code"><?php echo htmlspecialchars($coupon['code']); ?></span></td>
                        <td><?php echo $coupon['discount']; ?>%</td>
                        <td><?php echo date('Y-m-d', strtotime($coupon['expiry_date'])); ?></td>
                        <td>
                            <?php 
                            $now = new DateTime();
                            $expiry = new DateTime($coupon['expiry_date']);
                            if ($now > $expiry) {
                                echo '<span class="status-badge expired">Expired</span>';
                            } else {
                                echo '<span class="status-badge active">Active</span>';
                            }
                            ?>
                        </td>
                        <td class='manage-product-btn'>
                            <button onclick="editCoupon(<?php echo htmlspecialchars(json_encode($coupon)); ?>)" 
                                    class="edit-button">Edit</button>
                            <form class="delete-form">
                                <input type="hidden" name="couponId" value="<?php echo $coupon['id']; ?>">
                                <button type="button" onclick="deleteCoupon(<?php echo $coupon['id']; ?>)" 
                                        class="delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="discountModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 id="modalTitle">Add Discount</h2>
                <form id="discountForm" method="POST" action="manage-discount.php">
                    <input type="hidden" name="discount_id" id="discount_id">
                    <div class="form-group">
                        <label for="product_id">Product</label>
                        <select name="product_id" id="product_id" required>
                            <?php
                            $stmt = $connect->prepare("SELECT productId, name FROM products");
                            $stmt->execute();
                            $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                            foreach ($products as $product) {
                                echo "<option value='{$product['productId']}'>{$product['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="discount_percent">Discount Percentage</label>
                        <input type="number" name="discount_percent" id="discount_percent" 
                               min="1" max="99" required>
                    </div>
                    <div class="form-group">
                        <label for="is_flash_sale">Sale Type</label>
                        <select name="is_flash_sale" id="is_flash_sale">
                            <option value="0">Regular Discount</option>
                            <option value="1">Flash Sale</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="datetime-local" name="start_date" id="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="datetime-local" name="end_date" id="end_date" required>
                    </div>
                    <button type="submit" class="btn-primary">Save Discount</button>
                </form>
            </div>
        </div>

        <div id="couponModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('couponModal')">&times;</span>
                <h2>Add New Coupon</h2>
                <form id="couponForm">
                    <input type="hidden" name="couponId" id="couponId">
                    <div class="form-group">
                        <label for="code">Coupon Code</label>
                        <input type="text" id="code" name="code" required pattern="[A-Za-z0-9]+" 
                               title="Only letters and numbers allowed">
                    </div>
                    <div class="form-group">
                        <label for="discount">Discount Percentage</label>
                        <input type="number" id="discount" name="discount" min="1" max="100" required>
                    </div>
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Coupon</button>
                </form>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
    
    <script>
        document.querySelectorAll('.delete-form').forEach(form => {
            form.querySelector('.delete-button').addEventListener('click', async function(e) {
                if (!confirm('Are you sure you want to delete this product?')) return;
                
                try {
                    const response = await fetch('delete-product.php', {
                        method: 'POST',
                        body: new FormData(form)
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        form.closest('tr').remove();
                    } else {
                        alert('Error: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the product');
                }
            });
        });

        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                button.classList.add('active');
                document.getElementById(button.dataset.tab).classList.add('active');
            });
        });

        const modal = document.getElementById('discountModal');
        const span = document.getElementsByClassName('close')[0];
        
        function showAddDiscountModal() {
            document.getElementById('modalTitle').textContent = 'Add Discount';
            document.getElementById('discountForm').reset();
            document.getElementById('discount_id').value = '';
            modal.style.display = 'block';
            
            const today = new Date().toISOString().slice(0, 16);
            document.getElementById('start_date').min = today;
            document.getElementById('end_date').min = today;
        }

        function editDiscount(discount) {
            document.getElementById('modalTitle').textContent = 'Edit Discount';
            document.getElementById('discount_id').value = discount.id;
            document.getElementById('product_id').value = discount.product_id;
            document.getElementById('discount_percent').value = discount.discount_percent;
            document.getElementById('is_flash_sale').value = discount.is_flash_sale;
            document.getElementById('start_date').value = discount.start_date.slice(0, 16);
            document.getElementById('end_date').value = discount.end_date.slice(0, 16);
            modal.style.display = 'block';
        }

        span.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        async function deleteDiscount(id) {
            if (!confirm('Are you sure you want to delete this discount?')) return;
            
            try {
                const response = await fetch('manage-discount.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&discount_id=${id}`
                });
                
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while deleting the discount');
            }
        }

        function showAddCouponModal() {
            document.getElementById('couponForm').reset();
            document.getElementById('couponId').value = '';
            document.getElementById('couponModal').style.display = 'block';
        }

        function editCoupon(coupon) {
            document.getElementById('couponId').value = coupon.id;
            document.getElementById('code').value = coupon.code;
            document.getElementById('discount').value = coupon.discount;
            document.getElementById('expiry_date').value = coupon.expiry_date;
            document.getElementById('couponModal').style.display = 'block';
        }

        function deleteCoupon(id) {
            if (confirm('Are you sure you want to delete this coupon?')) {
                fetch('/admin/api/coupons.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting coupon');
                    }
                });
            }
        }

        document.getElementById('couponForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const method = formData.get('couponId') ? 'PUT' : 'POST';
            
            fetch('/admin/api/coupons.php', {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Error saving coupon');
                }
            });
        });

        const discountModal = document.getElementById('discountModal');
        const couponModal = document.getElementById('couponModal');
        const closeButtons = document.getElementsByClassName('close');

        Array.from(closeButtons).forEach(button => {
            button.onclick = function() {
                discountModal.style.display = 'none';
                couponModal.style.display = 'none';
            }
        });

        window.onclick = function(event) {
            if (event.target == discountModal) {
                discountModal.style.display = 'none';
            }
            if (event.target == couponModal) {
                couponModal.style.display = 'none';
            }
        }

        function showAddCouponModal() {
            document.getElementById('couponForm').reset();
            document.getElementById('couponId').value = '';
            couponModal.style.display = 'block';
            
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('expiry_date').min = today;
        }
    </script>
</body>
</html>