<?php
require_once 'config.php';
checkPermission('admin');

// Get monthly statistics
$monthly_stats = $conn->query("
    SELECT 
        DATE_FORMAT(submission_date, '%Y-%m') as month,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM applications 
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
    GROUP BY business_type
");

// Get moderator performance
$moderator_stats = $conn->query("
    SELECT 
        u.username,
        COUNT(*) as total_assigned,
        AVG(CASE WHEN a.status IN ('approved', 'rejected') 
            THEN TIMESTAMPDIFF(HOUR, a.submission_date, a.status_changed_date)
            ELSE NULL END) as avg_response_time
    FROM applications a
    JOIN users u ON a.assigned_to = u.id
    WHERE u.role = 'moderator'
    GROUP BY u.id, u.username
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Analysis</title>
    <link rel="stylesheet" href="css/admin_data.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>

<body>
    <?php include 'header.php'; ?>
    <?php include 'nav.php'; ?>

    <div class="content-container">
        <!-- Header Section -->
        <div class="data-section">
            <div class="header-actions">
                <h1>Data Analysis</h1>
                <div class="actions">
                    <select id="reportPeriod">
                        <option value="7">Last Week</option>
                        <option value="30">Last Month</option>
                        <option value="365">Last Year</option>
                        <option value="all">All Time</option>
                    </select>
                    <button onclick="generatePDF()" class="generate-btn">
                        <i class="fas fa-file-pdf"></i> <?php echo __('generate_report'); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Monthly Trends Section -->
        <div class="data-section">
            <h2 class="data-section-title">Monthly Application Trends</h2>
            <div class="stats-card">
                <div class="chart-container">
                    <canvas id="monthlyTrends"></canvas>
                </div>
            </div>
        </div>

        <!-- Business Distribution Section -->
        <div class="data-section">
            <h2 class="data-section-title">Business Type Distribution</h2>
            <div class="stats-card">
                <div class="chart-container">
                    <canvas id="businessDistribution"></canvas>
                </div>
            </div>
        </div>

        <!-- Moderator Performance Section -->
        <div class="data-section">
            <h2 class="data-section-title">Moderator Performance</h2>
            <div class="stats-card">
                <div class="performance-grid">
                    <?php while ($mod = $moderator_stats->fetch_assoc()): ?>
                        <div class="performance-card">
                            <h3><?= htmlspecialchars($mod['username']) ?></h3>
                            <p>Total Assigned: <?= $mod['total_assigned'] ?></p>
                            <p>Avg Response Time: <?= round($mod['avg_response_time'], 1) ?> hours</p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Monthly Trends Chart
        const monthlyStats = <?= json_encode($monthly_stats->fetch_all(MYSQLI_ASSOC)) ?>;

        const monthlyData = {
            labels: monthlyStats.map(row => row.month),
            datasets: [{
                label: 'Total',
                data: monthlyStats.map(row => row.total),
                borderColor: '#4A90E2',
                fill: false
            }, {
                label: 'Approved',
                data: monthlyStats.map(row => row.approved),
                borderColor: '#2ECC71',
                fill: false
            }, {
                label: 'Rejected',
                data: monthlyStats.map(row => row.rejected),
                borderColor: '#E74C3C',
                fill: false
            }]
        };

        new Chart('monthlyTrends', {
            type: 'line',
            data: monthlyData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Business Distribution Chart
        const businessStats = <?= json_encode($business_stats->fetch_all(MYSQLI_ASSOC)) ?>;

        const businessData = {
            labels: businessStats.map(row => row.business_type),
            datasets: [{
                data: businessStats.map(row => row.count),
                backgroundColor: ['#3498DB', '#2ECC71', '#E74C3C', '#F1C40F', '#9B59B6']
            }]
        };

        new Chart('businessDistribution', {
            type: 'pie',
            data: businessData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // PDF Report Generator Function
        async function generatePDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();
            const pageWidth = doc.internal.pageSize.getWidth();

            // Helper function to add centered text
            const addCenteredText = (text, y) => {
                const textWidth = doc.getStringUnitWidth(text) * doc.internal.getFontSize() / doc.internal.scaleFactor;
                const x = (pageWidth - textWidth) / 2;
                doc.text(text, x, y);
            };

            // Title Page
            doc.setFontSize(24);
            addCenteredText('Data Analysis Report', 30);

            doc.setFontSize(12);
            const reportPeriod = document.getElementById('reportPeriod').value;
            const periodText = {
                '7': 'Last Week',
                '30': 'Last Month',
                '365': 'Last Year',
                'all': 'All Time'
            } [reportPeriod];

            addCenteredText(`Report Period: ${periodText}`, 45);
            addCenteredText(`Generated on: ${new Date().toLocaleDateString()}`, 55);

            // Monthly Trends Chart
            doc.addPage();
            doc.setFontSize(16);
            addCenteredText('Monthly Application Trends', 20);

            const monthlyChart = document.getElementById('monthlyTrends');
            const monthlyChartImage = monthlyChart.toDataURL('image/jpeg', 1.0);
            doc.addImage(monthlyChartImage, 'JPEG', 15, 30, 180, 100);

            // Business Distribution Chart
            doc.addPage();
            addCenteredText('Business Type Distribution', 20);

            const businessChart = document.getElementById('businessDistribution');
            const businessChartImage = businessChart.toDataURL('image/jpeg', 1.0);
            doc.addImage(businessChartImage, 'JPEG', 15, 30, 180, 100);

            // Moderator Performance
            doc.addPage();
            addCenteredText('Moderator Performance', 20);

            const performanceCards = document.querySelectorAll('.performance-card');
            let yPosition = 40;

            performanceCards.forEach((card, index) => {
                if (yPosition > 250) {
                    doc.addPage();
                    yPosition = 40;
                }

                const username = card.querySelector('h3').textContent;
                const stats = Array.from(card.querySelectorAll('p')).map(p => p.textContent);

                doc.setFontSize(14);
                doc.text(username, 20, yPosition);
                doc.setFontSize(12);
                stats.forEach((stat, i) => {
                    doc.text(stat, 25, yPosition + 7 + (i * 7));
                });

                yPosition += 30;
            });

            // Save the PDF
            const timestamp = new Date().toISOString().split('T')[0];
            doc.save(`data_analysis_report_${timestamp}.pdf`);
        }
    </script>
</body>

</html>