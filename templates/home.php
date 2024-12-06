<?php
require_once __DIR__ . '/../includes/Product.php';
require_once __DIR__ . '/../includes/Template.php';

$product = new Product();
$template = Template::getInstance();

// Add error logging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all required product data
$featuredProducts = $product->getFeaturedProducts(8);
if (empty($featuredProducts)) {
    error_log("No featured products returned from query");
}

$recentProducts = $product->getRecentlyUpdated(10);
$flashSaleProducts = $product->getFlashSaleProducts(8);
if (empty($flashSaleProducts)) {
    error_log("No flash sale products returned from query");
}

$bestSelling = $product->getBestSelling(5);
$newArrivals = $product->getNewArrivals(4);
$allProducts = $product->getAllProducts(20);

// Helper function to create URL-friendly product slug
function createProductSlug($name) {
    return './products/' . strtolower(str_replace(' ', '-', $name)) . '/';
}

// Keep existing CSS and hero section
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
<link rel="stylesheet" href="/assets/css/home.css">

<!-- Hero Banner Section -->
<section class="hero-banner">
    <div class="hero-content container">
        <div class="hero-text">
            <div class="hero-tag">
                <p>Pro.Beyond.</p>
                <span class="badge-new">New Release</span>
            </div>
            <h1>iPhone 16 Pro</h1>
            <p>Experience the future with the latest flagship device</p>
            <a href="#products" class="cta-button">Shop Now</a>
        </div>
        <div class="hero-image">
            <img src="/assets/img/hero.webp" alt="iPhone 16 Pro">
        </div>
    </div>
</section>

<main class="container">
    <!-- Featured Products Grid -->
    <section class="featured-section">
        <div class="section-header">
            <div class="header-group">
                <span class="section-tag">Best Deals</span>
                <div>
                    <h2>Featured Products</h2>
                    <a href="/featured" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="products-grid">
            <?php if (!empty($featuredProducts)): ?>
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <a href="<?= createProductSlug($product['name']) ?>">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($product['image_path']) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php if($product['discount'] > 0): ?>
                        <span class="discount-badge">-<?= $product['discount'] ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="price-group">
                            <?php if($product['discount'] > 0): ?>
                            <span class="price-current">Rp
                                <?= number_format($product['discounted_price'], 0, ',', '.') ?></span>
                            <span class="price-original">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <?php else: ?>
                            <span class="price-current">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <button class="add-to-cart-btn" data-id="<?= $product['productId'] ?>">
                    Add to Cart
                </button>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p class="no-products">No featured products available</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Flash Sales Section -->
    <section class="flash-sales">
        <div class="section-header">
            <div class="header-group">
                <span class="section-tag">Limited Time</span>
                <div>
                    <h2>Flash Sales</h2>
                    <a href="/flash-sales" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                <div class="countdown" id="flash-sale-timer">
                    <div class="time-block">
                        <span id="hours">24</span>
                        <label>Hours</label>
                    </div>
                    <div class="time-block">
                        <span id="minutes">00</span>
                        <label>Minutes</label>
                    </div>
                    <div class="time-block">
                        <span id="seconds">00</span>
                        <label>Seconds</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="products-grid">
            <?php foreach ($flashSaleProducts as $product): ?>
            <div class="product-card sale">
                <a href="<?= createProductSlug($product['name']) ?>">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($product['image_path']) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>">
                        <span class="discount-badge">-<?= $product['discount'] ?>%</span>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="price-group">
                            <?php if($product['discount'] > 0): ?>
                            <span class="price-current">Rp
                                <?= number_format($product['discounted_price'], 0, ',', '.') ?></span>
                            <span class="price-original">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <?php else: ?>
                            <span class="price-current">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="stock-info">
                            <div class="stock-bar">
                                <div class="stock-progress"
                                    style="width: <?= min(100, ($product['sold_count'] / $product['quantity']) * 100) ?>%">
                                </div>
                            </div>
                            <span class="stock-text">Sold: <?= $product['sold_count'] ?></span>
                        </div>
                    </div>
                </a>
                <button class="add-to-cart-btn" data-id="<?= $product['productId'] ?>">
                    Add to Cart
                </button>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- New Arrivals Section -->
    <section class="products-section">
        <div class="section-header">
            <div class="header-group">
                <span class="section-tag">Latest Products</span>
                <div>
                    <h2>New Arrivals</h2>
                    <a href="/new-arrivals" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="products-grid">
            <?php foreach($newArrivals as $product): ?>
            <article class="product-card">
                <div class="product-image">
                    <img src="<?= htmlspecialchars($product['image_path']) ?>"
                        alt="<?= htmlspecialchars($product['name']) ?>">
                    <?php if($product['discount'] > 0): ?>
                    <span class="discount-badge">-<?= $product['discount'] ?>%</span>
                    <?php endif; ?>
                    <div class="quick-actions">
                        <button class="action-btn cart" data-id="<?= $product['productId'] ?>">
                            <i class="fas fa-shopping-cart"></i>
                        </button>
                        <button class="action-btn wishlist">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                </div>
                <div class="product-info">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <div class="price-group">
                        <?php if($product['discount'] > 0): ?>
                        <span class="price-current">
                            Rp <?= number_format($product['price'] * (100 - $product['discount']) / 100, 0, ',', '.') ?>
                        </span>
                        <span class="price-original">
                            Rp <?= number_format($product['price'], 0, ',', '.') ?>
                        </span>
                        <?php else: ?>
                        <span class="price-current">
                            Rp <?= number_format($product['price'], 0, ',', '.') ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Explore Section -->
    <section class="products-section explore-section">
        <div class="section-header">
            <div class="header-group">
                <span class="section-tag">Browse All</span>
                <div>
                    <h2>Explore Our Products</h2>
                    <a href="/products" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <div class="products-grid">
            <?php foreach($allProducts as $product): ?>
            <article class="product-card">
                <a href="<?= createProductSlug($product['name']) ?>">
                    <div class="product-image">
                        <img src="<?= htmlspecialchars($product['image_path']) ?>"
                            alt="<?= htmlspecialchars($product['name']) ?>">
                        <?php if($product['discount'] > 0): ?>
                        <span class="discount-badge">-<?= $product['discount'] ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="price-group">
                            <?php if($product['discount'] > 0): ?>
                            <span class="price-current">Rp
                                <?= number_format($product['discounted_price'], 0, ',', '.') ?></span>
                            <span class="price-original">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <?php else: ?>
                            <span class="price-current">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
                <button class="add-to-cart-btn" data-id="<?= $product['productId'] ?>">
                    Add to Cart
                </button>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Remove category tabs initialization
    const quickActions = document.querySelectorAll('.quick-actions .action-btn');
    quickActions.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            // Add cart/wishlist logic here
        });
    });
});
</script>