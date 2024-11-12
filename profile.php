<?php
include 'login/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentUsername = $_SESSION['username'];
    $newUsername = $_POST['username'];
    $newPassword = $_POST['password'];
    $newEmail = $_POST['email'];
    $newProfileImage = $_FILES['profile_image']['name'];

    // Handle file upload
    if (!empty($newProfileImage)) {
        $targetDir = "assets/img/profile/";
        $targetFile = $targetDir . basename($newProfileImage);
        
        // Move the uploaded file to the target directory
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
            // Save the full path of the uploaded image with localhost
            $newProfileImage = "http://localhost/" . $targetFile;
        } else {
            echo "Error uploading file.";
            exit;
        }
    } else {
        // If no new image is uploaded, retain the old image path
        $stmt = $connect->prepare("SELECT profile_image FROM users WHERE username = ?");
        $stmt->bind_param("s", $currentUsername);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        $newProfileImage = $userData['profile_image'];
    }

    // Prepare and execute the update statement
    $stmt = $connect->prepare("UPDATE users SET username = ?, password = ?, email = ?, profile_image = ? WHERE username = ?");
    $stmt->bind_param("sssss", $newUsername, $newPassword, $newEmail, $newProfileImage, $currentUsername);
    
    if ($stmt->execute()) {
        echo "Profile updated successfully.";
        $_SESSION['username'] = $newUsername;
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
    
    $stmt->close();
}

// Fetch user data for the current session
$currentUsername = $_SESSION['username'];
$stmt = $connect->prepare("SELECT email, profile_image FROM users WHERE username = ?");
$stmt->bind_param("s", $currentUsername);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>iniGadget</title>
    <link rel="icon" type="image/x-icon" href="/assets/img/logo-light.svg" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" />
</head>

<body>
    <?php include 'header.php'; ?>
    <div class="main">
        <form method="POST" class="profile-form" enctype="multipart/form-data">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="<?php echo htmlspecialchars($currentUsername); ?>" required />
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required />
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="<?php echo htmlspecialchars($userData['email']); ?>" required />
            <label for="profile_image">Profile Image:</label>
            <input type="file" id="profile_image" name="profile_image" />
            <button type="submit">Update</button>
        </form>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>
