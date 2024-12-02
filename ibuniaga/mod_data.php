<?php
require_once 'config.php';

// Check if user is logged in and is a moderator
if (!isLoggedIn() || getUserRole() !== 'moderator') {
    header('Location: login.php');
    exit();
}

// Get time period filter
$period = isset($_GET['period']) ? sanitize_input($_GET['period']) : 'all';
$date_condition = '';
switch($period) {
    case 'week':
        $date_condition = "AND submission_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 WEEK)";
        break;
    case 'month':
        $date_condition = "AND submission_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)";
        break;
    case 'year':
        $date_condition = "AND submission_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR)";
        break;
}

// Get moderator's statistics - Modified query to remove updated_date dependency
$stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        AVG(CASE 
            WHEN status IN ('approved', 'rejected') 
            THEN TIMESTAMPDIFF(HOUR, submission_date, CURRENT_TIMESTAMP)
            ELSE NULL 
        END) as avg_response_time
    FROM applications
    WHERE assigned_to = '{$_SESSION['user_id']}' {$date_condition}
")->fetch_assoc();

// Get monthly trends data
$monthly_data = $conn->query("
    SELECT 
        DATE_FORMAT(submission_date, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM applications
    WHERE assigned_to = '{$_SESSION['user_id']}' {$date_condition}
    GROUP BY DATE_FORMAT(submission_date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 12
");

// Get business type distribution
$business_stats = $conn->query("
    SELECT 
        business_type,
        COUNT(*) as count
    FROM applications
    WHERE assigned_to = '{$_SESSION['user_id']}' {$date_condition}
    GROUP BY business_type
");
?>

<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'en'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Report</title>
    <link rel="stylesheet" href="css/moderator/mod_data.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <div class="report-section">
            <div class="report-header">
                <div class="title-section">
                    <h1><?php echo __('performance_report'); ?></h1>
                    <div class="period-filter">
                        <select onchange="window.location.href='?period=' + this.value">
                            <option value="all" <?php echo $period === 'all' ? 'selected' : ''; ?>>
                                <?php echo __('all_time'); ?>
                            </option>
                            <option value="week" <?php echo $period === 'week' ? 'selected' : ''; ?>>
                                <?php echo __('last_week'); ?>
                            </option>
                            <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>
                                <?php echo __('last_month'); ?>
                            </option>
                            <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>
                                <?php echo __('last_year'); ?>
                            </option>
                        </select>
                    </div>
                </div>
                <button onclick="generatePDF()" class="generate-btn">
                    <i class="fas fa-file-pdf"></i> <?php echo __('generate_report'); ?>
                </button>
            </div>

            <div class="report-content" id="report-content">
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3><?php echo __('total_applications'); ?></h3>
                        <p class="stat-number"><?php echo $stats['total']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo __('approved'); ?></h3>
                        <p class="stat-number"><?php echo $stats['approved']; ?></p>
                        <p class="stat-percent">
                            (<?php echo $stats['total'] ? round(($stats['approved'] / $stats['total']) * 100, 1) : 0; ?>%)
                        </p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo __('rejected'); ?></h3>
                        <p class="stat-number"><?php echo $stats['rejected']; ?></p>
                        <p class="stat-percent">
                            (<?php echo $stats['total'] ? round(($stats['rejected'] / $stats['total']) * 100, 1) : 0; ?>%)
                        </p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo __('pending'); ?></h3>
                        <p class="stat-number"><?php echo $stats['pending']; ?></p>
                        <p class="stat-percent">
                            (<?php echo $stats['total'] ? round(($stats['pending'] / $stats['total']) * 100, 1) : 0; ?>%)
                        </p>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <!-- Monthly Trends Chart -->
                    <div class="chart-card">
                        <h3><?php echo __('monthly_trends'); ?></h3>
                        <div class="chart-container">
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                    </div>

                    <!-- Business Type Distribution -->
                    <div class="chart-card">
                        <h3><?php echo __('business_distribution'); ?></h3>
                        <div class="chart-container">
                            <canvas id="businessDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // Monthly Trends Chart
    new Chart(document.getElementById('monthlyTrendsChart'), {
        type: 'bar',
        data: {
            labels: <?php 
                $labels = [];
                $approved = [];
                $rejected = [];
                $pending = [];
                while ($row = $monthly_data->fetch_assoc()) {
                    $labels[] = date('M Y', strtotime($row['month'] . '-01'));
                    $approved[] = $row['approved'];
                    $rejected[] = $row['rejected'];
                    $pending[] = $row['pending'];
                }
                echo json_encode(array_reverse($labels)); 
            ?>,
            datasets: [{
                label: '<?php echo __("approved"); ?>',
                data: <?php echo json_encode(array_reverse($approved)); ?>,
                backgroundColor: 'rgba(40, 167, 69, 0.5)',
                borderColor: 'rgb(40, 167, 69)',
                borderWidth: 1
            }, {
                label: '<?php echo __("rejected"); ?>',
                data: <?php echo json_encode(array_reverse($rejected)); ?>,
                backgroundColor: 'rgba(220, 53, 69, 0.5)',
                borderColor: 'rgb(220, 53, 69)',
                borderWidth: 1
            }, {
                label: '<?php echo __("pending"); ?>',
                data: <?php echo json_encode(array_reverse($pending)); ?>,
                backgroundColor: 'rgba(255, 193, 7, 0.5)',
                borderColor: 'rgb(255, 193, 7)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Business Distribution Chart
    new Chart(document.getElementById('businessDistributionChart'), {
        type: 'pie',
        data: {
            labels: <?php 
                $types = [];
                $counts = [];
                while ($row = $business_stats->fetch_assoc()) {
                    $types[] = __($row['business_type']);
                    $counts[] = $row['count'];
                }
                echo json_encode($types); 
            ?>,
            datasets: [{
                data: <?php echo json_encode($counts); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)'
                ],
                borderColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 206, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });

    // PDF Generation
    function generatePDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Add title
        doc.setFontSize(18);
        doc.text('<?php echo __("performance_report"); ?>', 20, 20);
        
        // Add date and period
        doc.setFontSize(12);
        doc.text([
            '<?php echo __("generated_on"); ?>: ' + new Date().toLocaleDateString(),
            '<?php echo __("period"); ?>: <?php echo __($period); ?>'
        ], 20, 35);
        
        // Add statistics
        doc.setFontSize(14);
        doc.text('<?php echo __("statistics"); ?>', 20, 50);
        doc.setFontSize(12);
        doc.text([
            '<?php echo __("total_applications"); ?>: <?php echo $stats['total']; ?>',
            '<?php echo __("approved"); ?>: <?php echo $stats['approved']; ?> (<?php echo $stats['total'] ? round(($stats['approved'] / $stats['total']) * 100, 1) : 0; ?>%)',
            '<?php echo __("rejected"); ?>: <?php echo $stats['rejected']; ?> (<?php echo $stats['total'] ? round(($stats['rejected'] / $stats['total']) * 100, 1) : 0; ?>%)',
            '<?php echo __("pending"); ?>: <?php echo $stats['pending']; ?> (<?php echo $stats['total'] ? round(($stats['pending'] / $stats['total']) * 100, 1) : 0; ?>%)',
            '<?php echo __("avg_response_time"); ?>: <?php echo round($stats['avg_response_time'] ?? 0, 1); ?> <?php echo __("hours"); ?>'
        ], 25, 65);
        
        // Add charts
        try {
            // Monthly Trends Chart
            doc.addPage();
            doc.setFontSize(14);
            doc.text('<?php echo __("monthly_trends"); ?>', 20, 20);
            const monthlyChart = document.getElementById('monthlyTrendsChart');
            const monthlyChartImg = monthlyChart.toDataURL('image/jpeg', 1.0);
            doc.addImage(monthlyChartImg, 'JPEG', 20, 30, 170, 100);
            
            // Business Distribution Chart
            doc.addPage();
            doc.text('<?php echo __("business_distribution"); ?>', 20, 20);
            const businessChart = document.getElementById('businessDistributionChart');
            const businessChartImg = businessChart.toDataURL('image/jpeg', 1.0);
            doc.addImage(businessChartImg, 'JPEG', 20, 30, 170, 100);
            
        } catch(error) {
            console.error('Error adding charts to PDF:', error);
        }
        
        // Save the PDF with timestamp
        const timestamp = new Date().toISOString().split('T')[0];
        const filename = `moderator_report_${timestamp}.pdf`;
        doc.save(filename);
    }

    // Handle window resize for charts
    window.addEventListener('resize', function() {
        if (typeof monthlyTrendsChart !== 'undefined') monthlyTrendsChart.resize();
        if (typeof businessDistributionChart !== 'undefined') businessDistributionChart.resize();
    });
    </script>
</body>
</html>