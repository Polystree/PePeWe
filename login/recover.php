<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

$error = '';
$db = Database::getInstance();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $db->real_escape_string($_POST['identifier']);
    
    $stmt = $db->prepare("SELECT id, username, security_question, security_answer FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
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
    <?php include '../header.php'; ?>
    <div class="main">
        <div id="upload-product-title">
            <span>Recover Password</span>
        </div>
        <form method="POST" id="upload-product-form">
            <div class="upload-product-item">
                <div class="credential-form">
                    <label for="identifier" class="upload-label">Email or Username:</label>
                    <input type="text" id="identifier" name="identifier" required>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>