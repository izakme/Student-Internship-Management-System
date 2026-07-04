<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Report.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$reportObj = new Report();
$reports = $reportObj->getAllReports();
$search = "";

// Generate report
if (isset($_POST['generate_report'])) {
    try {
        $report_type = $_POST['report_type'] ?? '';
        
        switch ($report_type) {
            case 'applications':
                $reportObj->generateApplicationsReport($_SESSION['user_id']);
                $_SESSION['message'] = "Applications report generated successfully.";
                break;
            case 'internships':
                $reportObj->generateInternshipsReport($_SESSION['user_id']);
                $_SESSION['message'] = "Internships report generated successfully.";
                break;
            case 'students':
                $reportObj->generateStudentsReport($_SESSION['user_id']);
                $_SESSION['message'] = "Students report generated successfully.";
                break;
            default:
                $_SESSION['error'] = "Invalid report type.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error generating report: " . $e->getMessage();
        error_log("Report generation error: " . $e->getMessage());
    }
    header("Location: reports.php");
    exit();
}

// Search reports
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $reports = $reportObj->searchReports($search);
} else {
    $reports = $reportObj->getAllReports();
}

// Delete report
if (isset($_GET['delete'])) {
    try {
        $report_id = $_GET['delete'];
        if ($reportObj->deleteReport($report_id)) {
            $_SESSION['message'] = "Report deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete report.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting report: " . $e->getMessage();
    }
    header("Location: reports.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

<?php include '../layouts/header.php'; ?>

<div class="layout">
    <?php include '../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="card">
            <h2 class="center">Generate Reports</h2>

            <?php if (isset($_SESSION['message'])): ?>
                <p style="color: #28a745; background: #d4edda; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    ✓ <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                </p>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <p style="color: #dc3545; background: #f8d7da; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    ✗ <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </p>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Select Report Type:</label>
                    <select name="report_type" required style="margin-bottom: 15px;">
                        <option value="">-- Choose a Report --</option>
                        <option value="applications">Applications Report</option>
                        <option value="internships">Internships Report</option>
                        <option value="students">Students Report</option>
                    </select>
                </div>
                <button type="submit" name="generate_report" class="btn">Generate Report</button>
            </form>
        </div>

        <div class="card">
            <h2 class="center">Search Reports</h2>
            <form method="GET">
                <input type="text" name="search" placeholder="Search by report name or type..." 
                       value="<?= htmlspecialchars($search) ?>" style="margin-bottom: 15px;">
                <button type="submit" class="btn">Search</button>
                <?php if ($search): ?>
                    <a href="reports.php" class="btn" style="background: #6c757d;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card">
            <h2 class="center">Generated Reports</h2>
            
            <?php if ($reports && $reports->rowCount() > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Report Name</th>
                            <th>Type</th>
                            <th>Generated By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $reports->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['report_id']) ?></td>
                                <td><?= htmlspecialchars($row['report_name']) ?></td>
                                <td>
                                    <span class="badge" style="background: #0d6efd; color: white; padding: 6px 12px; border-radius: 50px;">
                                        <?= htmlspecialchars($row['report_type']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['full_name'] ?? 'Unknown') ?></td>
                                <td><?= htmlspecialchars(date('M d, Y H:i', strtotime($row['generated_date']))) ?></td>
                                <td>
                                    <a href="reports.php?delete=<?= $row['report_id']; ?>" 
                                       onclick="return confirm('Are you sure you want to delete this report?');" 
                                       class="btn" style="background: #dc3545; padding: 6px 12px; font-size: 12px; text-decoration: none;">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 40px;">
                    No reports found. Generate a new report to get started.
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include "../layouts/footer.php"; ?>

</body>
</html>
