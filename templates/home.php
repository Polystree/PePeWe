<?php
require_once __DIR__ . '/../includes/Product.php';
require_once __DIR__ . '/../includes/Template.php';

$product = new Product();
$template = Template::getInstance();

// Fetch all required product data
$recentProducts = $product->getRecentlyUpdated(10);
$flashSaleProducts = $product->getFlashSaleProducts(10);
$bestSelling = $product->getBestSelling(5);
$newArrivals = $product->getNewArrivals(4);
$exploredProducts = $product->getExploredProducts(8); // Add this line

// Keep existing CSS and hero section
?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
<link rel="stylesheet" href="/assets/css/home.css">

<section class="hero">
    <div class="text">
        <p>Pro.Beyond.</p>
        <h1>IPhone 16 Pro</h1>
        <p>Created to change everything for the better. For everyone</p>
        <a href="#recently-updated" class="shop-button">Shop Now</a>
    </div>
    <img alt="IPhone 16 Pro" src="/assets/img/hero.webp" />
</section>

<section id="recently-updated" class="recently-updated">
    <div class="section-title">
        <h2>Recently Updated Products</h2>
    </div>
    <div class="products-container">
        <?php if(!empty($recentProducts)): ?>
            <?php foreach ($recentProducts as $product): ?>
                <div class="product">
                    <a href="/product/<?php echo htmlspecialchars($product['productId']); ?>">
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             width="200" height="200" />
                        <div class="product-info">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No recently updated products found.</p>
        <?php endif; ?>
    </div>
</section>

<section class="flash-sales">
    <div class="title">
        <h2><span style="color: #ff4d4f;">Today's</span></h2>
    </div>
    <div class="flash-sales-header">
        <div class="flash-sales-text">Flash Sales</div>
        <div class="timer">
            <div><span id="days">01</span>
                <p>Days</p>
            </div>
            <div><span id="hours">12</span>
                <p>Hours</p>
            </div>
            <div><span id="minutes">32</span>
                <p>Minutes</p>
            </div>
            <div><span id="seconds">48</span>
                <p>Seconds</p>
            </div>
        </div>
    </div>

    <div class="carousel-container">
        <div class="products">
            <?php foreach ($flashSaleProducts as $product): ?>
                <div class="product">
                    <div class="discount">-<?php echo htmlspecialchars($product['discount']); ?>%</div>
                    <i class="far fa-heart wishlistt"></i>
                    <div class="image-container">
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             width="200" height="200" />
                        <div class="add-to-cart">Add To Cart</div>
                    </div>
                    <div class="title-item"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="prices">
                        <div class="price">Rp <?php echo number_format($product['price'] * (100 - $product['discount']) / 100, 0, ',', '.'); ?></div>
                        <div class="original-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                    </div>
                    <div class="rating">
                        <?php 
                        $rating = isset($product['avg_rating']) ? round($product['avg_rating']) : 0;
                        for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-<?php echo $i <= $rating ? 'star' : ($i - 0.5 <= $rating ? 'star-half-alt' : 'star'); ?>"></i>
                        <?php endfor; ?>
                        <span>(<?php echo isset($product['rating_count']) ? (int)$product['rating_count'] : 0; ?>)</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="category-container">
    <div class="container">
        <div class="separator"></div>
        <div class="header-category">
            <div>
                <div class="categories">Categories</div>
                <div class="title">Browse by Category</div>
            </div>
        </div>
        <div class="categories-container">
            <div class="category active">
                <i class="fas fa-mobile-alt"></i>
                <span>Phones</span>
            </div>
            <div class="category">
                <i class="fas fa-desktop"></i>
                <span>Computers</span>
            </div>
            <div class="category">
                <i class="fas fa-clock"></i>
                <span>SmartWatch</span>
            </div>
            <div class="category">
                <i class="fas fa-camera"></i>
                <span>Camera</span>
            </div>
            <div class="category">
                <i class="fas fa-headphones-alt"></i>
                <span>Headphones</span>
            </div>
            <div class="category">
                <i class="fas fa-gamepad"></i>
                <span>Gaming</span>
            </div>
        </div>
        <div class="separator"></div>
    </div>
</section>

<section class="best-selling">
    <div class="header-category">
        <div class="header-left">
            <div class="categories">This Month</div>
            <div class="title">Best Selling Products</div>
        </div>
        <div class="view-all">View All</div>
    </div>
    <div class="products-bs">
        <?php foreach ($bestSelling as $product): ?>
            <div class="product">
                <i class="far fa-heart wishlistt"></i>
                <div class="image-container">
                    <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         width="200" height="200" />
                    <div class="add-to-cart">Add To Cart</div>
                </div>
                <div class="title-item"><?php echo htmlspecialchars($product['name']); ?></div>
                <div class="prices">
                    <div class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                </div>
                <div class="rating">
                    <?php 
                    $rating = isset($product['avg_rating']) ? round($product['avg_rating']) : 0;
                    for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-<?php echo $i <= $rating ? 'star' : ($i - 0.5 <= $rating ? 'star-half-alt' : 'star'); ?>"></i>
                    <?php endfor; ?>
                    <span>(<?php echo isset($product['rating_count']) ? (int)$product['rating_count'] : 0; ?>)</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="our-product-container">
    <div class="our-product-hero">
        <div class="our-product-hero-text">
            <p>Categories</p>
            <h1>Enhance Your Music Experience</h1>
            <div class="our-product-timer">
                <div>
                    <span>23</span>
                    <small>Days</small>
                </div>
                <div>
                    <span>05</span>
                    <small>Hours</small>
                </div>
                <div>
                    <span>59</span>
                    <small>Minutes</small>
                </div>
                <div>
                    <span>35</span>
                    <small>Seconds</small>
                </div>
            </div>
            <button class="our-product-btn">Buy Now!</button>
        </div>
        <img alt="Black portable speaker with red logo" height="100%" src="/assets/img/jbl.png" width="100%" />
    </div>
    <div class="our-product-list-container">
        <div class="header-category">
            <div>
                <div class="categories">Our Products</div>
                <div class="title">Explore Our Products</div>
            </div>
        </div>
        <div class="our-product-list">
            <?php foreach ($exploredProducts as $product): ?>
                <div class="product">
                    <i class="far fa-heart wishlistt"></i>
                    <div class="image-container">
                        <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             width="200" height="200" />
                        <div class="add-to-cart">Add To Cart</div>
                    </div>
                    <div class="title-item"><?php echo htmlspecialchars($product['name']); ?></div>
                    <div class="prices">
                        <div class="price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                    </div>
                    <div class="rating">
                        <?php 
                        $rating = isset($product['avg_rating']) ? round($product['avg_rating']) : 0;
                        for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-<?php echo $i <= $rating ? 'star' : ($i - 0.5 <= $rating ? 'star-half-alt' : 'star'); ?>"></i>
                        <?php endfor; ?>
                        <span>(<?php echo isset($product['rating_count']) ? (int)$product['rating_count'] : 0; ?>)</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>


<div class="container-na">
    <div class="header-category">
        <div>
            <div class="categories">Featured</div>
            <div class="title">New Arrival</div>
        </div>
    </div>
    <div class="products-na">
        <?php foreach ($newArrivals as $index => $product): ?>
            <div class="product-na <?php echo $index === 0 ? 'product-vertical-na' : ($index === 1 ? 'product-horizontal-na' : 'product-square-na'); ?>">
                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div class="product-info-na">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                    <a href="/product/<?php echo htmlspecialchars($product['productId']); ?>">Shop Now</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="features-na">
        <div class="feature-na">
            <i class="fas fa-shipping-fast"></i>
            <h4>FREE AND FAST DELIVERY</h4>
            <p>Free delivery for all orders over $140</p>
        </div>
        <div class="feature-na">
            <i class="fas fa-headset"></i>
            <h4>24/7 CUSTOMER SERVICE</h4>
            <p>Friendly 24/7 customer support</p>
        </div>
        <div class="feature-na">
            <i class="fas fa-undo"></i>
            <h4>MONEY BACK GUARANTEE</h4>
            <p>We return money within 30 days</p>
        </div>
    </div>
</div>