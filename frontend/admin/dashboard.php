<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

include "../layouts/header.php";
include "../layouts/sidebar.php";

require_once __DIR__ . "/../../backend/classes/user.php";
require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";

require_once __DIR__ . "/../../backend/config/database.php";

$db = (new Database())->connect();

/* OBJECTS */
$userObj = new User($db);
$internshipObj = new Internship();
$appObj = new Application();

/* COUNTS */
$totalUsers = $userObj->countUsers();
$totalInternships = $internshipObj->countInternships();
$totalApplications = $appObj->countApplications();
?>

<div class="grid">

    <div class="stat-card">
        <div class="stat-title">Total Users</div>
        <div class="stat-number">
            <?php echo $totalUsers; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Internships</div>
        <div class="stat-number">
            <?php echo $totalInternships; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-title">Applications</div>
        <div class="stat-number">
            <?php echo $totalApplications; ?>
        </div>
    </div>

</div>

<?php include "../layouts/footer.php"; ?>
