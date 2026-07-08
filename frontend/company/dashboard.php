<?php
session_start();

require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";
require_once __DIR__ . "/../../backend/classes/company.php";

/* AUTH CHECK */
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

include "../layouts/header.php";
include "../layouts/sidebar.php";

require_once "../../backend/config/database.php";
$db = (new Database())->connect();

/* OBJECTS */
$internshipObj = new Internship();
$appObj = new Application();

/* TOTAL POSTED INTERNSHIPS */
$stmt = $db->prepare("SELECT COUNT(*) FROM internships WHERE company_id = ?");
$stmt->execute([$company_id]);
$postedCount = $stmt->fetchColumn();

/* COMPANY APPLICANTS */
$applicantsData = $appObj->getCompanyApplicants($company_id);
$allApplicants = $applicantsData->fetchAll(PDO::FETCH_ASSOC);
$totalApplicants = count($allApplicants);
$approved = 0;
foreach ($allApplicants as $app) {
    if ($app['status'] === 'Accepted') {
        $approved++;
    }
}
?>

<div class="grid">

    <div class="stat-card">
        <div class="stat-title">Posted Internships</div>
        <div class="stat-number">
            <?php echo $postedCount; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Applicants</div>
        <div class="stat-number">
            <?php echo $totalApplicants; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Approved Students</div>
        <div class="stat-number">
            <?php echo $approved; ?>
        </div>
    </div>

</div>

<?php include "../layouts/footer.php"; ?>
