<?php
require_once 'config.php';

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            // Login process
            $username = sanitize_input($_POST['username']);
            $password = $_POST['password'];
            $remember = isset($_POST['remember']);

            if (!isset($_POST['terms']) || $_POST['terms'] !== 'yes') {
                $error = 'You must agree to the terms and conditions and privacy policy';
            } else {
                $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        // Set session variables
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['role'] = $user['role'];

                        // Set remember me cookie if checked
                        if ($remember) {
                            $token = bin2hex(random_bytes(32));
                            $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                            $stmt->bind_param("ss", $token, $user['id']);
                            $stmt->execute();

                            setcookie(REMEMBER_COOKIE_NAME, $token, time() + REMEMBER_COOKIE_DURATION, '/');
                        }

                        // Log activity
                        logActivity($user['id'], 'LOGIN', 'User logged in successfully');

                        // Redirect based on role
                        switch ($user['role']) {
                            case 'admin':
                                header('Location: admin_task.php');
                                break;
                            case 'moderator':
                                header('Location: mod_task.php');
                                break;
                            default:
                                header('Location: index.php');
                        }
                        exit();
                    } else {
                        $error = 'Invalid username or password';
                    }
                } else {
                    $error = 'Invalid username or password';
                }
            }
        } elseif ($_POST['action'] === 'register') {
            // Registration process
            $username = sanitize_input($_POST['username']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if (!isset($_POST['terms']) || $_POST['terms'] !== 'yes') {
                $error = 'You must agree to the terms and conditions and privacy policy';
            } elseif ($password !== $confirm_password) {
                $error = 'Passwords do not match';
            } else {
                // Check if username exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->bind_param("s", $username);
                $stmt->execute();

                if ($stmt->get_result()->num_rows > 0) {
                    $error = 'Username already exists';
                } else {
                    // Create new user
                    $user_id = generateUniqueID('U', 'users', 'id');
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("INSERT INTO users (id, username, password, role) VALUES (?, ?, ?, 'user')");
                    $stmt->bind_param("sss", $user_id, $username, $hashed_password);

                    if ($stmt->execute()) {
                        $success = 'Registration successful! Please login.';
                        logActivity($user_id, 'REGISTER', 'New user registration');
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Register</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="auth-container">
            <!-- Error/Success Messages -->
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <!-- Auth Tabs -->
            <div class="auth-tabs">
                <div class="auth-tab active" data-form="login">Login</div>
                <div class="auth-tab" data-form="register">Register</div>
            </div>

            <!-- Login Form -->
            <form class="auth-form active" id="loginForm" method="POST" action="">
                <input type="hidden" name="action" value="login">

                <div class="form-group">
                    <label for="login-username">Username</label>
                    <input type="text" id="login-username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>

                <div class="terms-group">
                    <input type="checkbox" id="login-terms" name="terms" value="yes">
                    <label for="login-terms">
                        I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a> and
                        <a href="privacy.php" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="submit-btn">Login</button>
            </form>

            <!-- Register Form -->
            <form class="auth-form" id="registerForm" method="POST" action="">
                <input type="hidden" name="action" value="register">

                <div class="form-group">
                    <label for="reg-username">Username</label>
                    <input type="text" id="reg-username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="reg-password">Password</label>
                    <input type="password" id="reg-password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="reg-confirm-password">Confirm Password</label>
                    <input type="password" id="reg-confirm-password" name="confirm_password" required>
                </div>

                <div class="terms-group">
                    <input type="checkbox" id="reg-terms" name="terms" value="yes">
                    <label for="reg-terms">
                        I agree to the <a href="terms.php" target="_blank">Terms & Conditions</a> and
                        <a href="privacy.php" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="submit-btn">Register</button>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Tab switching functionality
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update tabs
                document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Update forms
                document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
                document.getElementById(this.dataset.form + 'Form').classList.add('active');
            });
        });

        // Password confirmation validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('reg-password').value;
            const confirmPassword = document.getElementById('reg-confirm-password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>

</html>