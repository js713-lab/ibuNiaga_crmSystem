<?php
require_once 'config.php';

// Check if user is logged in and is a moderator
if (!isLoggedIn() || getUserRole() !== 'moderator') {
    header('Location: login.php');
    exit();
}

// Handle application status updates
if (isset($_POST['update_status']) && isset($_POST['application_id'])) {
    $application_id = sanitize_input($_POST['application_id']);
    $status = sanitize_input($_POST['update_status']);
    $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
    
    // Update application status
    $stmt = $conn->prepare("
        UPDATE applications 
        SET status = ?,
            status_message = ?,
            status_changed_date = NOW(),
            updated_by = ?
        WHERE id = ? AND assigned_to = ?
    ");
    $stmt->bind_param("sssss", $status, $message, $_SESSION['user_id'], $application_id, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // Log the activity
        logActivity($_SESSION['user_id'], 'UPDATE_APPLICATION', "Updated application $application_id to $status");
        
        // Send notification to user
        $stmt = $conn->prepare("
            INSERT INTO inbox (user_id, subject, message, from_type)
            SELECT user_id, 
                   ?, 
                   ?,
                   'moderator'
            FROM applications 
            WHERE id = ?
        ");
        $subject = "Application " . ucfirst($status);
        $stmt->bind_param("sss", $subject, $message, $application_id);
        $stmt->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderator Tasks - <?php echo __('site_name'); ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/moderator/mod_task.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>
    
    <div class="content-container">
        <div class="task-section">
            <h1>Assigned tasks</h1>
            
            <div class="task-controls">
                <label>
                    <input type="checkbox" id="select-all">select all
                </label>
                <button onclick="refreshList()" class="refresh-btn">
                    <i class="fas fa-sync"></i>refresh
                </button>
                <button onclick="bulkApprove()" class="approve-btn">
                    <i class="fas fa-check"></i>approve selected
                </button>
                <button onclick="bulkReject()" class="reject-btn">
                    <i class="fas fa-times"></i>reject selected
                </button>
            </div>
            <div class="applications-list">
                <?php
                $applications = $conn->query("
                    SELECT a.*, u.username
                    FROM applications a
                    JOIN users u ON a.user_id = u.id
                    WHERE a.assigned_to = '{$_SESSION['user_id']}'
                    AND a.status = 'pending'
                    ORDER BY a.submission_date DESC
                ");
                while ($app = $applications->fetch_assoc()):
                ?>
                <div class="application-card">
                    <div class="application-header">
                        <label class="checkbox-container">
                            <input type="checkbox" name="selected_apps[]" value="<?php echo $app['id']; ?>">
                        </label>
                        <div class="application-title" onclick="toggleDetails(<?php echo $app['id']; ?>)">
                            <?php echo htmlspecialchars($app['name']); ?> - <?php echo htmlspecialchars($app['ic_number']); ?>
                            <span class="username">from: <?php echo htmlspecialchars($app['username']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                    
                    <div class="application-details" id="details-<?php echo $app['id']; ?>">
                        <div class="details-grid">
                            <div class="detail-item">
                                <strong>Contact: </strong> <?php echo htmlspecialchars($app['contact']); ?>
                            </div>
                            <div class="detail-item">
                                <strong>Email: </strong> <?php echo htmlspecialchars($app['email']); ?>
                            </div>
                            <div class="detail-item">
                                <strong>Business type: </strong> <?php echo __(htmlspecialchars($app['business_type'])); ?>
                            </div>
                            <div class="detail-item">
                                <strong>Business duration: </strong> <?php echo __(str_replace('_', ' ', $app['business_duration'])); ?>
                            </div>
                            <div class="detail-item">
                                <strong>Submission date: </strong> <?php echo formatDate($app['submission_date']); ?>
                            </div>
                        </div>
                        
                        <div class="action-form">
                            <form method="POST" class="approve-reject-form">
                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                <div class="form-group">
                                    <label>Message (optional): </label>
                                    <textarea name="message" required class="message-input"></textarea>
                                </div>
                                <div class="action-buttons">
                                    <button type="submit" name="update_status" value="approved" class="approve-btn">
                                        <i class="fas fa-check"></i> <?php echo __('approve'); ?>
                                    </button>
                                    <button type="submit" name="update_status" value="rejected" class="reject-btn">
                                        <i class="fas fa-times"></i> <?php echo __('reject'); ?>
                                    </button>
                                </div>
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
    function toggleDetails(id) {
        document.getElementById(`details-${id}`).classList.toggle('show');
    }

    document.getElementById('select-all').addEventListener('change', function() {
        document.querySelectorAll('input[name="selected_apps[]"]')
            .forEach(checkbox => checkbox.checked = this.checked);
    });

    function refreshList() {
        location.reload();
    }

    function bulkApprove() {
        processSelected('approved');
    }

    function bulkReject() {
        processSelected('rejected');
    }

    function processSelected(status) {
        const selected = document.querySelectorAll('input[name="selected_apps[]"]:checked');
        if (selected.length === 0) {
            alert('Please select at least one application.');
            return;
        }

        const message = prompt(`Enter message for ${status} applications:`);
        if (message === null || message.trim() === '') return;

        const promises = Array.from(selected).map(checkbox => {
            const form = new FormData();
            form.append('application_id', checkbox.value);
            form.append('update_status', status);
            form.append('message', message);
            
            return fetch('mod_task.php', {
                method: 'POST',
                body: form
            }).then(response => {
                if (response.ok) {
                    checkbox.closest('.application-card').remove();
                }
            });
        });
        Promise.all(promises).then(() => {
            location.reload();
        });
    }

    // Close expandable sections when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.application-card')) {
            document.querySelectorAll('.application-details').forEach(el => {
                el.classList.remove('show');
            });
        }
    });

    // Prevent form submission if message is empty
    document.querySelectorAll('.approve-reject-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = this.querySelector('.message-input').value.trim();
            if (!message) {
                e.preventDefault();
                alert('Please enter a message for the applicant.');
            }
        });
    });
    </script>
</body>
</html>