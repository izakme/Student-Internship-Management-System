<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

require_once __DIR__ . "/../../backend/config/database.php";
require_once __DIR__ . "/../../backend/classes/user.php";
require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";

$db = (new Database())->connect();

$userObj = new User($db);
$internshipObj = new Internship();
$appObj = new Application();

$totalUsers = $userObj->countUsers();
$totalInternships = $internshipObj->countInternships();
$totalApplications = $appObj->countApplications();
$pendingApplications = $appObj->countPending();
$acceptedApplications = $appObj->countAccepted();
$rejectedApplications = $appObj->countRejected();

/* Monthly applications data */
$monthlyData = [];
$stmt = $db->query("
    SELECT DATE_FORMAT(application_date, '%Y-%m') AS month,
           COUNT(*) AS total
    FROM applications
    GROUP BY month
    ORDER BY month ASC
    LIMIT 12
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $monthlyData[] = $row;
}

$months = [];
$monthlyCounts = [];
foreach ($monthlyData as $row) {
    $months[] = $row['month'];
    $monthlyCounts[] = (int)$row['total'];
}

/* Recent applications for notifications */
$recentApps = [];
$stmt = $db->query("
    SELECT a.application_id, u.full_name, i.title, a.application_date
    FROM applications a
    INNER JOIN students s ON a.student_id = s.student_id
    INNER JOIN users u ON s.user_id = u.user_id
    INNER JOIN internships i ON a.internship_id = i.internship_id
    ORDER BY a.application_date DESC
    LIMIT 10
");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $recentApps[] = $row;
}

$pendingCount = (int)$pendingApplications;

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="grid">

    <div class="stat-card">
        <div class="stat-icon">&#128101;</div>
        <div class="stat-title">Total Users</div>
        <div class="stat-number">
            <?php echo $totalUsers; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">&#128218;</div>
        <div class="stat-title">Internships</div>
        <div class="stat-number">
            <?php echo $totalInternships; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">&#128203;</div>
        <div class="stat-title">Applications</div>
        <div class="stat-number">
            <?php echo $totalApplications; ?>
        </div>
    </div>

    <div class="stat-card stat-card-warning">
        <div class="stat-icon">&#9200;</div>
        <div class="stat-title">Pending</div>
        <div class="stat-number">
            <?php echo $pendingApplications; ?>
        </div>
    </div>

</div>

<div class="charts-row">
    <div class="chart-card">
        <h3>Applications by Status</h3>
        <canvas id="statusChart"></canvas>
    </div>
    <div class="chart-card">
        <h3>Monthly Applications</h3>
        <canvas id="monthlyChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Accepted', 'Rejected'],
            datasets: [{
                data: [<?php echo $pendingApplications; ?>, <?php echo $acceptedApplications; ?>, <?php echo $rejectedApplications; ?>],
                backgroundColor: ['#f39c12', '#27ae60', '#e74c3c'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Applications',
                data: <?php echo json_encode($monthlyCounts); ?>,
                backgroundColor: '#5bbcff',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>

<?php include "../layouts/footer.php"; ?>
