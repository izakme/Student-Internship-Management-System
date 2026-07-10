<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Internship.php";
require_once "../../backend/classes/company.php";
require_once "../../backend/helpers/csrf.php";

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
    die("Company profile not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_internship'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $requirements = $_POST['requirements'] ?? '';
    $deadline = $_POST['deadline'] ?? '';

    if ($title && $description && $deadline) {
        if ($internshipObj->addInternship($company_id, $title, $description, $requirements, $deadline)) {
            $_SESSION['message'] = "Internship posted successfully.";
        } else {
            $_SESSION['error'] = "Error posting internship.";
        }
        header("Location: internships.php");
        exit();
    }
    }
}

if (isset($_GET['delete'])) {
    $internship_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    $internship = $internshipObj->getInternship($internship_id);
    if ($internship && $internship['company_id'] == $company_id) {
        $result = $internshipObj->deleteInternship($internship_id);
        if ($result['success']) {
            $_SESSION['message'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }
    } else {
        $_SESSION['error'] = "Internship not found or unauthorized.";
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
    <h2>Post New Internship</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form method="POST">
        <?= csrfField() ?>
        <div class="form-group">
            <label>Title *</label>
            <input type="text" name="title" required>
        </div>

        <div class="form-group">
            <label>Description *</label>
            <textarea name="description" required></textarea>
        </div>

        <div class="form-group">
            <label>Requirements</label>
            <textarea name="requirements"></textarea>
        </div>

        <div class="form-group">
            <label>Deadline *</label>
            <input type="date" name="deadline" required>
        </div>

        <button type="submit" name="add_internship" class="btn">Post Internship</button>
    </form>
</div>

<div class="card">
    <h2>My Internship Listings</h2>

    <div class="table-wrap">
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Deadline</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php if ($internships->rowCount() > 0): ?>
            <?php while ($row = $internships->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td data-label="ID"><?= htmlspecialchars($row['internship_id']) ?></td>
                    <td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
                    <td data-label="Deadline"><?= htmlspecialchars($row['deadline']) ?></td>
                    <td data-label="Actions">
                        <a href="internships.php?delete=<?php echo $row['internship_id']; ?>"
                           onclick="return confirm('Delete this internship?');"
                           class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="center">No internships posted yet.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include "../layouts/footer.php"; ?>
