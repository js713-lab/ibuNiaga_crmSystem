<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Handle message deletion
if (isset($_POST['delete_message'])) {
    $message_id = sanitize_input($_POST['message_id']);
    $stmt = $conn->prepare("DELETE FROM inbox WHERE id = ? AND user_id = ?");
    $stmt->bind_param("is", $message_id, $_SESSION['user_id']);
    $stmt->execute();
}

// Get user's messages
$messages = $conn->query("
    SELECT id, subject, message, from_type, created_at, is_read
    FROM inbox 
    WHERE user_id = '{$_SESSION['user_id']}'
    ORDER BY created_at DESC
");
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - <?php echo __('site_name'); ?></title>
    <link rel="stylesheet" href="css/user.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>
    
    <div class="content-container">
        <div class="notifications-section">
            <h1>Notifications</h1>
            
            <div class="notifications-list">
                <?php while ($message = $messages->fetch_assoc()): ?>
                    <div class="notification-card">
                        <div class="notification-header" onclick="toggleMessage(<?php echo $message['id']; ?>)">
                            <div class="notification-meta">
                                <span class="from"><?php echo ucfirst($message['from_type']); ?></span>
                                <span class="date"><?php echo date('Y-m-d H:i', strtotime($message['created_at'])); ?></span>
                            </div>
                            <div class="notification-title">
                                <h3><?php echo htmlspecialchars($message['subject']); ?></h3>
                                <?php if (!$message['is_read']): ?>
                                    <span class="unread-badge">New</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="notification-content" id="message-<?php echo $message['id']; ?>">
                            <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            <div class="notification-actions">
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                    <button type="submit" name="delete_message" class="delete-btn">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
    function toggleMessage(id) {
        const content = document.getElementById(`message-${id}`);
        const header = content.previousElementSibling;
        
        content.classList.toggle('show');
        icon.classList.toggle('rotate');
        
        // Mark as read when opened
        if (!content.classList.contains('show')) {
            fetch('mark_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message_id=${id}`
            });
        }
    }
    </script>
</body>
</html>