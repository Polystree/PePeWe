<?php include '../account.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <title>iniGadget</title>
    <link rel="icon" type="image/x-icon" href="/assets/img/logo-light.svg" />
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/account.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400&display=swap" />
</head>

<body>
    <?php include '../header.php'; ?>
    <div class="main">
        <form method="POST" class="profile-form" enctype="multipart/form-data">
            <div class="profile-image-container">
                <?php if (isset($userData['profile_image'])): ?>
                <img src="<?php echo htmlspecialchars($userData['profile_image']); ?>" alt="Profile Image" height="400px" />
                <?php else: ?>
                <img src="/assets/img/Generic avatar.svg" alt="Profile Image" height="400px" />
                <?php endif; ?>
            </div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($currentUsername); ?>" required />
            <label for="password">Password:</label>
            <input type="password" id="password" name="password"  />
            <label for="confirm-password">Confirm Password:</label>
            <input type="password" id="confirm-password" name="password"  />
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required />
            <label for="profile_image">Profile Image:</label>
            <input type="file" id="profile_image" name="profile_image" />
            <label for="contact_details">Contact Details:</label>
            <input type="text" id="contact_details" name="contact_details" value="<?php echo htmlspecialchars($userData['contact_details']); ?>" required />
            <label for="address">Shipping Address:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($userData['address']); ?>" required />
            <button type="submit" class="next-button">Update</button>
        </form>
    </div>
    <?php include '../footer.php'; ?>
</body>
</html>
