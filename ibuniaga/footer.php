<footer class="main-footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li><a href="terms.php">Terms & Conditions</a></li>
                <li><a href="privacy.php">Privacy Policy</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Stay Informed</h3>
            <p>
                <i class="fas fa-map-marker-alt"></i>
                1, Jln Taylors, 47500 Subang Jaya, Selangor
            </p>
            <p>
                <i class="fas fa-phone"></i>
                03-5629 5000
            </p>
        </div>

        <div class="footer-section">
            <h3>Find us on</h3>
            <div class="social-links">
                <a href="https://www.facebook.com/TaylorsUniversityMY" target="_blank"><i class="fab fa-facebook"></i></a>
                <a href="https://www.instagram.com/taylorsuni/" target="_blank"><i class="fab fa-instagram"></i></a>
                <a href="https://www.youtube.com/channel/UCONG2GfhpmHP9JhmWiJFoxQ" target="_blank"><i class="fab fa-youtube"></i></a>
                <a href="https://x.com/TaylorsUni" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://www.tiktok.com/@taylors.uni?lang=en" target="_blank"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>Copyright &copy; 2024 Taylor's University Sdn. Bhd. 198601000495 (149634-D) DU023 (B). All rights reserved</p>
    </div>
</footer>

<script>
    // Common JavaScript functions
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.body.classList.toggle('sidebar-open');
    }

    function toggleProfile() {
        document.getElementById('profileDropdown').classList.toggle('show');
    }

    function toggleSubmenu(element) {
        const submenu = element.nextElementSibling;
        const icon = element.querySelector('.submenu-icon');
        submenu.classList.toggle('show');
        icon.classList.toggle('rotate');
    }

    function changeLanguage(lang) {
        fetch('change_language.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'lang=' + lang
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
    }

    // Close dropdowns when clicking outside
    window.onclick = function(event) {
        if (!event.target.matches('.profile-btn') && !event.target.matches('.profile-btn *')) {
            const dropdowns = document.getElementsByClassName('dropdown-content');
            for (let dropdown of dropdowns) {
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        }

        // Close sidebar on mobile when clicking outside
        if (window.innerWidth < 993) {
            if (!event.target.matches('.sidebar') &&
                !event.target.matches('.sidebar *') &&
                !event.target.matches('.menu-toggle') &&
                !event.target.matches('.menu-toggle *')) {
                document.getElementById('sidebar').classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }
        }
    };
</script>
</body>

</html>