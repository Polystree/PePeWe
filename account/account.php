<?php
$conn = new mysqli('localhost', 'root', '', 'ecommerce_v3');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();

$securityQuestions = [
    "What was the name of your first pet?",
    "In which city were you born?",
    "What was your childhood nickname?",
    "What is your mother's maiden name?",
    "What was the name of your first school?"
];

$currentSecurityQuestion = '';
$currentSecurityAnswer = '';

$currentUsername = $_SESSION['username'];
$stmt = $conn->prepare("SELECT email, profile_image, contact_details, security_question, security_answer FROM users WHERE username = ?");
$stmt->bind_param("s", $currentUsername);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

$stmt = $conn->prepare("
    SELECT * FROM user_addresses 
    WHERE user_id = (SELECT id FROM users WHERE username = ?) 
    AND is_default = 1 
    LIMIT 1
");
$stmt->bind_param("s", $currentUsername);
$stmt->execute();
$addressResult = $stmt->get_result();
$defaultAddress = $addressResult->fetch_assoc();

$currentEmail = $userData['email'];
$currentContact = $userData['contact_details'];
$currentProfileImage = !empty($userData['profile_image']) ? $userData['profile_image'] : '/assets/img/Generic avatar.svg';
$currentSecurityQuestion = $userData['security_question'];
$currentSecurityAnswer = $userData['security_answer'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentUsername = $_SESSION['username'];
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password'];
    $newEmail = $_POST['email'];
    $newContactDetails = $_POST['contact_details'];
    $newAddress = $_POST['address'];
    
    function deleteOldProfileImage($imagePath) {
        if ($imagePath && $imagePath !== '/assets/img/Generic avatar.svg') {
            $fullPath = dirname(__DIR__) . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    $newProfileImage = null;
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['size'] > 0) {
        $targetDir = dirname(__DIR__) . "/assets/img/profile/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowedTypes)) {
            die("Error: Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.");
        }

        $randomNumber = mt_rand(1000, 9999);
        $newFileName = $randomNumber . '-' . $currentUsername . '.' . $extension;
        $targetFile = $targetDir . $newFileName;
        
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            deleteOldProfileImage($userData['profile_image']);
            $newProfileImage = "/assets/img/profile/" . $newFileName;
        } else {
            die("Error: Failed to upload image.");
        }
    }

    $stmtCheck = $conn->prepare("SELECT username, email FROM users WHERE (username = ? OR email = ?) AND username != ?");
    $stmtCheck->bind_param("sss", $newUsername, $newEmail, $currentUsername);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo "Username or email already in use.";
        exit;
    }

    $securityQuestion = $_POST['security_question'];
    $securityAnswer = $_POST['security_answer'];
    
    if (!empty($securityAnswer)) {
        $hashedAnswer = password_hash(strtolower(trim($securityAnswer)), PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET security_question = ?, security_answer = ? WHERE username = ?");
        $stmt->bind_param("sss", $securityQuestion, $hashedAnswer, $currentUsername);
        $stmt->execute();
    } elseif (!empty($securityQuestion) && $securityQuestion !== $currentSecurityQuestion) {
        $stmt = $conn->prepare("UPDATE users SET security_question = ? WHERE username = ?");
        $stmt->bind_param("ss", $securityQuestion, $currentUsername);
        $stmt->execute();
    }

    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, contact_details = ? WHERE username = ?");
    $stmt->bind_param("ssss", $newUsername, $newEmail, $newContactDetails, $currentUsername);

    if ($newProfileImage) {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_image = ?, contact_details = ? WHERE username = ?");
        $stmt->bind_param("sssss", $newUsername, $newEmail, $newProfileImage, $newContactDetails, $currentUsername);
    } else {
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, contact_details = ? WHERE username = ?");
        $stmt->bind_param("ssss", $newUsername, $newEmail, $newContactDetails, $currentUsername);
    }

    if (!empty($newPassword)) {
        $stmtPassword = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmtPassword->bind_param("ss", $newPassword, $currentUsername);
        $stmtPassword->execute();
        $stmtPassword->close();
    }

    if ($stmt->execute()) {
        $_SESSION['username'] = $newUsername;
        header("Location: /account");
        exit();
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}

$securityNotice = '';
if (empty($currentSecurityQuestion) || empty($currentSecurityAnswer)) {
    $securityNotice = '<div class="alert alert-warning">
        <strong>Important:</strong> Please set up your security question to enable password recovery.
    </div>';
}
?>