<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Application.php";
require_once "../../backend/classes/company.php";
require_once "../../backend/helpers/csrf.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'company') {
    header("Location: ../authentication/login.php");
    exit();
}

$company_user_id = $_SESSION['user_id'];
$db = (new Database())->connect();
$companyObj = new Company();
$appObj = new Application();

// Get company_id from user_id
$company = $companyObj->getCompanyByUserId($company_user_id);
$company_id = $company['company_id'] ?? null;

if (!$company_id) {
    die("Company profile not found.");
}

// Update application status
if (isset($_POST['application_id']) && isset($_POST['status'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
    $app_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'];
    $allowedStatuses = ['Pending', 'Accepted', 'Rejected'];
    
    if (!in_array($status, $allowedStatuses, true)) {
        $_SESSION['error'] = "Invalid status value.";
    } elseif ($appObj->updateCompanyApplicationStatus($app_id, $company_id, $status)) {
        $_SESSION['message'] = "Application status updated.";
    } else {
        $_SESSION['error'] = "Application not found or unauthorized.";
    }
    header("Location: applications.php");
    exit();
    }
}

// Get applicants for this company's internships
$applicants = $appObj->getCompanyApplicants($company_id);

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>


<div class="card">
    <h2>Review Applications</h2>
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
            <th>Student Name</th>
            <th>Registration No</th>
            <th>Internship</th>
            <th>Cover Letter</th>
            <th>Status</th>
            <th>Applied Date</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($applicants->rowCount() > 0): ?>
            <?php while ($row = $applicants->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td data-label="ID"><?= htmlspecialchars($row['application_id']) ?></td>
                    <td data-label="Student"><?= htmlspecialchars($row['username']) ?></td>
                    <td data-label="Reg No"><?= htmlspecialchars($row['registration_no']) ?></td>
                    <td data-label="Internship"><?= htmlspecialchars($row['title']) ?></td>
                    <td data-label="Cover Letter" style="max-width:200px;">
                        <?php if (!empty($row['cover_letter'])): ?>
                            <em><?= htmlspecialchars($row['cover_letter']) ?></em>
                        <?php else: ?>
                            <span style="color:#999;">—</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Status">
                        <form method="POST" class="status-form">
                            <?= csrfField() ?>
                            <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">
                            <select name="status">
                                <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Accepted" <?= $row['status'] === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                                <option value="Rejected" <?= $row['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                            </select>
                            <button type="submit" class="btn btn-sm">Update</button>
                        </form>
                    </td>
                    <td data-label="Date"><?= htmlspecialchars(date("M j, Y", strtotime($row['application_date']))) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="center">No applications yet.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../layouts/footer.php"; ?>
