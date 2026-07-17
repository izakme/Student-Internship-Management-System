<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Internship.php";
require_once "../../backend/classes/company.php";
require_once "../../backend/helpers/csrf.php";
require_once "../../backend/helpers/Language.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'company') {
    header("Location: ../authentication/login.php");
    exit();
}

$company_user_id = $_SESSION['user_id'];
$db = (new Database())->connect();
$companyObj = new Company();
$internshipObj = new Internship();

$company = $companyObj->getCompanyByUserId($company_user_id);
$company_id = $company['company_id'] ?? null;

if (!$company_id) {
    die(__("Company profile not found."));
}

$editInternship = null;
if (isset($_GET['edit'])) {
    $edit_id = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    $editInternship = $internshipObj->getInternship($edit_id);
    if (!$editInternship || $editInternship['company_id'] != $company_id) {
        $editInternship = null;
        $_SESSION['error'] = __("Internship not found or unauthorized.");
        header("Location: internships.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['save_internship']) || isset($_POST['title']))) {
    error_log("[SIMS] POST handler entered for company_id=$company_id, save_internship=" . $_POST['save_internship']);
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        error_log("[SIMS] CSRF validation FAILED");
        $_SESSION['error'] = __("Invalid form submission.");
    } else {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $deadline = trim($_POST['deadline'] ?? '');

    error_log("[SIMS] title='$title' deadline='$deadline' all_filled=" . ($title && $description && $requirements && $deadline ? 'yes' : 'no'));

    if ($title && $description && $requirements && $deadline) {
        $internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);

        if ($internship_id) {
            // Update existing
            $internship = $internshipObj->getInternship($internship_id);
            if ($internship && $internship['company_id'] == $company_id) {
                if ($internshipObj->updateInternship($internship_id, $title, $description, $requirements, $deadline)) {
                    $_SESSION['message'] = __("Internship updated successfully.");
                } else {
                    $_SESSION['error'] = __("Error updating internship.");
                }
            } else {
                $_SESSION['error'] = __("Internship not found or unauthorized.");
            }
        } else {
            // Create new
            try {
                error_log("[SIMS] Calling addInternship(company_id=$company_id)");
                $result = $internshipObj->addInternship($company_id, $title, $description, $requirements, $deadline);
                error_log("[SIMS] addInternship returned " . ($result ? 'true' : 'false'));
                if ($result) {
                    $_SESSION['message'] = __("Internship posted successfully.");
                } else {
                    error_log("[SIMS] addInternship returned false for company_id=$company_id title=$title");
                    $_SESSION['error'] = __("Error posting internship. Please check the logs.");
                }
            } catch (Exception $e) {
                error_log("[SIMS] addInternship EXCEPTION: " . $e->getMessage());
                $_SESSION['error'] = __("Database error: ") . $e->getMessage();
            }
        }
        header("Location: internships.php");
        exit();
    } else {
        error_log("[SIMS] Field validation FAILED");
        $_SESSION['error'] = __("Please fill all required fields.");
    }
    }
}

if (isset($_POST['delete']) && isset($_POST['internship_id'])) {
    error_log("[SIMS] DELETE handler entered, internship_id=" . $_POST['internship_id']);
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        error_log("[SIMS] DELETE CSRF FAILED");
        $_SESSION['error'] = __("Invalid form submission.");
    } else {
    $internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
    error_log("[SIMS] DELETE validated internship_id=$internship_id");
    $internship = $internshipObj->getInternship($internship_id);
    if ($internship && $internship['company_id'] == $company_id) {
        $result = $internshipObj->deleteInternship($internship_id);
        error_log("[SIMS] DELETE result: " . ($result['success'] ? 'success' : 'fail') . " - " . $result['message']);
        if ($result['success']) {
            $_SESSION['message'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } else {
        error_log("[SIMS] DELETE unauthorized. internship=" . var_export($internship, true) . " company_id=$company_id");
        $_SESSION['error'] = __("Internship not found or unauthorized.");
    }
    }
    header("Location: internships.php");
    exit();
}

$stmt = $db->prepare("SELECT * FROM internships WHERE company_id = ? ORDER BY internship_id DESC");
$stmt->execute([$company_id]);
$internships = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>



<div class="card">
    <h2><?php echo $editInternship ? __('Edit Internship') : __('Post New Internship'); ?></h2>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form method="POST">
        <?= csrfField() ?>
        <?php if ($editInternship): ?>
            <input type="hidden" name="internship_id" value="<?php echo $editInternship['internship_id']; ?>">
        <?php endif; ?>
        <div class="form-group">
            <label><?= __('Title') ?> *</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($editInternship['title'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label><?= __('Description') ?> *</label>
            <textarea name="description" required><?php echo htmlspecialchars($editInternship['description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label><?= __('Requirements') ?> *</label>
            <textarea name="requirements" required><?php echo htmlspecialchars($editInternship['requirements'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label><?= __('Deadline') ?> *</label>
            <input type="date" name="deadline" value="<?php echo htmlspecialchars($editInternship['deadline'] ?? ''); ?>" required>
        </div>

        <div class="form-buttons">
            <button type="submit" name="save_internship" class="btn"><?php echo $editInternship ? __('Update Internship') : __('Post Internship'); ?></button>
            <?php if ($editInternship): ?>
                <a href="internships.php" class="btn btn-secondary"><?= __('Cancel') ?></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h2 style="margin:0"><?= __('My Internship Listings') ?></h2>
        <?php if (count($internships) > 0): ?>
        <button onclick="window.print()" class="btn btn-sm no-print" style="display:inline-flex;align-items:center;gap:6px;">
            <i class="fas fa-print"></i> <?= __('Print') ?>
        </button>
        <?php endif; ?>
    </div>

    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th><?= __('ID') ?></th>
            <th><?= __('Title') ?></th>
            <th><?= __('Deadline') ?></th>
            <th class="no-print"><?= __('Actions') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if (count($internships) > 0): ?>
            <?php foreach ($internships as $row): ?>
                <tr>
                    <td data-label="ID"><?= htmlspecialchars($row['internship_id']) ?></td>
                    <td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
                    <td data-label="Deadline"><?= htmlspecialchars(date("M j, Y", strtotime($row['deadline']))) ?></td>
                    <td data-label="Actions" class="no-print">
                        <div class="action-group">
                            <a href="internships.php?edit=<?php echo $row['internship_id']; ?>"
                               class="btn btn-sm"><?= __('Edit') ?></a>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('<?= __('Delete this internship?') ?>');">
                                <?= csrfField() ?>
                                <input type="hidden" name="internship_id" value="<?= $row['internship_id'] ?>">
                                <button type="submit" name="delete" class="btn btn-danger btn-sm"><?= __('Delete') ?></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="center"><?= __('No internships posted yet.') ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<style>
@media print {
    .topbar, .sidebar, .sidebar-overlay, .site-footer, .no-print,
    .card:first-of-type { display: none !important; }
    .layout, .content { margin: 0 !important; padding: 0 !important; }
    body { background: white !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; }
    table { font-size: 12px; }
    th { background: #0d6efd !important; color: white !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<?php include "../layouts/footer.php"; ?>
