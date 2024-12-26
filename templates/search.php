<?php
require_once __DIR__ . '/../includes/Database.php';
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';
$sort = $_GET['sort'] ?? '';
$category = $_GET['category'] ?? '';
$db = Database::getInstance();
$categories = $db->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL")->fetch_all(MYSQLI_ASSOC);

if (!empty($searchQuery)): 
?>
<link rel="stylesheet" href="/assets/css/search.css" />
<main class="container">
    <section class="products-section">
        <div class="section-header">
            <div class="header-group">
                <h2>Results for "<?= htmlspecialchars($searchQuery) ?>"</h2>
                <form method="GET" action="" class="filter-form">
                    <input type="hidden" name="query" value="<?= htmlspecialchars($searchQuery) ?>">
                    <div class="filter-group">
                        <select name="category" class="filter-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>" <?= $category === $cat['category'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="sort" class="filter-select" onchange="this.form.submit()">
                            <option value="">Sort by</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php
        $whereConditions = ["name LIKE ?"];
        $params = ["%$searchQuery%"];
        $types = "s";
        
        if (!empty($category)) {
            $whereConditions[] = "category = ?";
            $params[] = $category;
            $types .= "s";
        }
        
        $orderBy = match($sort) {
            'price_asc' => "final_price ASC",
            'price_desc' => "final_price DESC",
            'newest' => "p.created_at DESC",
            default => "p.name ASC"
        };

        $query = "SELECT p.*, d.discount_percent, CASE WHEN d.discount_percent IS NOT NULL THEN p.price * (100 - d.discount_percent) / 100 ELSE p.price END as final_price FROM products p LEFT JOIN discounts d ON p.productId = d.product_id AND d.start_date <= NOW() AND (d.end_date IS NULL OR d.end_date >= NOW()) WHERE " . implode(" AND ", $whereConditions) . " ORDER BY $orderBy";
        $stmt = $db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $searchResults = $stmt->get_result();
        ?>

        <?php if ($searchResults->num_rows > 0): ?>
            <div class="products-grid">
                <?php while ($product = $searchResults->fetch_assoc()): ?>
                    <article class="product-card">
                        <a href="/products/<?= urlencode(strtolower(str_replace(' ', '-', $product['name']))) ?>/">
                            <div class="product-image">
                                <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                <?php if(isset($product['discount_percent'])): ?>
                                    <span class="discount-badge">-<?= $product['discount_percent'] ?>%</span>
                                <?php endif; ?>
                                <div class="quick-actions">
                                    <button class="action-btn cart" data-id="<?= $product['productId'] ?>"><i class="fas fa-shopping-cart"></i></button>
                                    <button class="action-btn wishlist"><i class="fas fa-heart"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <div class="price-group">
                                    <?php if(isset($product['discount_percent'])): ?>
                                        <span class="price-current">Rp <?= number_format($product['final_price'], 0, ',', '.') ?></span>
                                        <span class="price-original">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="price-current">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <p>No products found matching your search criteria.</p>
                <a href="/" class="cta-button">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php endif; ?>