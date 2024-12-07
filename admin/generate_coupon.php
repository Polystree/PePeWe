<?php
session_start();
include '../login/database.php';

if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header('Location: ../login/index.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['delete_coupon'])) {
    $couponCode = strtoupper(bin2hex(random_bytes(4))); // Generate a random coupon code
    $discount = $_POST['discount'];
    $expiryDate = $_POST['expiry_date'];

    $stmt = $connect->prepare("INSERT INTO coupons (code, discount, expiry_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $couponCode, $discount, $expiryDate);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_coupon'])) {
    $couponCodeToDelete = $_POST['coupon_code'];
        
    $stmt = $connect->prepare("DELETE FROM coupons WHERE code = ?");
    $stmt->bind_param("s", $couponCodeToDelete);
    $stmt->execute();
    $stmt->close();
        
    // Optionally, you can redirect or display a success message
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

$query = "SELECT code, discount, expiry_date FROM coupons";
$result = $connect->query($query);
$coupons = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Coupon Codes</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/cart.css">
</head>
<body>
    <?php include '../header.php'; ?>
    <div class="main">
        <div id="upload-product-title">
            <span>Generate Coupon Codes</span>
        </div>
        <form method="POST" action="" id="upload-product-form">
            <div class="upload-product-item">
                <div class="credential-form">
                    <label for="discount" class="upload-label">Discount</label>
                    <div class="price-input">
                        <input type="number" name="discount" id="price" placeholder="Your discount (%)..." required>
                        <div class="currency-symbol-container">
                            <span class="currency-symbol">%</span>
                        </div>
                    </div>
                </div>
                <div class="credential-form">
                    <label for="expiry_date" class="">Expiry Date</label>
                    <input type="date" name="expiry_date" id="expiry_date" required>
                </div>
                <button type="submit" class="next-button">Generate Coupon</button>
            </div>
        </form>
        <div style="margin:0 2rem;">
        <h2>Existing Coupons</h2>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Coupon Code</th>
                    <th>Discount (%)</th>
                    <th>Expiry Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($coupons)): ?>
                <?php foreach ($coupons as $coupon): ?>
                <tr>
                    <td><?php echo htmlspecialchars($coupon['code']); ?></td>
                    <td><?php echo htmlspecialchars($coupon['discount']); ?></td>
                    <td><?php echo htmlspecialchars($coupon['expiry_date']); ?></td>
                    <td class='manage-product-btn' style="width: 8%">
                        <form method="POST" action="">
                            <input type="hidden" name="coupon_code" value="<?php echo htmlspecialchars($coupon['code']); ?>">
                            <button type="submit" name="delete_coupon" class="delete-button">Delete</button>
                        </form>
                    </td>

                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="3">No coupons available.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </div>
    <?php include '../footer.php'; ?>
</body>
</html>