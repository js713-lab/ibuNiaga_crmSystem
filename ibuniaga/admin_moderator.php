<?php
require_once 'config.php';

// Security check
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle moderator creation
if (isset($_POST['add_moderator'])) {
    $username = sanitize_input($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    if ($stmt->get_result()->num_rows > 0) {
        $error = 'Username already exists';
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'moderator')");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            logActivity($_SESSION['user_id'], 'CREATE_MODERATOR', "Created moderator: $username");
            $success = 'Moderator account created successfully';
        } else {
            $error = 'Failed to create moderator account';
        }
    }
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = sanitize_input($_POST['user_id']);

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);

    if ($stmt->execute()) {
        logActivity($_SESSION['user_id'], 'DELETE_USER', "Deleted user: $user_id");
        $success = 'User removed successfully';
    } else {
        $error = 'Failed to remove user';
    }
}

// Get moderators and users
$moderators = $conn->query("SELECT id, username, joined_date FROM users WHERE role = 'moderator' ORDER BY joined_date DESC");
$users = $conn->query("SELECT id, username, joined_date FROM users WHERE role = 'user' ORDER BY joined_date DESC");
?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <style>
        /* .admin-table {
            margin-bottom: 1.5rem;
        } */

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn i {
            font-size: 0.875rem;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="admin-container">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Create Moderator Form -->
            <div class="admin-card">
                <div class="admin-header">
                    <h2>Create Moderator Account</h2>
                </div>
                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" name="add_moderator" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Create Moderator
                    </button>
                </form>
            </div>

            <!-- Moderators List -->
            <div class="admin-card">
                <div class="admin-header">
                    <h2>Moderators</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($mod = $moderators->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($mod['id']) ?></td>
                                <td><?= htmlspecialchars($mod['username']) ?></td>
                                <td><?= date('Y-m-d', strtotime($mod['joined_date'])) ?></td>
                                <td>
                                    <form method="POST" style="display:inline"
                                        onsubmit="return confirm('Are you sure you want to remove this moderator?');">
                                        <input type="hidden" name="user_id" value="<?= $mod['id'] ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Users List -->
            <div class="admin-card">
                <div class="admin-header">
                    <h2>Users</h2>
                </div>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= date('Y-m-d', strtotime($user['joined_date'])) ?></td>
                                <td>
                                    <form method="POST" style="display:inline"
                                        onsubmit="return confirm('Are you sure you want to remove this user?');">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>