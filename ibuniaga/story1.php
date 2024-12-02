<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success Story - <?php echo __('site_name'); ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .story-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        .story-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .story-profile {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            overflow: hidden;
        }

        .story-profile img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .story-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            color: #777;
        }

        .story-section {
            margin-bottom: 2.5rem;
        }

        .quote-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
            margin: 2rem 0;
            position: relative;
        }

        .quote-section::before {
            content: '"';
            position: absolute;
            top: -20px;
            left: 20px;
            font-size: 5rem;
            color: #bc4a46;
            opacity: 0.2;
        }

        .timeline {
            position: relative;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            width: 2px;
            background: #ddd;
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -1px;
        }

        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
        }

        .timeline-item::before {
            content: '';
            width: 20px;
            height: 20px;
            background: #bc4a46;
            position: absolute;
            border-radius: 50%;
            right: -10px;
            top: 15px;
        }

        .timeline-item:nth-child(even) {
            left: 50%;
        }

        .timeline-item:nth-child(even)::before {
            left: -10px;
        }

        .story-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }

        .story-cta {
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
            .timeline::before {
                left: 31px;
            }
            .timeline-item {
                width: 100%;
                padding-left: 70px;
            }
            .timeline-item:nth-child(even) {
                left: 0;
            }
            .timeline-item::before {
                left: 21px;
            }
            .timeline-item:nth-child(even)::before {
                left: 21px;
            }
            .story-meta {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="story-container">
            <div class="story-header">
                <div class="story-profile">
                    <img src="./img/profile.jpeg" alt="Success Story Profile">
                </div>
                <h1>Entrepreneur Name</h1>
                <p>Business Name - Industry Type</p>
                <div class="story-meta">
                    <div class="story-meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Location</span>
                    </div>
                    <div class="story-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Established 2020</span>
                    </div>
                </div>
            </div>

            <div class="quote-section">
                <p>"Inspiring quote from the entrepreneur"</p>
                <strong>- Entrepreneur Name</strong>
            </div>

            <div class="story-section">
                <h2>The Journey</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <h3>Starting Point</h3>
                        <p>Beginning of the journey</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Growth Phase</h3>
                        <p>Business expansion details</p>
                    </div>
                    <div class="timeline-item">
                        <h3>Current Success</h3>
                        <p>Current achievements</p>
                    </div>
                </div>
            </div>

            <div class="story-gallery">
                <div class="gallery-item">
                    <img src="./img/picture.png" alt="Story Image 1">
                </div>
                <div class="gallery-item">
                    <img src="./img/picture.png" alt="Story Image 2">
                </div>
                <div class="gallery-item">
                    <img src="./img/picture.png" alt="Story Image 3">
                </div>
            </div>

            <div class="story-cta">
                <h2>Start Your Success Story</h2>
                <p>Join our community of successful entrepreneurs</p><br>
                <a href="apply.php" class="cta-button">Apply Now</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>