<?php
require_once 'config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'apply.php';
    header('Location: login.php');
    exit();
}

// Get all applications for the user
$stmt = $conn->prepare("
    SELECT * FROM applications 
    WHERE user_id = ? 
    ORDER BY submission_date DESC
");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$applications = $stmt->get_result();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $ic = sanitize_input($_POST['ic']);
    $contact = sanitize_input($_POST['contact']);
    $email = sanitize_input($_POST['email']);
    $business_type = sanitize_input($_POST['business_type']);
    $business_duration = sanitize_input($_POST['business_duration']);

    $stmt = $conn->prepare("
        INSERT INTO applications 
        (user_id, name, ic_number, contact, email, business_type, business_duration, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'submitted')
    ");

    $stmt->bind_param(
        "sssssss",
        $_SESSION['user_id'],
        $name,
        $ic,
        $contact,
        $email,
        $business_type,
        $business_duration
    );

    if ($stmt->execute()) {
        // Send notification to inbox
        $application_id = $conn->insert_id;
        $stmt = $conn->prepare("
            INSERT INTO inbox 
            (user_id, subject, message, from_type)
            VALUES (?, 'Application Submitted', 'Your application has been submitted successfully and is pending review.', 'system')
        ");
        $stmt->bind_param("s", $_SESSION['user_id']);
        $stmt->execute();

        header('Location: apply.php?submitted=1');
        exit();
    }
}

$progress_steps = [
    'submitted' => 1,
    'pending' => 2,
    'approved' => 3,
    'rejected' => 3
];
?>

<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Form</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/apply.css">
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="application-container">
            <!-- Progress Bars Section -->
            <?php if ($applications->num_rows > 0): ?>
                <div class="progress-bars-section">
                    <?php while ($app = $applications->fetch_assoc()):
                        // Skip completed applications (approved or rejected)
                        if ($app['status'] === 'approved' || $app['status'] === 'rejected') continue;
                        $current_step = $progress_steps[$app['status']] ?? 0;
                    ?>
                        <div class="progress-bar-card">
                            <div class="progress-bar-header">
                                <span class="progress-bar-title">Application: <?php echo htmlspecialchars($app['name']); ?></span>
                                <span class="submission-date"><?php echo date('Y-m-d', strtotime($app['submission_date'])); ?></span>
                            </div>

                            <div class="progress-steps">
                                <div class="progress-step <?php echo $current_step >= 1 ? 'completed' : ''; ?>">
                                    <i class="fas fa-check"></i>
                                    <span class="progress-label">Submitted</span>
                                </div>
                                <div class="progress-step <?php echo $current_step >= 2 ? 'completed' : ($current_step == 1 ? 'active' : ''); ?>">
                                    <i class="fas <?php echo $current_step >= 2 ? 'fa-check' : 'fa-clock'; ?>"></i>
                                    <span class="progress-label">Under Review</span>
                                </div>
                                <div class="progress-step <?php echo $current_step >= 3 ? 'completed' : ''; ?>">
                                    <i class="fas <?php echo $app['status'] === 'approved' ? 'fa-check' : ($app['status'] === 'rejected' ? 'fa-times' : 'fa-flag'); ?>"></i>
                                    <span class="progress-label">
                                        <?php
                                        if ($app['status'] === 'approved') echo 'Approved';
                                        elseif ($app['status'] === 'rejected') echo 'Rejected';
                                        else echo 'Decision';
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <div class="status-message">
                                <?php
                                switch ($app['status']) {
                                    case 'submitted':
                                        echo 'Your application has been submitted and is waiting for review.';
                                        break;
                                    case 'pending':
                                        echo 'Your application is currently under review.';
                                        break;
                                    case 'approved':
                                        echo 'Congratulations! Your application has been approved.';
                                        break;
                                    case 'rejected':
                                        echo 'Your application has been rejected. You may submit a new application.';
                                        break;
                                }
                                ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>

            <!-- Application Form -->
            <div class="application-form">
                <div class="form-header">
                    <h1>Business Application Form</h1>
                    <p class="form-description">
                        Submit your application to join our platform. We'll review your information
                        and get back to you within 2-3 business days.
                    </p>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="ic">IC Number *</label>
                        <input type="text" id="ic" name="ic" required pattern="[0-9]{12}" title="Please enter a valid 12-digit IC number">
                    </div>

                    <div class="form-group">
                        <label for="contact">Contact Number *</label>
                        <input type="tel" id="contact" name="contact" required pattern="[0-9]{10,11}" title="Please enter a valid phone number">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="business_type">Business Type *</label>
                        <select id="business_type" name="business_type" required>
                            <option value="">Select Business Type</option>
                            <option value="retail">Retail</option>
                            <option value="service">Service</option>
                            <option value="manufacturing">Manufacturing</option>
                            <option value="food">Food & Beverage</option>
                            <option value="others">Others</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="business_duration">How long has your business been operating? *</label>
                        <select id="business_duration" name="business_duration" required>
                            <option value="">Select Duration</option>
                            <option value="less_than_1">Less than 1 year</option>
                            <option value="1_to_3">1-3 years</option>
                            <option value="3_to_5">3-5 years</option>
                            <option value="more_than_5">More than 5 years</option>
                        </select>
                    </div>

                    <button type="submit" class="submit-btn">Submit Application</button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        <?php if (isset($_GET['submitted'])): ?>
            alert('Your application has been submitted successfully!');
            history.replaceState({}, document.title, window.location.pathname);
        <?php endif; ?>
    </script>
</body>

</html>