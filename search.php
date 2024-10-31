<?php
if (!isset($connect)) {
    include 'login/database.php';
}

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $searchQuery = htmlspecialchars(trim($_GET['query']));

    $stmt = $connect->prepare("SELECT * FROM products WHERE name LIKE ?");
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
                echo "<div class='product'>";
                echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='" . htmlspecialchars($row['name']) . "' />";
                echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                echo "<p>Price: Rp " . number_format($row['price'], 0, ',', '.') . "</p>";
                echo "<p>" . nl2br(htmlspecialchars($row['description'])) . "</p>";
                echo "<button class='add-to-cart next-button' name='product-page'>Add to cart</button>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p>No products found matching your search.</p>";
        }
        echo "</div>";

        $stmt->close();
    } else {
        echo "<p>Error preparing the search query.</p>";
    }
} else {
    echo "<p>Please enter a search term.</p>";
}
?>

<link rel="stylesheet" href="/assets/css/search.css" />