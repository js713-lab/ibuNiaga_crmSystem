<?php
// header.php
require_once 'config.php';
?>
<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Ibu Niaga</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (getUserRole() === 'admin'): ?>
        <link rel="stylesheet" href="css/admin.css">
    <?php elseif (getUserRole() === 'moderator'): ?>
        <link rel="stylesheet" href="css/moderator.css">
    <?php endif; ?>
    <script>
        function acceptCookies() {
            setCookie('<?php echo COOKIE_CONSENT; ?>', 'accepted', <?php echo COOKIE_DURATION; ?>);
            document.getElementById('cookieConsent').style.display = 'none';
        }

        function declineCookies() {
            setCookie('<?php echo COOKIE_CONSENT; ?>', 'declined', <?php echo COOKIE_DURATION; ?>);
            deleteAllCookies();
            document.getElementById('cookieConsent').style.display = 'none';
        }

        function setCookie(name, value, maxAge) {
            document.cookie = `${name}=${value};max-age=${maxAge};path=/;SameSite=Lax`;
        }

        function deleteAllCookies() {
            const cookies = document.cookie.split(';');
            for (let cookie of cookies) {
                const eqPos = cookie.indexOf('=');
                const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
                if (name !== '<?php echo COOKIE_CONSENT; ?>') {
                    document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
                }
            }
        }

        const profileBtn = document.querySelector('.profile-btn');
        const profileDropdown = document.getElementById('profileDropdown');

        function toggleProfile(event) {
            event.stopPropagation();
            profileDropdown.classList.toggle('show');
            profileBtn.setAttribute('aria-expanded', profileDropdown.classList.contains('show'));
        }
    </script>
</head>

<body>
    <?php if (!isset($_COOKIE[COOKIE_CONSENT])): ?>
        <div id="cookieConsent" class="cookie-banner">
            <div class="cookie-content">
                <h3>cookie setting</h3>
                <p>Do you accept cookies?</p>
                <div class="cookie-buttons">
                    <button onclick="acceptCookies()" class="btn-accept">accept cookies</button>
                    <button onclick="declineCookies()" class="btn-decline">decline cookies</button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <header class="main-header">
        <div class="header-left">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <a href="index.php" class="logo">
                <img src="img/logo.png" alt="<?php echo __('site_name'); ?>" height="40">
            </a>
        </div>

        <div class="header-right">
            <div class="language-selector">
                <select onchange="changeLanguage(this.value)">
                    <option value="en" <?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'en') ? 'selected' : ''; ?>>English</option>
                    <option value="ms" <?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'ms') ? 'selected' : ''; ?>>Bahasa Melayu</option>
                    <option value="zh" <?php echo (isset($_SESSION['lang']) && $_SESSION['lang'] === 'zh') ? 'selected' : ''; ?>>中文</option>
                </select>
            </div>

            <div class="profile-dropdown">
                <button class="profile-btn" aria-expanded="false" aria-controls="profileDropdown">
                    <img src="<?php echo isset($_SESSION['user_id']) ?
                                    'uploads/profiles/' . $_SESSION['user_id'] . '.jpg' :
                                    './img/profile.jpeg'; ?>"
                        alt="Profile"
                        class="profile-image-small"
                        onerror="this.src='./img/profile.jpeg'">
                </button>

                <div id="profileDropdown" class="dropdown-content">
                    <?php if (isLoggedIn()): ?>
                        <div class="profile-info">
                            <img src="<?php echo 'uploads/profiles/' . $_SESSION['user_id'] . '.jpg'; ?>"
                                alt="Profile"
                                class="profile-image"
                                onerror="this.src='./img/profile.jpeg'">
                            <p class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            <p class="user-id">ID: <?php echo htmlspecialchars($_SESSION['user_id']); ?></p>
                        </div>
                    <?php endif; ?>
                    <ul class="profile-links">
                        <?php if (isLoggedIn()): ?>
                            <li><a href="profile.php"><i class="fas fa-user-edit"></i>profile</a></li>
                            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>logout</a></li>
                        <?php else: ?>
                            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i>login</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileBtn = document.querySelector('.profile-btn');
            const profileDropdown = document.getElementById('profileDropdown');

            if (profileBtn && profileDropdown) {
                profileBtn.addEventListener('click', function(event) {
                    event.stopPropagation();
                    profileDropdown.classList.toggle('show');
                    this.setAttribute('aria-expanded', profileDropdown.classList.contains('show'));
                });

                document.addEventListener('click', function(event) {
                    if (!event.target.closest('.profile-dropdown')) {
                        profileDropdown.classList.remove('show');
                        profileBtn.setAttribute('aria-expanded', 'false');
                    }
                });

                profileDropdown.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            }
        });
    </script>