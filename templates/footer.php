<link rel="stylesheet" href="/assets/css/footer.css" />
<footer>
    <div class="footer">
        <div class="logo-parent">
            <div class="footer-links">
                <a href="/" class="logo"><img src="/assets/img/logo-landscape-light.svg" height="60px" alt="logo"></a>
                <div class="subscribe">Subscribe</div>
                <div class="get-10-your">Get 10% off your first order</div>
                <div class="email-footer">
                    <div class="email">
                        <input type="email" class="email-input" placeholder="Enter your email" />
                        <input type="submit" name="email-submit" />
                        <label for="email-submit"><img class="email-icon" alt="" src="/assets/img/send.svg" /></label>
                    </div>
                </div>
                <?php if (isset($_POST['email-submit'])): ?>
                    <?php
                    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
                    if ($email) {
                        $stmt = $db->prepare("INSERT INTO newsletter (email) VALUES (?) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP");
                        $stmt->bind_param("s", $email);
                        $stmt->execute();
                        echo '<div class="success-message">Thank you for subscribing!</div>';
                    }
                    ?>
                <?php endif; ?>
            </div>
            <div class="footer-links">
                <div class="support">Support</div>
                <a href="https://maps.google.com" target="_blank" class="fanum-v-blok">Fanum V Blok A, 6969 Ohio
                    City</a>
                <a class="sigmaskibidigmailcom" href="mailto:sigmaskibidi@gmail.com"
                    target="_blank">sigmaskibidi@gmail.com</a>
                <div class="div">+1 312 123 422</div>
            </div>
            <div class="footer-links">
                <div class="account">Account</div>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/account" class="my-account">My Account</a>
                    <a href="/login/logout.php" class="login-register">Logout</a>
                <?php else: ?>
                    <a href="/login" class="login-register">Login / Register</a>
                <?php endif; ?>
                <a href="/cart" class="cart">Cart</a>
                <a href="/wishlist" class="wishlist">Wishlist</a>
                <a href="/shop" class="shop">Shop</a>
            </div>
            <div class="footer-links">
                <div class="quick-link">Quick Link</div>
                <a href="" class="privacy-policy">Privacy Policy</a>
                <a href="" class="terms-of-use">Terms Of Use</a>
                <a href="" class="faq">FAQ</a>
                <a href="" class="contact">Contact</a>
            </div>
            <div class="footer-links">
                <div class="download-app">Download App</div>
                <div class="save-rp9999-with-app-new-user-parent">
                    <!-- Promo banner -->
                    <div class="save-rp9999-with" role="banner">
                        <span class="discount-text">Save Rp9.999</span>
                        <span class="new-user-badge">New User Only</span>
                    </div>
                    
                    <!-- Download options -->
                    <div class="qr-code-parent">
                        <img class="qr-code-icon" alt="QR Code to download app" src="/assets/img/qr-code.png" />
                        <div class="store-parent">
                            <a href="market://details?id=com.miHoYo.GenshinImpact" aria-label="Download from Google Play Store">
                                <img class="googleplay-icon" alt="Get it on Google Play" src="/assets/img/download-playstore.png" />
                            </a>
                            <a href="itms-apps://apps.apple.com/app/id1517783697" aria-label="Download from App Store">
                                <img class="appstore-icon" alt="Download on the App Store" src="/assets/img/download-appstore.png" />
                            </a>
                        </div>
                    </div>
                    
                    <!-- Social media links -->
                    <div class="social-media-parent" role="navigation" aria-label="Social media links">
                        <a href="">
                            <img class="social-media" alt="" src="/assets/img/Icon-Facebook.svg" />
                        </a>
                        <a href="">
                            <img class="social-media" alt="" src="/assets/img/Icon-Twitter.svg" />
                        </a>
                        <a href="">
                            <img class="social-media" alt="" src="/assets/img/icon-instagram.svg" />
                        </a>
                        <a href="">
                            <img class="social-media" alt="" src="/assets/img/Icon-Linkedin.svg" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright-team-3">
        ©️ Copyright Team 3 <?php echo date('Y'); ?>. All right reserved
    </div>
</footer>

<script>
document.querySelector('.email-footer form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = e.target.querySelector('input[type="email"]').value;
    
    try {
        const response = await fetch('/api/newsletter-signup.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email })
        });
        
        if (response.ok) {
            e.target.innerHTML = '<div class="success-message">Thank you for subscribing!</div>';
        }
    } catch (err) {
        console.error('Newsletter signup failed:', err);
    }
});
</script>