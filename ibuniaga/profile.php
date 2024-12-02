<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = sanitize_input($_POST['username']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Get current user data
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("s", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $updates = array();
                $params = array();
                $types = "";
                
                if ($username !== $_SESSION['username']) {
                    $updates[] = "username = ?";
                    $params[] = $username;
                    $types .= "s";
                }
                
                if (!empty($new_password)) {
                    $updates[] = "password = ?";
                    $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                    $types .= "s";
                }
                
                if (!empty($updates)) {
                    $params[] = $_SESSION['user_id'];
                    $types .= "s";
                    
                    $sql = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param($types, ...$params);
                    
                    if ($stmt->execute()) {
                        if ($username !== $_SESSION['username']) {
                            $_SESSION['username'] = $username;
                        }
                        $success = 'Profile updated successfully';
                        logActivity($_SESSION['user_id'], 'PROFILE_UPDATE', 'Profile information updated');
                    } else {
                        $error = 'Failed to update profile';
                    }
                }
            } else {
                $error = 'New passwords do not match';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
    
    // Handle profile image upload
    if (isset($_FILES['profile_image'])) {
        $file = $_FILES['profile_image'];
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $upload_dir = 'uploads/profiles/';
            $filename = $_SESSION['user_id'] . '.jpg';
            
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $success = 'Profile image updated successfully';
                logActivity($_SESSION['user_id'], 'PROFILE_IMAGE_UPDATE', 'Profile image updated');
            } else {
                $error = 'Failed to upload profile image';
            }
        } else {
            $error = 'Invalid file type or size';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo __('site_name'); ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>
    
    <div class="content-container">
        <div class="profile-container">
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="profile-card">
                <div class="profile-header">
                    <h2>Profile Settings</h2>
                    <div class="profile-image-section">
                        <img src="<?php echo 'uploads/profiles/' . $_SESSION['user_id'] . '.jpg'; ?>" 
                             alt="Profile Image" 
                             class="profile-image"
                             onerror="this.src='./img/profile.jpeg'">
                        <form method="POST" enctype="multipart/form-data" class="image-upload-form">
                            <label for="profile_image" class="upload-label">
                                <i class="fas fa-camera"></i>
                                Change Photo
                            </label>
                            <input type="file" id="profile_image" name="profile_image" accept="image/*" class="file-input">
                        </form>
                    </div>
                </div>
                
                <form method="POST" class="profile-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password (leave blank to keep current)</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                    
                    <button type="submit" name="update_profile" class="submit-btn">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Auto-submit form when image is selected
        document.getElementById('profile_image').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                this.closest('form').submit();
            }
        });
        
        // Password confirmation validation
        document.querySelector('.profile-form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
            }
        });
    </script>
</body>
</html>