<?php
session_start();

include "../layouts/header.php";
include "../layouts/sidebar.php";

require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";

/* AUTH CHECK */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'company') {
    header("Location: ../authentication/login.php");
    exit();
}

$company_id = $_SESSION['user_id'];

/* OBJECTS */
$internshipObj = new Internship();
$appObj = new Application();

/* TOTAL POSTED INTERNSHIPS */
$allInternships = $internshipObj->getInternships();
$postedCount = 0;

while ($row = $allInternships->fetch(PDO::FETCH_ASSOC)) {
    if ($row['company_id'] == $company_id) {
        $postedCount++;
    }
}

/* COMPANY APPLICANTS */
$applicants = $appObj->getCompanyApplicants($company_id);
$totalApplicants = $applicants->rowCount();

/* APPROVED STUDENTS */
$approved = 0;

$applicants->execute([$company_id]); // reset pointer

while ($row = $applicants->fetch(PDO::FETCH_ASSOC)) {
    if ($row['status'] === 'Accepted') {
        $approved++;
    }
}
?>

<link rel="stylesheet" href="../assets/css/style.css">

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