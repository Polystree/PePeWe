<?php
include 'database.php';
session_start();

if (!isset($_SESSION['reset_password']) || !isset($_SESSION['recover_user_id'])) {
    header("Location: recover.php");
    exit();
}

$userId = $_SESSION['recover_user_id'];
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword === $confirmPassword) {
        $Password = $newPassword;
        
        $stmt = $connect->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $Password, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $success = "Password reset successful. You can now login with your new password.";
            // Clear session variables
            unset($_SESSION['reset_password']);
            unset($_SESSION['recover_user_id']);
        } else {
            $error = "Failed to reset password. The new password is the same as the old one.";
        }
    } else {
        $error = "Passwords do not match.";
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
</head>
<body>
    <?php include '../header.php'; ?>
    <div class="main">
        <div id="upload-product-title">
            <span>Reset Password</span>
        </div>
        <form method="POST" id="upload-product-form">
            <div class="upload-product-item">
                <div class="credential-form">
                    <label for="new_password" class="upload-label">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div class="credential-form">
                    <label for="confirm_password" class="upload-label">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>
                <button type="submit" class="next-button">Reset Password</button>
            </div>
        </form>
    </div>
    <?php include '../footer.php'; ?>
</body>
</html>