<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Title - <?php echo __('site_name'); ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .project-header {
            position: relative;
            height: 400px;
            color: #ffffff;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .project-header-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
        }

        .project-header-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            filter: brightness(0.7);
        }

        .project-header-content {
            position: relative;
            z-index: 1;
            padding: 2rem;
        }

        .project-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .project-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        .project-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .gallery-item img {
            width: 100%;
            height: auto;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .gallery-item:hover img {
            transform: scale(1.05);
        }

        .project-highlights {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
        }

        .highlight-item {
            text-align: center;
            padding: 1.5rem;
        }

        .highlight-item i {
            font-size: 2rem;
            color: #bc4a46;
            margin-bottom: 1rem;
        }

        .project-cta {
            text-align: center;
            margin: 3rem 0;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .cta-button {
            display: inline-block;
            padding: 1rem 2rem;
            background-color: #bc4a46;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        @media (max-width: 768px) {
            .project-header {
                height: 300px;
            }

            .project-title {
                font-size: 2rem;
            }

            .project-content {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="project-header">
            <div class="project-header-bg">
                <img src="./img/project1_background.jpeg" alt="Project Banner">
            </div>
            <div class="project-header-content">
                <h1 class="project-title">Project Title Here</h1>
                <p>A brief description of the project</p>
            </div>
        </div>

        <div class="project-content">
            <div class="project-section">
                <h2>Project Overview</h2>
                <p>Project description and objectives</p>
            </div>

            <div class="project-gallery">
                <div class="gallery-item">
                    <img src="./img/project1.jpg" alt="Project Image 1">
                </div>
                <div class="gallery-item">
                    <img src="./img/project1.jpg" alt="Project Image 2">
                </div>
                <div class="gallery-item">
                    <img src="./img/project1.jpg" alt="Project Image 3">
                </div>
            </div>

            <div class="project-highlights">
                <div class="highlight-item">
                    <i class="fas fa-chart-line"></i>
                    <h3>Impact</h3>
                    <p>Project impact description</p>
                </div>
                <div class="highlight-item">
                    <i class="fas fa-clock"></i>
                    <h3>Duration</h3>
                    <p>Project timeline</p>
                </div>
                <div class="highlight-item">
                    <i class="fas fa-users"></i>
                    <h3>Beneficiaries</h3>
                    <p>Number of beneficiaries</p>
                </div>
            </div>

            <div class="project-results">
                <h2>Results & Achievements</h2>
                <p>Project outcomes and achievements</p>
            </div>

            <div class="project-cta">
                <h2>Interested in Similar Projects?</h2>
                <p>Learn how we can help your business grow</p><br>
                <a href="apply.php" class="cta-button">Apply Now</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>