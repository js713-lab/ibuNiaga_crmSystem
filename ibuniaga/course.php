<?php
require_once 'config.php';
?>
<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link rel="stylesheet" href="css/course.css">

</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="course-container">
            <!-- Open Bazaar Section -->
            <div class="open-bazaar">
                <h2>Open Bazaar</h2>
                <div class="open-bazaar-content">
                    <div class="open-bazaar-image-container">
                        <img src="img/bazaar.png" alt="Open Bazaar" class="open-bazaar-image">
                    </div>
                    <div class="open-bazaar-text">
                        <p>Experience our vibrant open bazaar where entrepreneurs showcase their products
                            and services. Network with fellow business owners and gain practical
                            market exposure in a supportive environment.</p>
                        <ul>
                            <li>Direct market experience</li>
                            <li>Networking opportunities</li>
                            <li>Customer feedback</li>
                            <li>Business growth strategies</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 7 Weeks Training Section -->
            <div class="training-section">
                <h2>7 Weeks Training Program</h2>
                <div class="modules-container">
                    <button class="scroll-button left" onclick="scrollModules('left')">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <div class="modules-scroll" id="modulesScroll">
                        <!-- Emotional Support Module -->
                        <div class="module-card">
                            <img src="img/emo.jpg" alt="Emotional Support" class="module-image">
                            <div class="module-content">
                                <h3>Emotional Support</h3>
                                <p>Learn techniques for providing emotional support in business contexts</p>
                            </div>
                        </div>

                        <!-- Pastry Module -->
                        <div class="module-card">
                            <img src="img/pastry.jpg" alt="Pastry Module" class="module-image">
                            <div class="module-content">
                                <h3>Pastry Module</h3>
                                <p>Master the art of pastry making and bakery operations</p>
                            </div>
                        </div>

                        <!-- Table Setting Module -->
                        <div class="module-card">
                            <img src="img/table.jpg" alt="Table Setting" class="module-image">
                            <div class="module-content">
                                <h3>Table Setting Module</h3>
                                <p>Professional table setting and dining etiquette</p>
                            </div>
                        </div>

                        <!-- Financial Module -->
                        <div class="module-card">
                            <img src="img/finance.jpg" alt="Financial Module" class="module-image">
                            <div class="module-content">
                                <h3>Financial Module</h3>
                                <p>Business finance and money management skills</p>
                            </div>
                        </div>
                    </div>

                    <button class="scroll-button right" onclick="scrollModules('right')">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function scrollModules(direction) {
            const container = document.getElementById('modulesScroll');
            const scrollAmount = 320; // Card width + gap

            if (direction === 'left') {
                container.scrollLeft -= scrollAmount;
            } else {
                container.scrollLeft += scrollAmount;
            }
        }

        // Add click events to module cards
        document.querySelectorAll('.module-card').forEach(card => {
            card.addEventListener('click', () => {
                // Add your module details page navigation here
                console.log('Module clicked:', card.querySelector('h3').textContent);
            });
        });
    </script>
</body>

</html>