<?php
require_once 'config.php';
?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        .privacy-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="card">
            <h1>Privacy Policy</h1>
            <div class="privacy-content">
                <h2>1. Information We Collect</h2>
                <p>We collect information you provide directly to us when you create an account, fill out a form, or communicate with us.</p>

                <h2>2. How We Use Your Information</h2>
                <p>We use the information we collect to provide, maintain, and improve our services, to process your requests, and to communicate with you.</p>

                <h2>3. Information Sharing</h2>
                <p>We do not sell, trade, or otherwise transfer your personally identifiable information to third parties. This does not include trusted third parties who assist us in operating our website.</p>

                <h2>4. Data Security</h2>
                <p>We implement appropriate data collection, storage, and processing practices and security measures to protect against unauthorized access, alteration, disclosure, or destruction of your information.</p>

                <h2>5. Cookies</h2>
                <p>We use cookies to help us remember and process items in your shopping cart, understand and save your preferences for future visits, and compile aggregate data about site traffic and site interaction.</p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>

</html>