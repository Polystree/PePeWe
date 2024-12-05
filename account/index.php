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
    <?php include __DIR__ . '/../templates/header.php'; ?>
    <div class="main-content">
        <div class="profile-container">
            <h1 class="page-title">Account Settings</h1>
            
            <form method="POST" class="profile-form" enctype="multipart/form-data">
                <div class="form-grid">
                    <!-- Image Section -->
                    <div class="image-section">
                        <div class="profile-image-preview">
                            <?php if (isset($userData['profile_image'])): ?>
                                <img src="<?php echo htmlspecialchars($userData['profile_image']); ?>" alt="Profile Image">
                            <?php else: ?>
                                <img src="/assets/img/Generic avatar.svg" alt="Profile Image">
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <input type="file" name="profile_image" id="profile_image" accept="image/*" class="file-input">
                            <label for="profile_image" class="btn-upload">Change Profile Picture</label>
                        </div>
                    </div>

                    <!-- Details Section -->
                    <div class="details-section">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" 
                                   placeholder="<?php echo htmlspecialchars($currentUsername); ?>"
                                   value="<?php echo htmlspecialchars($currentUsername); ?>" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">New Password</label>
                                <input type="password" id="password" name="password"
                                       placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label for="confirm-password">Confirm Password</label>
                                <input type="password" id="confirm-password" name="confirm_password"
                                       placeholder="Confirm new password">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                   placeholder="Enter your email address"
                                   value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="contact_details">Contact Details</label>
                            <input type="text" id="contact_details" name="contact_details"
                                   placeholder="Enter your contact number"
                                   value="<?php echo htmlspecialchars($userData['contact_details'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="address">Shipping Address</label>
                            <input type="text" id="address" name="address"
                                   placeholder="Enter your shipping address"
                                   value="<?php echo htmlspecialchars($userData['address'] ?? ''); ?>" required>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='/'">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php include __DIR__ . '/../templates/footer.php'; ?>

    <script>
        // Profile image preview
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const preview = document.querySelector('.profile-image-preview img');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
