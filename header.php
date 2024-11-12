<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include 'login/database.php';
$profileImage = '/assets/img/Generic avatar.svg';

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $query = "SELECT profile_image FROM users WHERE username = '$username'";
    $result = mysqli_query($connect, $query);
    $row = mysqli_fetch_assoc($result);
    $profileImage = $row['profile_image'] ?? '/assets/img/Generic avatar.svg';
}
?>

<link rel="stylesheet" href="/assets/css/header.css" />
<input type="checkbox" name="cart-switch" id="cart-switch">
<header>
    <div class="frame">
        <a class="logo" href="/"><img src="/assets/img/logo-landscape.svg" height="100%" alt="logo"></a>
        <input type="checkbox" name="profile" id="profile">
        <div class="header-right">
            <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin'): ?>
                <a href="/product" class="header-item">Products</a>
            <?php endif; ?>
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
            <?php if (isset($_SESSION['username'])): ?>
                <div class="frame1">
                    <label for="cart-switch">
                        <img class="cart-icon" alt="" src="/assets/img/cart.svg" />
                    </label>
                </div>
            <?php endif; ?>
            <?php
            echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
            ?>
            <label for="profile">
                <?php if (isset($userData['profile_image'])): ?>
                    <img class="generic-avatar-icon" alt="Profile Image"
                        src="<?php echo htmlspecialchars($userData['profile_image']); ?>" />
                <?php else: ?>
                    <img class="generic-avatar-icon" alt="Profile Image"
                        src="<?php echo htmlspecialchars($profileImage); ?>" />
                <?php endif; ?>
            </label>
            <div class="profile-dropdown">
                <?php if (!isset($_SESSION['username'])): ?>
                    <a href="/login" class="profile-item" id="profile-login">Login / Register</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="/account" class="profile-item" id="my-account">My Account</a>
                    <a href="/login/logout.php" class="profile-item" id="profile-login">Logout</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>