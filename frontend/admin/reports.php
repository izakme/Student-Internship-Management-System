<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Report.php";
require_once "../../backend/helpers/csrf.php";
require_once "../../backend/helpers/Pdf.php";
require_once "../../backend/helpers/Language.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$reportObj = new Report();
$reports = $reportObj->getAllReports();
$search = "";

// Download report (CSV)
if (isset($_GET['download'])) {
    $report_id = filter_input(INPUT_GET, 'download', FILTER_VALIDATE_INT);

    if ($report_id && $reportObj->outputReportCsv($report_id)) {
        exit();
    }

    $_SESSION['error'] = __("Report not found or unavailable for download.");
    header("Location: reports.php");
    exit();
}

// Download report (PDF)
if (isset($_GET['pdf'])) {
    $report_id = filter_input(INPUT_GET, 'pdf', FILTER_VALIDATE_INT);
    $report = $reportObj->getReport($report_id);

    if ($report) {
        $rows = json_decode($report['report_data'] ?? '[]', true);

        $headerMap = [
            'username'        => 'FULL NAME',
            'year_of_study'   => 'YOS',
            'registration_no' => 'REGISTRATION NUMBER',
            'application_id'  => 'ID',
            'student_name'    => 'STUDENT NAME',
            'internship_title'=> 'INTERNSHIP TITLE',
            'company_name'    => 'COMPANY',
            'applicant_count' => 'APPLICANTS',
            'application_date'=> 'APPLIED DATE',
            'description'     => 'DESCRIPTION',
            'title'           => 'TITLE',
            'course'          => 'COURSE',
            'email'           => 'EMAIL',
            'status'          => 'STATUS',
            'deadline'        => 'DEADLINE',
            'internship_id'   => 'ID',
            'applications'    => 'APPLICATIONS',
        ];

        $headers = !empty($rows) ? array_keys($rows[0]) : [];
        $displayHeaders = array_map(function($h) use ($headerMap) {
            return $headerMap[$h] ?? strtoupper(str_replace('_', ' ', $h));
        }, $headers);

        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '_', $report['report_name']) . '.pdf';
        Pdf::generate($report['report_name'], $headers, $rows, $filename, $displayHeaders);
        exit();
    }

    $_SESSION['error'] = __("Report not found for PDF download.");
    header("Location: reports.php");
    exit();
}

// Generate report
if (isset($_POST['generate_report'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = __("Invalid form submission.");
    } else {
    try {
        $report_type = $_POST['report_type'] ?? '';

        switch ($report_type) {
            case 'applications':
                $reportObj->generateApplicationsReport($_SESSION['user_id']);
                $_SESSION['message'] = __("Applications report generated successfully.");
                break;
            case 'internships':
                $reportObj->generateInternshipsReport($_SESSION['user_id']);
                $_SESSION['message'] = __("Internships report generated successfully.");
                break;
            case 'students':
                $reportObj->generateStudentsReport($_SESSION['user_id']);
                $_SESSION['message'] = __("Students report generated successfully.");
                break;
            default:
                $_SESSION['error'] = __("Invalid report type.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = __("Error generating report: ") . $e->getMessage();
        error_log("Report generation error: " . $e->getMessage());
    }
    header("Location: reports.php");
    exit();
    }
}

// Search reports
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $reports = $reportObj->searchReports($search);
} else {
    $reports = $reportObj->getAllReports();
}

// Delete report
if (isset($_POST['delete']) && isset($_POST['report_id'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = __("Invalid form submission.");
    } else {
    try {
        $report_id = filter_input(INPUT_POST, 'report_id', FILTER_VALIDATE_INT);
        if ($report_id === false || $report_id === null) {
            $_SESSION['error'] = __("Invalid report ID.");
        } elseif ($reportObj->deleteReport($report_id)) {
            $_SESSION['message'] = __("Report deleted successfully.");
        } else {
            $_SESSION['error'] = __("Failed to delete report.");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = __("Error deleting report: ") . $e->getMessage();
    }
    }
    header("Location: reports.php");
    exit();
}
?>

<?php include "../layouts/header.php"; ?>
<?php include "../layouts/sidebar.php"; ?>

<div class="card">
    <h2 class="center"><?= __('Generate Reports') ?></h2>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
        </p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
            <label for="report_type"><?= __('Select Report Type:') ?></label>
            <select name="report_type" id="report_type" required>
                <option value=""><?= __('-- Choose a Report --') ?></option>
                <option value="applications"><?= __('Applications Report') ?></option>
                <option value="internships"><?= __('Internships Report') ?></option>
                <option value="students"><?= __('Students Report') ?></option>
            </select>
        </div>
        <button type="submit" name="generate_report" class="btn"><?= __('Generate Report') ?></button>
    </form>
    <div style="margin-top:15px;text-align:center;">
        <a href="internship-details.php" class="btn btn-primary" style="display:inline-block;">
            <?= __('View Internship Details') ?>
        </a>
    </div>
</div>

<div class="card">
    <h2 class="center"><?= __('Search Reports') ?></h2>
    <form method="GET">
        <input type="text" name="search" placeholder="<?= __('Search by report name or type...') ?>"
               value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn"><?= __('Search') ?></button>
        <?php if ($search): ?>
            <a href="reports.php" class="btn btn-secondary"><?= __('Clear') ?></a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <h2 class="center"><?= __('Generated Reports') ?></h2>

    <?php if ($reports && $reports->rowCount() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th><?= __('ID') ?></th>
                    <th><?= __('Report Name') ?></th>
                    <th><?= __('Type') ?></th>
                    <th><?= __('Generated By') ?></th>
                    <th><?= __('Date') ?></th>
                    <th><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $reports->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td data-label="ID"><?= htmlspecialchars($row['report_id']) ?></td>
                        <td data-label="Report Name"><?= htmlspecialchars($row['report_name']) ?></td>
                        <td data-label="Type">
                            <span class="badge badge-primary">
                                <?= htmlspecialchars($row['report_type']) ?>
                            </span>
                        </td>
                        <td data-label="Generated By"><?= htmlspecialchars($row['username'] ?? __('Unknown')) ?></td>
                        <td data-label="Date"><?= htmlspecialchars(date('M d, Y H:i', strtotime($row['generated_date']))) ?></td>
                        <td data-label="Actions">
                            <div class="action-group">
                                <a href="reports.php?download=<?= $row['report_id']; ?>"
                                   class="btn btn-sm btn-success"><?= __('CSV') ?></a>
                                <a href="reports.php?pdf=<?= $row['report_id']; ?>"
                                   class="btn btn-sm btn-primary"><?= __('PDF') ?></a>
                                <form method="POST" style="display:inline;" onsubmit="return confirm('<?= __('Are you sure you want to delete this report?') ?>');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="report_id" value="<?= $row['report_id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-sm btn-danger"><?= __('Delete') ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="empty-state">
            <?= __('No reports found. Generate a new report to get started.') ?>
        </p>
    <?php endif; ?>
</div>

<?php include "../layouts/footer.php"; ?>
