<?php
include 'database.php';
session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['identifier'];
    
    $stmt = $connect->prepare("SELECT id, username, email FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['recover_user_id'] = $user['id'];
        header("Location: recover_question.php");
        exit();
    } else {
        $error = "No account found with that username or email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Password - iniGadget</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo-light.svg" />
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../header.php'; ?>
    <div class="main">
        <div id="upload-product-title">
            <span>Recover Password</span>
        </div>
        <form method="POST" id="upload-product-form">
            <div class="upload-product-item">
                <div class="credential-form">
                    <label for="identifier" class="upload-label">Email or Username</label>
                    <input type="text" id="identifier" name="identifier" required>
                </div>
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <button type="submit" class="next-button">Next</button>
            </div>
        </form>
    </div>
    <?php include '../footer.php'; ?>
</body>
</html>