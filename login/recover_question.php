<?php
include 'database.php';
session_start();

if (!isset($_SESSION['recover_user_id'])) {
    header("Location: recover.php");
    exit();
}

$userId = $_SESSION['recover_user_id'];
$error = '';
$success = '';

$securityQuestions = [
    "What was the name of your first pet?",
    "In which city were you born?",
    "What was your childhood nickname?"
];

// Check if security question and answer are already set
$stmt = $connect->prepare("SELECT security_question, security_answer FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$isQuestionSet = !empty($user['security_question']) && !empty($user['security_answer']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($isQuestionSet) {
        // Verify the answer
        $answer = $_POST['security_answer'];
        if ($answer === $user['security_answer']) {
            $_SESSION['reset_password'] = true;
            header("Location: reset_password.php");
            exit();
        } else {
            $error = "Incorrect answer. Please try again.";
        }
    } else {
        // Set the security question and answer for the first time
        $selectedQuestion = $_POST['security_question'];
        $answer = $_POST['security_answer'];
        
        $stmt = $connect->prepare("UPDATE users SET security_question = ?, security_answer = ? WHERE id = ?");
        $stmt->bind_param("ssi", $selectedQuestion, $answer, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $success = "Security question and answer set successfully.";
            $isQuestionSet = true;
            $user['security_question'] = $selectedQuestion;
        } else {
            $error = "Failed to update security question and answer.";
        }
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
</head>
<body>
    <?php include '../header.php'; ?>
    <div class="main">
        <div id="upload-product-title">
            <span>Security Question</span>
        </div>
        <form method="POST" id="upload-product-form">
            <div class="upload-product-item security-question">
                <?php if (!$isQuestionSet): ?>
                    <div class="credential-form">
                        <label for="security_question" class="upload-label">Select a Security Question</label>
                        <select id="security_question" name="security_question" required>
                            <?php foreach ($securityQuestions as $question): ?>
                                <option value="<?php echo htmlspecialchars($question); ?>"><?php echo htmlspecialchars($question); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <div class="credential-form">
                        <label class="upload-label">Your Security Question</label>
                        <p><?php echo htmlspecialchars($user['security_question']); ?></p>
                    </div>
                <?php endif; ?>
                <div class="credential-form">
                    <label for="security_answer" class="upload-label">Your Answer</label>
                    <input type="text" id="security_answer" name="security_answer" required>
                </div>
                <?php if ($error): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>
                <button type="submit" class="next-button">
                    <?php echo $isQuestionSet ? 'Verify Answer' : 'Set Security Question'; ?>
                </button>
            </div>
        </form>
    </div>
    <?php include '../footer.php'; ?>
</body>
</html>