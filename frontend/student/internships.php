<?php
session_start();
require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";
require_once __DIR__ . "/../../backend/helpers/csrf.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../authentication/login.php");
    exit;
}

if (!isset($_SESSION['student_id'])) {
    die("Student ID not found in session. Please log in again.");
}

$internship = new Internship();
$application = new Application();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
    $internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
    $student_id = $_SESSION['student_id'];
    $cover_letter = trim($_POST['cover_letter'] ?? '');

    if ($internship_id && !$application->hasApplied($student_id, $internship_id)) {
        if ($application->apply($student_id, $internship_id, $cover_letter)) {
            $_SESSION['message'] = "Application submitted successfully.";
        } else {
            $_SESSION['error'] = "Failed to submit application. The internship may be expired.";
        }
    } else {
        $_SESSION['error'] = "You have already applied for this internship.";
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
    <h2 class="center">Available Internships</h2>

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
                <th>Company</th>
                <th>Title</th>
                <th>Description</th>
                <th>Requirements</th>
                <th>Deadline</th>
                <th>Action</th>
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
                        <span class="badge badge-success"><i class="fas fa-check"></i> Applied</span>
                    <?php else: ?>
                        <button type="button" class="btn btn-sm" onclick="openApplyModal(<?= $row['internship_id'] ?>)">Apply</button>
                    <?php endif; ?>
                </td>
            </tr>

        <?php endwhile; ?>

        </tbody>
    </table>
    </div>

    <?php else: ?>
        <div class="empty-state">No internships available at the moment.</div>
    <?php endif; ?>
</div>

<div class="modal-overlay" id="applyModal" style="display:none;">
    <div class="modal-card">
        <h3>Apply for Internship</h3>
        <form method="POST" id="applyForm">
            <?= csrfField() ?>
            <input type="hidden" name="internship_id" id="modalInternshipId">
            <textarea name="cover_letter" placeholder="Optional cover letter / message to company..." rows="4" style="width:100%;margin-bottom:10px;"></textarea>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" class="btn btn-sm" onclick="closeApplyModal()">Cancel</button>
                <button type="submit" name="apply" class="btn btn-sm">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(0,0,0,0.5); z-index: 1000;
    display: flex; align-items: center; justify-content: center;
}
.modal-card {
    background: #fff; padding: 24px; border-radius: var(--radius);
    max-width: 480px; width: 90%; box-shadow: var(--shadow-hover);
}
</style>

<script>
function openApplyModal(id) {
    document.getElementById('modalInternshipId').value = id;
    document.getElementById('applyModal').style.display = 'flex';
}
function closeApplyModal() {
    document.getElementById('applyModal').style.display = 'none';
}
document.getElementById('applyModal').addEventListener('click', function(e) {
    if (e.target === this) closeApplyModal();
});
</script>

<?php include "../layouts/footer.php"; ?>
