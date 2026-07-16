<?php
session_start();
require_once __DIR__ . "/../../backend/helpers/Language.php";
require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";
require_once __DIR__ . "/../../backend/helpers/csrf.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../authentication/login.php");
    exit;
}

if (!isset($_SESSION['student_id'])) {
    die(__("Student ID not found in session. Please log in again."));
}

$internship = new Internship();
$application = new Application();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = __("Invalid form submission.");
    } else {
    $internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
    $student_id = $_SESSION['student_id'];
    $cover_letter = trim($_POST['cover_letter'] ?? '');

    if ($internship_id && !$application->hasApplied($student_id, $internship_id)) {
        if ($application->apply($student_id, $internship_id, $cover_letter)) {
            $_SESSION['message'] = __("Application submitted successfully.");
        } else {
            $_SESSION['error'] = __("Failed to submit application. The internship may be expired.");
        }
    } else {
        $_SESSION['error'] = __("You have already applied for this internship.");
    }

    header("Location: internships.php");
    exit;
    }
}

$stmt = $internship->activeInternships();

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2 class="center"><?= __('Available Internships') ?></h2>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <?php if ($stmt->rowCount() > 0): ?>

    <div class="table-wrap">
    <table class="internships-table">
        <colgroup>
            <col class="col-company">
            <col class="col-title">
            <col class="col-desc">
            <col class="col-req">
            <col class="col-deadline">
            <col class="col-action">
        </colgroup>
        <thead>
            <tr>
                <th><?= __('Company') ?></th>
                <th><?= __('Title') ?></th>
                <th><?= __('Description') ?></th>
                <th><?= __('Requirements') ?></th>
                <th><?= __('Deadline') ?></th>
                <th><?= __('Action') ?></th>
            </tr>
        </thead>
        <tbody>

        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
            $has_applied = $application->hasApplied($_SESSION['student_id'], $row['internship_id']);
        ?>

            <tr>
                <td data-label="Company"><?php echo htmlspecialchars($row['company_name']); ?></td>
                <td data-label="Title"><?php echo htmlspecialchars($row['title']); ?></td>
                <td data-label="Description"><?php echo htmlspecialchars($row['description']); ?></td>
                <td data-label="Requirements"><?php echo htmlspecialchars($row['requirements']); ?></td>
                <td data-label="Deadline"><?php echo htmlspecialchars(date("M j, Y", strtotime($row['deadline']))); ?></td>
                <td data-label="Action">
                    <?php if ($has_applied): ?>
                        <span class="badge badge-success"><i class="fas fa-check"></i> <?= __('Applied') ?></span>
                    <?php else: ?>
                        <form method="POST" class="inline-form" onsubmit="return confirm('<?= __("Submit application?") ?>');">
                            <?= csrfField() ?>
                            <input type="hidden" name="internship_id" value="<?= $row['internship_id'] ?>">
                            <input type="hidden" name="apply" value="1">
                            <textarea name="cover_letter" placeholder="<?= __('Cover letter (optional)') ?>..." rows="2" style="width:100%;font-size:11px;padding:4px;"></textarea>
                            <button type="submit" class="btn btn-sm" style="font-size:11px;"><?= __('Apply') ?></button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>

        <?php endwhile; ?>

        </tbody>
    </table>
    </div>

    <?php else: ?>
        <div class="empty-state"><?= __('No internships available at the moment.') ?></div>
    <?php endif; ?>
</div>

<?php include "../layouts/footer.php"; ?>
