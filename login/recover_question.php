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
    <link rel="stylesheet" href="../assets/css/add-product.css">
</head>
<body>
<<<<<<< HEAD
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class="main-content">
        <div class="product-form-container">
            <h1 class="page-title">Security Question</h1>
            
            <form method="POST" class="product-form">
                <div class="details-section">
                    <div class="form-group">
                        <label>Your Security Question</label>
                        <div class="readonly-text">
                            <?php echo htmlspecialchars($user['security_question']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="security_answer">Your Answer</label>
                        <input type="text" id="security_answer" name="security_answer" 
                               placeholder="Enter your answer" required>
                    </div>

                    <?php if ($error): ?>
                        <p class="error"><?php echo $error; ?></p>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <p class="success"><?php echo $success; ?></p>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" 
                                onclick="window.location.href='recover.php'">Back</button>
                        <button type="submit" class="btn btn-primary">
                            Verify Answer
                        </button>
                    </div>
=======
    <?php include '../header.php'; ?>
    <div class="main">
        <div id="upload-product-title">
            <span>Security Question</span>
        </div>
        <form method="POST" id="upload-product-form">
            <div class="upload-product-item">
                <?php if (!$isQuestionSet): ?>
                    <div class="credential-form">
                        <label for="security_question" class="upload-label">Select a Security Question:</label>
                        <select id="security_question" name="security_question" required>
                            <?php foreach ($securityQuestions as $question): ?>
                                <option value="<?php echo htmlspecialchars($question); ?>"><?php echo htmlspecialchars($question); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="credential-form">
                        <label class="upload-label">Your Security Question:</label>
                        <p><?php echo htmlspecialchars($user['security_question']); ?></p>
                    </div>
                <?php endif; ?>
                <div class="credential-form">
                    <label for="security_answer" class="upload-label">Your Answer:</label>
                    <input type="text" id="security_answer" name="security_answer" required>
>>>>>>> 39a9da2ee0140380fe74f6faf86f6643403945cc
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>
</body>
</html>