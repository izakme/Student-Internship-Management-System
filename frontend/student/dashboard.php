<?php
session_start();

require_once __DIR__ . "/../../backend/config/database.php";
require_once __DIR__ . "/../../backend/helpers/Language.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: ../authentication/login.php");
    exit();
}

$db = (new Database())->connect();

require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";

$student_id = $_SESSION['student_id'];

$internshipObj = new Internship();
$appObj = new Application();

$availableInternships = $internshipObj->activeInternships();
$availableCount = $availableInternships->rowCount();

$totalApplications = $appObj->countStudentApplications($student_id);
$accepted = $appObj->countAcceptedByStudent($student_id);

$pending = 0;
$rejected = 0;
$stmt = $db->prepare("
    SELECT status, COUNT(*) AS cnt
    FROM applications
    WHERE student_id = ?
    GROUP BY status
");
$stmt->execute([$student_id]);
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['status'] === 'Pending') $pending = (int)$row['cnt'];
    if ($row['status'] === 'Rejected') $rejected = (int)$row['cnt'];
}

/* Last 5 applications for recent activity */
$recentStmt = $db->prepare("
    SELECT i.title, a.status, a.application_date
    FROM applications a
    INNER JOIN internships i ON a.internship_id = i.internship_id
    WHERE a.student_id = ?
    ORDER BY a.application_date DESC
    LIMIT 5
");
$recentStmt->execute([$student_id]);
$recentApps = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="grid">

    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-briefcase"></i></div>
        <div class="stat-title"><?= __('Available Internships') ?></div>
        <div class="stat-number"><?php echo $availableCount; ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
        <div class="stat-title"><?= __('My Applications') ?></div>
        <div class="stat-number"><?php echo $totalApplications; ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-title"><?= __('Accepted') ?></div>
        <div class="stat-number"><?php echo $accepted; ?></div>
    </div>

    <div class="stat-card stat-card-warning">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-title"><?= __('Pending') ?></div>
        <div class="stat-number"><?php echo $pending; ?></div>
    </div>

</div>

<div class="charts-row">
    <div class="chart-card">
        <h3><?= __('My Application Status') ?></h3>
        <canvas id="statusChart"></canvas>
    </div>
    <div class="chart-card">
        <h3><?= __('Recent Activity') ?></h3>
        <?php if (!empty($recentApps)): ?>
        <table>
            <thead>
                <tr>
                    <th><?= __('Internship') ?></th>
                    <th><?= __('Status') ?></th>
                    <th><?= __('Date') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentApps as $app): ?>
                <tr>
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
        <p style="text-align:center;color:var(--text-light);padding:30px;"><?= __('No applications yet.') ?></p>
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
                data: [<?php echo $pending; ?>, <?php echo $accepted; ?>, <?php echo $rejected; ?>],
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
