<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Application.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$appObj = new Application();

$applications = $appObj->getApplications();

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2>All Applications</h2>
    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Registration No</th>
            <th>Internship</th>
            <th>Status</th>
            <th>Applied Date</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $applications->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td data-label="ID"><?= htmlspecialchars($row['application_id']) ?></td>
                <td data-label="Student"><?= htmlspecialchars($row['full_name']) ?></td>
                <td data-label="Reg No"><?= htmlspecialchars($row['registration_no']) ?></td>
                <td data-label="Internship"><?= htmlspecialchars($row['title']) ?></td>
                <td data-label="Status"><?= htmlspecialchars($row['status']) ?></td>
                <td data-label="Date"><?= htmlspecialchars($row['application_date']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../layouts/footer.php"; ?>
