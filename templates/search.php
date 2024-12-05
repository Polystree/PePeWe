<?php
require_once __DIR__ . '/../includes/Database.php';

// Initialize searchQuery
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

?>
<form method="GET" action="">
    <input type="hidden" name="query" value="<?php echo htmlspecialchars($searchQuery); ?>">
    <select name="sort" onchange="this.form.submit()">
        <option value="">Sort by</option>
        <option value="price_asc" <?php echo ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
        <option value="price_desc" <?php echo ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
    </select>
</form>

<?php
$db = Database::getInstance();  // This already returns the mysqli connection

if (!empty($searchQuery)) {
    $searchQuery = htmlspecialchars($searchQuery);
    $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
    $orderBy = '';

    switch ($sort) {
        case 'price_asc':
            $orderBy = 'ORDER BY price ASC';
            break;
        case 'price_desc':
            $orderBy = 'ORDER BY price DESC';
            break;
    }

    $stmt = $db->prepare("SELECT * FROM products WHERE name LIKE ? $orderBy");  // Use $db directly
    if ($stmt) {
        $likeQuery = "%" . $searchQuery . "%";
        $stmt->bind_param("s", $likeQuery);
        $stmt->execute();
        $result = $stmt->get_result();

        echo "<div class='search-result-page'>";
        echo "<div class='search-result'>Search Results for '" . $searchQuery . "':</div>";

        if ($result->num_rows > 0) {
            echo "<div class='products'>";
            while ($row = $result->fetch_assoc()) {
                $url = $row['url'] ?? '';
                $image = $row['image_path'] ?? '';
                $name = $row['name'] ?? '';
                $price = $row['price'] ?? 0;
                $description = $row['description'] ?? '';
                $productId = $row['id'] ?? 0;

                echo "<div class='product-card'>";
                echo "<a href='" . htmlspecialchars($url) . "' class='product-link'>";
                echo "<div class='product'>";
                echo "<img src='" . htmlspecialchars($image) . "' alt='" . htmlspecialchars($name) . "' />";
                echo "<h3>" . htmlspecialchars($name) . "</h3>";
                echo "<p>Price: Rp " . number_format($price, 0, ',', '.') . "</p>";
                echo "<p class='description'>" . nl2br(htmlspecialchars($description)) . "</p>";
                echo "</div>";
                echo "</a>";
                echo "<button class='add-to-cart-btn next-button' data-product-id='" . $productId . "'>Add to Cart</button>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>No products found matching your search.</p>";
        }
        echo "</div>";
        echo "</a>";

        $stmt->close();
    } else {
        echo "<p>Error preparing the search query.</p>";
    }
} else {
    echo "<p>Please enter a search term.</p>";
}
?>

<link rel="stylesheet" href="/assets/css/search.css" />