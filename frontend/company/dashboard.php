<?php
session_start();

require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";
require_once __DIR__ . "/../../backend/classes/company.php";
require_once __DIR__ . "/../../backend/config/database.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'company') {
    header("Location: ../authentication/login.php");
    exit();
}

$companyObj = new Company();
$company = $companyObj->getCompanyByUserId($_SESSION['user_id']);
$company_id = $company['company_id'] ?? null;

if (!$company_id) {
    die("Company profile not found.");
}

$db = (new Database())->connect();
$internshipObj = new Internship();
$appObj = new Application();

$stmt = $db->prepare("SELECT COUNT(*) FROM internships WHERE company_id = ?");
$stmt->execute([$company_id]);
$postedCount = $stmt->fetchColumn();

$applicantsData = $appObj->getCompanyApplicants($company_id);
$allApplicants = $applicantsData->fetchAll(PDO::FETCH_ASSOC);
$totalApplicants = count($allApplicants);

$approved = 0;
$pending = 0;
foreach ($allApplicants as $app) {
    if ($app['status'] === 'Accepted') $approved++;
    if ($app['status'] === 'Pending') $pending++;
}

/* Recent 5 applicants */
$recentApplicants = array_slice($allApplicants, 0, 5);

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="grid">

    <div class="stat-card">
        <div class="stat-icon">&#128218;</div>
        <div class="stat-title">Posted Internships</div>
        <div class="stat-number"><?php echo $postedCount; ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">&#128101;</div>
        <div class="stat-title">Applicants</div>
        <div class="stat-number"><?php echo $totalApplicants; ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">&#9989;</div>
        <div class="stat-title">Approved</div>
        <div class="stat-number"><?php echo $approved; ?></div>
    </div>

    <div class="stat-card stat-card-warning">
        <div class="stat-icon">&#9200;</div>
        <div class="stat-title">Pending</div>
        <div class="stat-number"><?php echo $pending; ?></div>
    </div>

</div>

<div class="charts-row">
    <div class="chart-card">
        <h3>Applicant Status</h3>
        <canvas id="statusChart"></canvas>
    </div>
    <div class="chart-card">
        <h3>Recent Applicants</h3>
        <?php if (!empty($recentApplicants)): ?>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Internship</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentApplicants as $app): ?>
                <tr>
                    <td data-label="Name"><?php echo htmlspecialchars($app['full_name']); ?></td>
                    <td data-label="Internship"><?php echo htmlspecialchars($app['title']); ?></td>
                    <td data-label="Status">
                        <span class="badge badge-<?php
                            echo $app['status'] === 'Accepted' ? 'success' : ($app['status'] === 'Pending' ? 'pending' : 'danger');
                        ?>"><?php echo $app['status']; ?></span>
                    </td>
                    <td data-label="Date"><?php echo date('M j, Y', strtotime($app['application_date'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align:center;color:var(--text-light);padding:30px;">No applicants yet.</p>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Accepted', 'Rejected'],
            datasets: [{
                data: [<?php echo $pending; ?>, <?php echo $approved; ?>, <?php echo $totalApplicants - $approved - $pending; ?>],
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
});
</script>

<?php include "../layouts/footer.php"; ?>
