<?php
require_once 'config.php';

// Check if user is logged in and is a moderator
if (!isLoggedIn() || getUserRole() !== 'moderator') {
    header('Location: login.php');
    exit();
}

// Handle notice submission
if (isset($_POST['send_notice'])) {
    $send_to = sanitize_input($_POST['send_to']);
    $subject = sanitize_input($_POST['subject']);
    $message = sanitize_input($_POST['message']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle file upload
    $file_path = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $file_info = pathinfo($_FILES['attachment']['name']);
        if (isAllowedFile($_FILES['attachment']['name'])) {
            $new_filename = uniqid() . '.' . $file_info['extension'];
            $upload_path = UPLOAD_PATH . '/' . $new_filename;
            
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                $file_path = $new_filename;
            }
        }
    }
    
    if ($send_to === 'index') {
        // Insert into notices table for index.php display
        $stmt = $conn->prepare("
            INSERT INTO notices (title, content, created_by, is_active)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("sssi", $subject, $message, $_SESSION['user_id'], $is_active);
        $stmt->execute();
        
        logActivity($_SESSION['user_id'], 'CREATE_NOTICE', "Created new public notice: $subject");
    } else {
        // Send to specific users based on selection
        $user_query = "SELECT id FROM users WHERE role = 'user'";
        if ($send_to === 'logged_in') {
            $user_query .= " AND last_login IS NOT NULL";
        }
        
        $users = $conn->query($user_query);
        
        // Prepare the inbox insertion statement
        $stmt = $conn->prepare("
            INSERT INTO inbox (user_id, subject, message, attachment, from_type)
            VALUES (?, ?, ?, ?, 'moderator')
        ");
        
        while ($user = $users->fetch_assoc()) {
            $stmt->bind_param("ssss", $user['id'], $subject, $message, $file_path);
            $stmt->execute();
        }
        
        logActivity($_SESSION['user_id'], 'SEND_NOTICE', "Sent notice to " . ($send_to === 'all' ? 'all users' : 'logged in users'));
    }
    
    // Redirect to avoid form resubmission
    header('Location: ' . $_SERVER['PHP_SELF'] . '?success=1');
    exit();
}

// Handle notice deletion
if (isset($_POST['delete_notice'])) {
    $notice_id = sanitize_input($_POST['notice_id']);
    $stmt = $conn->prepare("DELETE FROM notices WHERE id = ? AND created_by = ?");
    $stmt->bind_param("is", $notice_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        logActivity($_SESSION['user_id'], 'DELETE_NOTICE', "Deleted notice ID: $notice_id");
    }
}
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
    <link rel="stylesheet" href="css/moderator/mod_announcement.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="notice-section">
            <h1>Announcement</h1><br>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <p>announcement uploaded successfully</p>
            </div>
            <?php endif; ?>
            
            <div class="notice-tabs">
                <button class="tab-btn active" data-tab="send-notice">Upload</button>
                <button class="tab-btn" data-tab="manage-notices">Manage</button>
            </div>
            
            <div class="tab-content" id="send-notice">
                <form class="notice-form" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="send_to">Category</label>
                        <select id="send_to" name="send_to" required>
                            <option value="index">Homepage banner announcement</option>
                            <option value="all">All user inbox</option>
                            <option value="logged_in">Logged in user inbox</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="6"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="attachment">Attachment (optional)</label>
                        <input type="file" id="attachment" name="attachment">
                        <small>allowed files: JPG, JPEG, PNG, PDF (Max 5MB)</small>
                    </div>

                    <div class="form-group checkbox-group" id="index-options" style="display: none;">
                        <label>
                            <input type="checkbox" name="is_active" checked>
                            <?php echo __('show_on_index'); ?>
                        </label>
                    </div>

                    <button type="submit" name="send_notice" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Upload Announcement
                    </button>
                </form>
            </div>
            
            <div class="tab-content hidden" id="manage-notices">
                <div class="notices-grid">
                    <?php
                    $notices = $conn->query("
                        SELECT n.*, u.username 
                        FROM notices n
                        JOIN users u ON n.created_by = u.id
                        WHERE n.created_by = '{$_SESSION['user_id']}'
                        ORDER BY n.created_at DESC
                    ");
                    while ($notice = $notices->fetch_assoc()):
                    ?>
                    <div class="notice-card">
                        <div class="notice-header">
                            <h3><?php echo htmlspecialchars($notice['title']); ?></h3>
                            <div class="notice-meta">
                                <span class="date"><?php echo formatDate($notice['created_at']); ?></span>
                                <span class="status <?php echo $notice['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $notice['is_active'] ? __('active') : __('inactive'); ?>
                                </span>
                            </div>
                        </div>
                        <div class="notice-content">
                            <?php echo nl2br(htmlspecialchars($notice['content'])); ?>
                        </div>
                        <div class="notice-actions">
                            <form method="POST" class="delete-form" onsubmit="return confirm('<?php echo __('confirm_delete_notice'); ?>');">
                                <input type="hidden" name="notice_id" value="<?php echo $notice['id']; ?>">
                                <button type="submit" name="delete_notice" class="delete-btn">
                                    <i class="fas fa-trash"></i> <?php echo __('delete'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.add('hidden'));
            
            button.classList.add('active');
            document.getElementById(button.dataset.tab).classList.remove('hidden');
        });
    });

    // Show/hide index options based on send_to selection
    document.getElementById('send_to').addEventListener('change', function() {
        document.getElementById('index-options').style.display = 
            this.value === 'index' ? 'block' : 'none';
    });

    // Initialize file input validation
    document.getElementById('attachment').addEventListener('change', function() {
        const maxSize = 5 * 1024 * 1024; // 5MB
        if (this.files[0] && this.files[0].size > maxSize) {
            alert('<?php echo __('file_too_large'); ?>');
            this.value = '';
        }
    });
    </script>
</body>
</html>