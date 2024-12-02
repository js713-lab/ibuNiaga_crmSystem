<?php
$userRole = getUserRole();
?>
<nav class="sidebar" id="sidebar">
    <button class="close-sidebar" onclick="toggleSidebar()">
        <i class="fas fa-times"></i>
    </button>

    <ul class="sidebar-menu">
        <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="apply.php"><i class="fas fa-file-alt"></i> Form application</a></li>
        <li><a href="course.php"><i class="fas fa-graduation-cap"></i> Course</a></li>
        <?php if (isLoggedIn()): ?>
            <li><a href="notifications.php"><i class="fas fa-inbox"></i> Notification</a></li>
        <?php endif; ?>

        <?php if ($userRole === 'admin'): ?>
            <li class="has-submenu">
                <a href="#" onclick="toggleSubmenu(this)">
                    <i class="fas fa-user-shield"></i> Administrator
                    <i class="fas fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="admin_moderator.php"><i class="fas fa-users-cog"></i> Moderator</a></li>
                    <li><a href="admin_task.php"><i class="fas fa-tasks"></i> Task</a></li>
                    <li><a href="admin_data.php"><i class="fas fa-chart-line"></i> Data Analysis</a></li>
                </ul>
            </li>
        <?php endif; ?>

        <?php if ($userRole === 'moderator'): ?>
            <li class="has-submenu">
                <a href="#" onclick="toggleSubmenu(this)">
                    <i class="fas fa-user-tie"></i> Moderator
                    <i class="fas fa-chevron-down submenu-icon"></i>
                </a>
                <ul class="submenu">
                    <li><a href="mod_task.php"><i class="fas fa-clipboard-list"></i> Task</a></li>
                    <li><a href="mod_announcement.php"><i class="fas fa-bell"></i> Announcement</a></li>
                    <li><a href="mod_data.php"><i class="fas fa-chart-bar"></i> Data Analysis</a></li>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.content-container');
        const isMobile = window.innerWidth <= 992;

        if (isMobile) {
            // Mobile behavior
            sidebar.classList.toggle('active');

            if (sidebar.classList.contains('active')) {
                // Create and show overlay
                const overlay = document.createElement('div');
                overlay.className = 'overlay';
                document.body.appendChild(overlay);

                setTimeout(() => {
                    overlay.classList.add('active');
                }, 50);

                overlay.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    setTimeout(() => overlay.remove(), 300);
                });
            } else {
                // Remove overlay
                const overlay = document.querySelector('.overlay');
                if (overlay) {
                    overlay.classList.remove('active');
                    setTimeout(() => overlay.remove(), 300);
                }
            }
        } else {
            // Desktop behavior
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');

            // Save state
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }
    }

    // Restore sidebar state on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.content-container');

        if (window.innerWidth > 992 && localStorage.getItem('sidebarCollapsed') === 'true') {
            sidebar.classList.add('collapsed');
            content.classList.add('expanded');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const content = document.querySelector('.content-container');
        const overlay = document.querySelector('.overlay');

        if (window.innerWidth > 992) {
            sidebar.classList.remove('active');
            if (overlay) {
                overlay.remove();
            }
        } else {
            sidebar.classList.remove('collapsed');
            content.classList.remove('expanded');
        }
    });

    function toggleSubmenu(element) {
        const submenu = element.nextElementSibling;
        const icon = element.querySelector('.submenu-icon');
        submenu.classList.toggle('show');
        icon.classList.toggle('rotate');
    }

    // Close sidebar on mobile when clicking outside
    if (window.innerWidth < 993) {
        window.addEventListener('click', function(event) {
            if (!event.target.matches('.sidebar') &&
                !event.target.matches('.sidebar *') &&
                !event.target.matches('.menu-toggle') &&
                !event.target.matches('.menu-toggle *')) {
                document.getElementById('sidebar').classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }
        });
    }
</script>