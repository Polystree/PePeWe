<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/Database.php';
$db = Database::getInstance();
$profileImage = '/assets/img/Generic avatar.svg';

if (isset($_SESSION['username'])) {
    $username = $db->real_escape_string($_SESSION['username']);
    $stmt = $db->prepare("SELECT id, profile_image FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $_SESSION['userId'] = $row['id'] ?? null;
    $profileImage = $row['profile_image'] ?? '/assets/img/Generic avatar.svg';
}

// Replace the current page detection with this
$current_uri = $_SERVER['REQUEST_URI'];
$is_login_page = (strpos($current_uri, '/login') === 0);
?>

<link rel="stylesheet" href="/assets/css/header.css" />
<input type="checkbox" name="cart-switch" id="cart-switch">
<header>
    <div class="frame">
        <a class="logo" href="/"><img src="/assets/img/logo-landscape.svg" height="100%" alt="logo"></a>
        <input type="checkbox" name="profile" id="profile">
        <div class="header-right">

            <?php if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin'): ?>
                <a href="/admin" class="header-item">Admin</a>
            <?php endif; ?>

            <?php if (!$is_login_page): ?>
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
            <?php endif; ?>

            <?php if (isset($_SESSION['username']) && $_SESSION['username'] !== 'admin'): ?>
                <div class="frame1">
                    <label for="cart-switch">
                        <img class="cart-icon" alt="" src="/assets/img/cart.svg" />
                    </label>
                </div>
            <?php endif; ?>

            <?php if (!$is_login_page): ?>
                <?php
                echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';
                $_SESSION['userId'] = $row['id'] ?? null;
                ?>
            <?php endif; ?>

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
