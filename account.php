<?php
// Get database configuration
$config = include(__DIR__ . '/config/config.php');
$db_config = $config['db'];

// Create connection using config values
$connect = new mysqli(
    $db_config['host'],
    $db_config['username'],
    $db_config['password'],
    $db_config['database']
);

// Check connection
if ($connect->connect_error) {
    error_log("Database connection failed: " . $connect->connect_error);
    die("Connection failed. Please try again later.");
}

// Add this after the database connection code
$profile_image_dir = __DIR__ . '/assets/img/profile';
if (!is_dir($profile_image_dir)) {
    mkdir($profile_image_dir, 0777, true);
}

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentUsername = $_SESSION['username'];
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password'];
    $newEmail = $_POST['email'];
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

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $random_number = mt_rand(1000, 9999);
        $safe_username = preg_replace('/[^a-z0-9]+/', '-', strtolower($newUsername));
        $image_filename = $random_number . '-' . $safe_username . '.' . $file_extension;
        $image_path = '/assets/img/profile/' . $image_filename;
        $upload_path = $profile_image_dir . '/' . $image_filename;

        // Delete old image if exists
        $stmt = $connect->prepare("SELECT profile_image FROM users WHERE username = ?");
        $stmt->bind_param("s", $currentUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        $oldData = $result->fetch_assoc();
        
        if (!empty($oldData['profile_image'])) {
            $old_image = __DIR__ . str_replace('http://localhost', '', $oldData['profile_image']);
            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
            $newProfileImage = 'http://localhost' . $image_path;
        } else {
            error_log("Failed to move uploaded file to $upload_path");
            die("Failed to upload image. Please try again.");
        }
    } else {
        // Keep existing profile image
        $stmt = $connect->prepare("SELECT profile_image FROM users WHERE username = ?");
        $stmt->bind_param("s", $currentUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $newProfileImage = $userData['profile_image'];
    }

    // Update user data
    $stmt = $connect->prepare("UPDATE users SET username = ?, email = ?, profile_image = ?, contact_details = ?, address = ? WHERE username = ?");
    $stmt->bind_param("ssssss", $newUsername, $newEmail, $newProfileImage, $newContactDetails, $newAddress, $currentUsername);

    if (!empty($newPassword)) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmtPassword = $connect->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmtPassword->bind_param("ss", $hashedPassword, $currentUsername);
        $stmtPassword->execute();
        $stmtPassword->close();
    }

    if ($stmt->execute()) {
        $_SESSION['username'] = $newUsername;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } else {
        error_log("Error updating profile: " . $stmt->error);
        die("Failed to update profile. Please try again.");
    }

    $stmt->close();
}

// Get current user data
$currentUsername = $_SESSION['username'];
$stmt = $connect->prepare("SELECT email, profile_image, contact_details, address FROM users WHERE username = ?");
$stmt->bind_param("s", $currentUsername);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

$connect->close();
?>
