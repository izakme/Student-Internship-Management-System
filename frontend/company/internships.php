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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_internship'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = __("Invalid form submission.");
    } else {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $deadline = $_POST['deadline'] ?? '';

    if ($title && $description && $deadline) {
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
            if ($internshipObj->addInternship($company_id, $title, $description, $requirements, $deadline)) {
                $_SESSION['message'] = __("Internship posted successfully.");
            } else {
                $_SESSION['error'] = __("Error posting internship.");
            }
        }
        header("Location: internships.php");
        exit();
    } else {
        $_SESSION['error'] = __("Please fill all required fields.");
    }
    }
}

if (isset($_POST['delete']) && isset($_POST['internship_id'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = __("Invalid form submission.");
    } else {
    $internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
    $internship = $internshipObj->getInternship($internship_id);
    if ($internship && $internship['company_id'] == $company_id) {
        $result = $internshipObj->deleteInternship($internship_id);
        if ($result['success']) {
            $_SESSION['message'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } else {
        $_SESSION['error'] = __("Internship not found or unauthorized.");
    }
    }
    header("Location: internships.php");
    exit();
}

$stmt = $db->prepare("SELECT * FROM internships WHERE company_id = ? ORDER BY internship_id DESC");
$stmt->execute([$company_id]);
$internships = $stmt;

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
            <label><?= __('Requirements') ?></label>
            <textarea name="requirements"><?php echo htmlspecialchars($editInternship['requirements'] ?? ''); ?></textarea>
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
    <h2><?= __('My Internship Listings') ?></h2>

    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th><?= __('ID') ?></th>
            <th><?= __('Title') ?></th>
            <th><?= __('Deadline') ?></th>
            <th><?= __('Actions') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php if ($internships->rowCount() > 0): ?>
            <?php while ($row = $internships->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td data-label="ID"><?= htmlspecialchars($row['internship_id']) ?></td>
                    <td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
                    <td data-label="Deadline"><?= htmlspecialchars(date("M j, Y", strtotime($row['deadline']))) ?></td>
                    <td data-label="Actions">
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
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="center"><?= __('No internships posted yet.') ?></td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include "../layouts/footer.php"; ?>
