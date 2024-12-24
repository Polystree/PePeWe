<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

if (!isset($_SESSION['reset_password']) || !isset($_SESSION['recover_user_id'])) {
    header("Location: recover.php");
    exit();
}

$db = Database::getInstance();
$userId = $_SESSION['recover_user_id'];
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword === $confirmPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $success = "Password reset successful. You can now login with your new password.";
            unset($_SESSION['reset_password']);
            unset($_SESSION['recover_user_id']);
        } else {
            $error = "Failed to reset password.";
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
    <link rel="stylesheet" href="../assets/css/add-product.css">
</head>
<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class="main-content">
        <div class="product-form-container">
            <h1 class="page-title">Reset Password</h1>
            
            <form method="POST" class="product-form">
                <div class="details-section">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" 
                                   placeholder="Enter new password" required>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirm new password" required>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <p class="success"><?php echo $success; ?></p>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" 
                                onclick="window.location.href='recover_question.php'">Back</button>
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </div>
            </form>
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
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>