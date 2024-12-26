<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

if (!isset($_SESSION['recover_user_id']) || !isset($_SESSION['reset_password']) || !$_SESSION['reset_password']) {
    header("Location: recover.php");
    exit();
}

$error = '';
$success = '';
$db = Database::getInstance();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $userId = $_SESSION['recover_user_id'];
        
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $userId);
        
        if ($stmt->execute()) {
            $success = "Password successfully reset. You can now login with your new password.";
            unset($_SESSION['recover_user_id']);
            unset($_SESSION['reset_password']);
        } else {
            $error = "An error occurred. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - iniGadget</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo-light.svg" />
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/recover.css">
</head>
<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class="recover-container">
        <div class="recover-box">
            <h1>Reset Password</h1>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="success-message">
                    <?php echo $success; ?>
                    <a href="/login" class="login-link">Go to Login</a>
                </div>
            <?php else: ?>
                <form method="POST" class="recover-form">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required 
                               minlength="8" autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               required minlength="8" autocomplete="new-password">
                    </div>
                    <button type="submit" class="recover-button">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>