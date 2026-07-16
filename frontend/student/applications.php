<?php
session_start();

require_once __DIR__ . "/../../backend/helpers/Language.php";
require_once "../../backend/config/database.php";
require_once "../../backend/classes/Application.php";
require_once "../../backend/helpers/csrf.php";

if (empty($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

$stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$student_id = $stmt->fetchColumn();

if (!$student_id) {
    die("<div style='text-align:center;margin-top:60px;color:red;font-size:18px;'>" . __("Student profile not found.") . "<br>" . __("Please ensure your account is linked to a student profile.") . "</div>");
}

$app = new Application();

// Handle withdraw
if (isset($_POST['withdraw'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = __("Invalid form submission.");
    } else {
        $application_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
        if ($application_id && $app->withdraw($application_id, $student_id)) {
            $_SESSION['message'] = __("Application withdrawn successfully.");
        } else {
            $_SESSION['error'] = __("Could not withdraw application. Only pending applications can be withdrawn.");
        }
    }
    header("Location: applications.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT a.application_id, i.title, a.status, a.application_date
    FROM applications a
    INNER JOIN internships i ON a.internship_id = i.internship_id
    WHERE a.student_id = ?
    ORDER BY a.application_date DESC
");
$stmt->execute([$student_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2 class="center"><?= __('My Internship Applications') ?></h2>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th><?= __('ID') ?></th>
                <th><?= __('Internship') ?></th>
                <th><?= __('Status') ?></th>
                <th><?= __('Date') ?></th>
                <th><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($applications)): ?>
                <?php foreach ($applications as $row): ?>
                    <tr>
                        <td data-label="ID"><?= htmlspecialchars($row['application_id']) ?></td>
                        <td data-label="Internship"><?= htmlspecialchars($row['title']) ?></td>
                        <td data-label="Status">
                            <?php if ($row['status'] === "Accepted"): ?>
                                <span class="badge badge-success"><?= __('Accepted') ?></span>
                            <?php elseif ($row['status'] === "Rejected"): ?>
                                <span class="badge badge-danger"><?= __('Rejected') ?></span>
                            <?php else: ?>
                                <span class="badge badge-pending"><?= __('Pending') ?></span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Date"><?= htmlspecialchars(date("M j, Y", strtotime($row['application_date']))) ?></td>
                        <td data-label="Actions">
                            <?php if ($row['status'] === 'Pending'): ?>
                                <form method="POST" onsubmit="return confirm('<?= __("Withdraw this application?") ?>');">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">
                                    <button type="submit" name="withdraw" class="btn btn-danger btn-sm"><?= __('Withdraw') ?></button>
                                </form>
                            <?php else: ?>
                                <span class="badge badge-secondary">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="center"><?= __('No applications found.') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../layouts/footer.php"; ?>
