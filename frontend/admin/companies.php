<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/company.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$companyObj = new Company();

// Delete company
if (isset($_GET['delete'])) {
    $company_id = $_GET['delete'];
    if ($companyObj->deleteCompany($company_id)) {
        $_SESSION['message'] = "Company deleted successfully.";
    }
    header("Location: companies.php");
    exit();
}

$companies = $companyObj->getCompanies();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Companies</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="topbar">
    Internship Management System
</div>

<div class="layout">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="students.php">Students</a>
        <a href="companies.php" class="active">Companies</a>
        <a href="internships.php">Internships</a>
        <a href="applications.php">Applications</a>
        <a href="reports.php">Reports</a>
        <a href="../authentication/logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="card">
            <h2>Registered Companies</h2>
            <?php if (isset($_SESSION['message'])): ?>
                <p class="success-msg"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Company Name</th>
                    <th>Location</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $companies->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['company_id']) ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= htmlspecialchars($row['location'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['email'] ?? 'N/A') ?></td>
                        <td>
                            <a href="companies.php?delete=<?php echo $row['company_id']; ?>" 
                               onclick="return confirm('Delete this company?');" 
                               class="btn btn-danger" style="padding:5px 10px;font-size:12px;">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../layouts/footer.php"; ?>
</body>
</html>
