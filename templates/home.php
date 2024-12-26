<?php
require_once __DIR__ . '/../includes/Product.php';
require_once __DIR__ . '/../includes/Template.php';

$product = new Product();
$template = Template::getInstance();
$featuredProducts = $product->getFeaturedProducts(10);
$flashSaleProducts = $product->getFlashSaleProducts(10);
$newArrivals = $product->getNewArrivals(5);
$allProducts = $product->getAllProducts(20);

function createProductSlug($name) {
    return './products/' . strtolower(str_replace(' ', '-', $name)) . '/';
}
?>

<link rel="stylesheet" href="/assets/css/home.css">
<section class="hero-banner">
    <div class="hero-content container">
        <div class="hero-text">
            <div class="hero-tag">
                <p>Pro.Beyond.</p>
                <span class="badge-new">New Release</span>
            </div>
            <h1>iPhone 16 Pro</h1>
            <p>Experience the future with the latest flagship device</p>
            <a href="/products/iphone-16-pro/" class="cta-button">Shop Now</a>
        </div>
        <div class="hero-image">
            <img src="/assets/img/hero.webp" alt="iPhone 16 Pro">
        </div>
    </div>
</section>

<main class="container">
    <section class="featured-section">
        <div class="section-header">
            <div class="header-group">
                <span class="section-tag">Best Deals</span>
                <div>
                    <h2>Featured Products</h2>
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
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p class="no-products">No featured products available</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="flash-sales">
        <div class="section-header">
            <div class="header-group">
                <span class="section-tag">Limited Time</span>
                <div>
                    <h2>Flash Sales</h2>
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
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="products-section">
        <div class="section-header">
            <div class="header-group">
                <span class="section-tag">Latest Products</span>
                <div>
                    <h2>New Arrivals</h2>
                </div>
            </div>
        </div>

        <div class="products-grid">
            <?php foreach($newArrivals as $product): ?>
            <article class="product-card">
                <a href="<?= createProductSlug($product['name']) ?>">
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
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="products-section explore-section">
        <div class="section-header">
            <div class="header-group">
                <span class="section-tag">Browse All</span>
                <div>
                    <h2>Explore Our Products</h2>
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
            </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>