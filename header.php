<?php session_unset();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>

<link rel="stylesheet" href="/assets/css/header.css" />
<header>
    <div class="frame">
        <a class="logo" href="/"><img src="/assets/img/logo-landscape.svg" height="100%" alt="logo"></a>
        <input type="checkbox" name="profile" id="profile">
        <div class="header-right">
            <div class="search-input">
                <form action="/index.php" method="GET" id="header-search">
                    <div class="search">
                        <input type="search" name="query" class="search-text" placeholder="What are you looking for?"
                            value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
                            maxlength="100" />
                        <button type="submit" class="search-button">
                            <img class="search-icon" alt="Search" src="/assets/img/Search.svg" />
                        </button>
                    </div>
                </form>
            </div>
            <div class="frame1">
                <img class="cart-icon" alt="" src="/assets/img/cart.svg" />
            </div>
            <?php
            echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
            ?>
            <label for="profile">
                <img class="generic-avatar-icon" alt="" src="/assets/img/Generic avatar.svg" />
            </label>
            <div class="profile-dropdown">
                <a href="/login" class="profile-item" id="profile-login">Login / Register</a>
                <a href="/profile" class="profile-item" id="my-account">My Account</a>
            </div>
        </div>
    </div>
</header>