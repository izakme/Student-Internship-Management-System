<?php
session_start();

include "../layouts/header.php";
include "../layouts/sidebar.php";

require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";

// Check login
if (!isset($_SESSION['student_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

/* OBJECTS */
$internshipObj = new Internship();
$appObj = new Application();

/* AVAILABLE INTERNSHIPS (ACTIVE ONLY) */
$availableInternships = $internshipObj->activeInternships();
$availableCount = $availableInternships->rowCount();

/* STUDENT APPLICATIONS */
$applications = $appObj->getStudentApplications($student_id);

$totalApplications = 0;
$accepted = 0;

while ($row = $applications->fetch(PDO::FETCH_ASSOC)) {
    $totalApplications++;

    if ($row['status'] === 'Accepted') {
        $accepted++;
    }
}
?>

<div class="grid">

    <div class="stat-card">
        <div class="stat-title">Available Internships</div>
        <div class="stat-number"><?php echo $availableCount; ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-title">My Applications</div>
        <div class="stat-number"><?php echo $totalApplications; ?></div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Accepted</div>
        <div class="stat-number"><?php echo $accepted; ?></div>
    </div>

</div>

<?php include "../layouts/footer.php"; ?>

<link rel="stylesheet" href="../assets/css/style.css">