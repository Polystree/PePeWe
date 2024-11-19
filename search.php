<form method="GET" action="">
    <input type="hidden" name="query" value="<?php echo htmlspecialchars($searchQuery); ?>">
    <select name="sort" onchange="this.form.submit()">
        <option value="">Sort by</option>
        <option value="price_asc" <?php echo ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
        <option value="price_desc" <?php echo ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
    </select>
</form>

<?php
if (!isset($connect)) {
    include 'login/database.php';
}

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $searchQuery = htmlspecialchars(trim($_GET['query']));
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

    $stmt = $connect->prepare("SELECT * FROM products WHERE name LIKE ? $orderBy");
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
                echo "<a href='{$row['url']}'>";
                echo "<div class='product'>";
                echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='" . htmlspecialchars($row['name']) . "' />";
                echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                echo "<p>Price: Rp " . number_format($row['price'], 0, ',', '.') . "</p>";
                echo "<p>" . nl2br(htmlspecialchars($row['description'])) . "</p>";
                // echo "<button class='add-to-cart next-button' name='cart'>Add to cart</button>";
                echo "</div>";
                echo "</a>";
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