<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Application.php";
require_once "../../backend/classes/company.php";

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
    $app_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'];
    
    if ($appObj->updateCompanyApplicationStatus($app_id, $company_id, $status)) {
        $_SESSION['message'] = "Application status updated.";
    } else {
        $_SESSION['error'] = "Application not found or unauthorized.";
    }
    header("Location: applications.php");
    exit();
}

// Get applicants for this company's internships
$applicants = $appObj->getCompanyApplicants($company_id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Review Applications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .status-form {
            display: inline-flex;
            gap: 5px;
        }
        .status-form select {
            padding: 5px;
            border-radius: 3px;
            border: 1px solid #ccc;
        }
        .status-form button {
            padding: 5px 10px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="topbar">
    Internship Management System
</div>

<div class="layout">
    <div class="sidebar">
        <h3>Company Menu</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="internships.php">My Internships</a>
        <a href="applications.php" class="active">Applications</a>
        <a href="profile.php">Profile</a>
        <a href="../authentication/logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="card">
            <h2>Review Applications</h2>
            <?php if (isset($_SESSION['message'])): ?>
                <p class="success-msg"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error-msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
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
                    <th>Status</th>
                    <th>Applied Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($applicants->rowCount() > 0): ?>
                    <?php while ($row = $applicants->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['application_id']) ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['registration_no']) ?></td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td>
                                <form method="POST" class="status-form">
                                    <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">
                                    <select name="status">
                                        <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="Accepted" <?= $row['status'] === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                                        <option value="Rejected" <?= $row['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                    </select>
                                    <button type="submit">Update</button>
                                </form>
                            </td>
                            <td><?= htmlspecialchars($row['application_date']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="center">No applications yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../layouts/footer.php"; ?>
