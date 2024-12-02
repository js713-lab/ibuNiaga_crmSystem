<?php
require_once 'config.php';

// Get the most recent active notice
$active_notice = $conn->query("
    SELECT n.*, u.username
    FROM notices n 
    JOIN users u ON n.created_by = u.id
    WHERE n.is_active = 1 
    ORDER BY n.created_at DESC 
    LIMIT 1
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <!-- Notification Banner -->
        <div class="notification-banner" id="notificationBanner">
            <div class="notification-content">
                <p><?php echo __('welcome'); ?></p>
            </div>
            <button class="notification-close" onclick="closeNotification()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Notification Banner -->
        <?php if ($active_notice): ?>
        <div class="notification-banner" id="notificationBanner">
            <div class="notification-content">
                <h3><?php echo htmlspecialchars($active_notice['title']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($active_notice['content'])); ?></p>
            </div>
            <button class="notification-close" onclick="closeNotification()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>

        

        <!-- Background Section -->
        <div class="background-section">
            <h2>About Us</h2>
            <p>We are dedicated to helping businesses grow and succeed through innovative solutions and dedicated support.</p>
        </div>

        <!-- Past Projects Section -->
        <div class="projects-section">
            <h2>Past Projects</h2>
            <div class="projects-grid">
                <a href="project1.php" class="project-card-link">
                    <div class="project-card">
                        <img src="./img/project1.jpg" alt="Project 1">
                        <h3>Project Name</h3>
                        <p>Description of the first successful project implementation.</p>
                    </div>
                </a>
                <a href="project2.php" class="project-card-link">
                    <div class="project-card">
                        <img src="./img/project2.jpg" alt="Project 2">
                        <h3>Project Name</h3>
                        <p>Description of the second successful project implementation.</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Success Stories Section -->
        <div class="success-section">
            <h2>Success Stories</h2>
            <div class="success-grid">
                <a href="story1.php" class="success-card-link">
                    <div class="success-card">
                        <img src="./img/picture.png" alt="Success Story 1">
                        <h3>Success story 1</h3>
                        <p>How we helped our client achieve their business goals.</p>
                    </div>
                </a>
                <a href="story2.php" class="success-card-link">
                    <div class="success-card">
                        <img src="./img/picture.png" alt="Success Story 2">
                        <h3>Success story 2</h3>
                        <p>Another success story of business transformation.</p>
                    </div>
                </a>
                <a href="story3.php" class="success-card-link">
                    <div class="success-card">
                        <img src="./img/picture.png" alt="Success Story 3">
                        <h3>Success story 3</h3>
                        <p>Creating impact through innovative solutions.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function closeNotification() {
            document.getElementById('notificationBanner').style.display = 'none';
            
            // Store the hidden state in localStorage
            localStorage.setItem('notice_<?php echo $active_notice['id'] ?? ''; ?>', 'hidden');
        }

        // Check if this notice was previously hidden
        window.addEventListener('load', function() {
            <?php if ($active_notice): ?>
            const noticeId = '<?php echo $active_notice['id']; ?>';
            if (localStorage.getItem('notice_' + noticeId) === 'hidden') {
                document.getElementById('notificationBanner').style.display = 'none';
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>