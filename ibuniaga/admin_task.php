<?php
require_once 'config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle task assignment
if (isset($_POST['assign_tasks'])) {
    $moderator_id = sanitize_input($_POST['moderator_id']);
    $selected_apps = $_POST['applications'] ?? [];
    if (!empty($selected_apps)) {
        $stmt = $conn->prepare("UPDATE applications SET assigned_to = ?, status = 'pending' WHERE id = ?");
        foreach ($selected_apps as $app_id) {
            $stmt->bind_param("si", $moderator_id, $app_id);
            $stmt->execute();
        }
    }
}

// Get unassigned applications
$applications = $conn->query("
    SELECT id, name, ic_number, submission_date
    FROM applications
    WHERE assigned_to IS NULL
    ORDER BY submission_date DESC
");

// Get moderators
$moderators = $conn->query("
    SELECT id, username
    FROM users
    WHERE role = 'moderator'
    ORDER BY username
");
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Assignment</title>
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="task-section">
            <h2>Task Assignment</h2>

            <form method="POST" id="assignForm">
                <div class="task-controls">
                    <div class="controls-left">
                        <label>
                            <input type="checkbox" id="select-all">
                            Select All
                        </label>
                        <button type="button" onclick="refreshList()" class="refresh-btn">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                    </div>
                    <div class="controls-right">
                        <div class="moderator-select">
                            <select name="moderator_id" required>
                                <option value="">Select Moderator</option>
                                <?php while ($mod = $moderators->fetch_assoc()): ?>
                                    <option value="<?= $mod['id'] ?>"><?= htmlspecialchars($mod['username']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" name="assign_tasks" class="assign-btn">
                            <i class="fas fa-user-plus"></i> Assign Selected
                        </button>
                    </div>
                </div>

                <div class="applications-list">
                    <?php while ($app = $applications->fetch_assoc()): ?>
                        <div class="application-card">
                            <div class="application-header">
                                <div class="checkbox-container">
                                    <input type="checkbox" name="applications[]" value="<?= $app['id'] ?>">
                                </div>
                                <div class="application-title">
                                    <span><?= htmlspecialchars($app['name']) ?> - <?= htmlspecialchars($app['ic_number']) ?></span>
                                    <span class="username">Submitted: <?= date('Y-m-d', strtotime($app['submission_date'])) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Handle select all functionality
        const selectAllCheckbox = document.getElementById('select-all');
        const applicationCheckboxes = document.getElementsByName('applications[]');

        selectAllCheckbox.addEventListener('change', function() {
            applicationCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Handle form submission
        document.getElementById('assignForm').addEventListener('submit', function(e) {
            const selectedApps = document.querySelectorAll('input[name="applications[]"]:checked');
            const selectedModerator = document.querySelector('select[name="moderator_id"]').value;

            if (selectedApps.length === 0) {
                e.preventDefault();
                alert('Please select at least one application to assign.');
                return;
            }

            if (!selectedModerator) {
                e.preventDefault();
                alert('Please select a moderator.');
                return;
            }
        });

        function refreshList() {
            location.reload();
        }
    </script>
</body>

</html>