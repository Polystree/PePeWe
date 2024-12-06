<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

$error = '';
$db = Database::getInstance();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $db->real_escape_string($_POST['identifier']);
    
    // First check if the account exists
    $stmt = $db->prepare("SELECT id, username, security_question, security_answer FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Then check if security question is set
        if (empty($user['security_question']) || empty($user['security_answer'])) {
            $error = "Please set up your security question in Account Settings before attempting password recovery.";
        } else {
            $_SESSION['recover_user_id'] = $user['id'];
            header("Location: recover_question.php");
            exit();
        }
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
    <link rel="stylesheet" href="../assets/css/add-product.css">
</head>
<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class="main-content">
        <div class="product-form-container">
            <h1 class="page-title">Recover Password</h1>
            
            <form method="POST" class="product-form">
                <div class="details-section">
                    <div class="form-group">
                        <label for="identifier">Email or Username</label>
                        <input type="text" id="identifier" name="identifier" 
                               placeholder="Enter your email or username" required>
                    </div>
                    <?php if ($error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" 
                                onclick="window.location.href='/login'">Cancel</button>
                        <button type="submit" class="btn btn-primary">Continue</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>