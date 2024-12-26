<?php
require_once __DIR__ . '/../includes/Database.php';
session_start();

if (!isset($_SESSION['recover_user_id'])) {
    header("Location: recover.php");
    exit();
}

$db = Database::getInstance();
$userId = $_SESSION['recover_user_id'];
$error = '';
$success = '';

$stmt = $db->prepare("SELECT security_question, security_answer FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (empty($user['security_question']) || empty($user['security_answer'])) {
    $_SESSION['error'] = "Security question not set. Please visit Account Settings to set up your security question.";
    unset($_SESSION['recover_user_id']);
    header("Location: /login");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $answer = strtolower(trim($_POST['security_answer']));
    if (password_verify($answer, $user['security_answer'])) {
        $_SESSION['reset_password'] = true;
        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Incorrect answer. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Question - iniGadget</title>
    <link rel="icon" type="image/x-icon" href="../assets/img/logo-light.svg" />
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/recover.css">
</head>
<body>
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class="recover-container">
        <div class="recover-box">
            <h1>Security Question</h1>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" class="recover-form">
                <div class="form-group">
                    <label>Your Security Question:</label>
                    <div class="security-question"><?php echo htmlspecialchars($user['security_question']); ?></div>
                </div>
                <div class="form-group">
                    <label for="security_answer">Your Answer</label>
                    <input type="text" id="security_answer" name="security_answer" required autocomplete="off">
                </div>
                <button type="submit" class="recover-button">Verify Answer</button>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>