<?php
include 'login/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentUsername = $_SESSION['username'];
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password'];
    $newEmail = $_POST['email'];
    $newProfileImage = preg_replace("/[^a-zA-Z0-9.\-_]/", "", $_FILES['profile_image']['name']);
    $newContactDetails = $_POST['contact_details'];
    $newAddress = $_POST['address'];

    // Check if the new username or email already exists
    $stmtCheck = $connect->prepare("SELECT username, email FROM users WHERE (username = ? OR email = ?) AND username != ?");
    $stmtCheck->bind_param("sss", $newUsername, $newEmail, $currentUsername);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        echo "Username or email already in use.";
        exit;
    }

    if (!empty($newProfileImage)) {
        $targetDir = __DIR__ . "/assets/img/profile/";
        $targetFile = $targetDir . basename($newProfileImage);

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            $newProfileImage = "http://localhost/" . "assets/img/profile/" . basename($newProfileImage);
        } else {
            echo "Error uploading file.";
            exit;
        }
    } else {
        $stmt = $connect->prepare("SELECT profile_image FROM users WHERE username = ?");
        $stmt->bind_param("s", $currentUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $newProfileImage = $userData['profile_image'];
    }

    $stmt = $connect->prepare("UPDATE users SET username = ?, email = ?, profile_image = ?, contact_details = ?, address = ? WHERE username = ?");
    $stmt->bind_param("ssssss", $newUsername, $newEmail, $newProfileImage, $newContactDetails, $newAddress, $currentUsername);

    if (!empty($newPassword)) {
        $stmtPassword = $connect->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmtPassword->bind_param("ss", $newPassword, $currentUsername);
        $stmtPassword->execute();
        $stmtPassword->close();
    }

    if ($stmt->execute()) {
        $_SESSION['username'] = $newUsername;
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
}

$currentUsername = $_SESSION['username'];
$stmt = $connect->prepare("SELECT email, profile_image, contact_details, address FROM users WHERE username = ?");
$stmt->bind_param("s", $currentUsername);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
?>
