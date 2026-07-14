<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Internship.php";
require_once "../../backend/helpers/csrf.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$internshipObj = new Internship();
$db = (new Database())->connect();

// Bulk delete
if (isset($_POST['bulk_delete']) && isset($_POST['selected'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
        $ids = array_filter($_POST['selected'], 'is_numeric');
        $deleted = 0;
        $errors = [];
        foreach ($ids as $id) {
            $result = $internshipObj->deleteInternship((int)$id);
            if ($result['success']) {
                $deleted++;
            } else {
                $errors[] = $result['message'];
            }
        }
        $_SESSION['message'] = "$deleted internship(s) deleted successfully.";
        if ($errors) {
            $_SESSION['error'] = implode('<br>', array_unique($errors));
        }
    }
    header("Location: internships.php");
    exit();
}

if (isset($_POST['delete_internship']) && isset($_POST['internship_id'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
    $internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
    $internship = $internshipObj->getInternship($internship_id);

    if (!$internship) {
        $_SESSION['error'] = "Internship not found.";
        header("Location: internships.php");
        exit();
    }

    $result = $internshipObj->deleteInternship($internship_id);

    if ($result['success']) {
        $_SESSION['message'] = $result['message'];
    } else {
        $_SESSION['error'] = $result['message'];
    }
    }
    header("Location: internships.php");
    exit();
}

// Filtering
$companyFilter = $_GET['company_id'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

if ($companyFilter || $statusFilter || $search) {
    $sql = "SELECT i.*, c.company_name FROM internships i JOIN companies c ON i.company_id = c.company_id WHERE 1=1";
    $params = [];
    if ($companyFilter) {
        $sql .= " AND i.company_id = ?";
        $params[] = $companyFilter;
    }
    if ($statusFilter === 'active') {
        $sql .= " AND i.deadline >= CURDATE()";
    } elseif ($statusFilter === 'expired') {
        $sql .= " AND i.deadline < CURDATE()";
    }
    if ($search) {
        $sql .= " AND (i.title LIKE ? OR c.company_name LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY i.deadline DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $internships = $stmt;
} else {
    $internships = $internshipObj->getInternships();
}

// Get companies for filter dropdown
$companies = $db->query("SELECT company_id, company_name FROM companies ORDER BY company_name")->fetchAll(PDO::FETCH_ASSOC);

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2>All Internship Opportunities</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form method="GET" class="filter-form" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px;">
        <input type="text" name="search" placeholder="Search title or company..." value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:200px;">
        <select name="company_id">
            <option value="">All Companies</option>
            <?php foreach ($companies as $c): ?>
                <option value="<?= $c['company_id'] ?>" <?= $companyFilter == $c['company_id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['company_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select name="status">
            <option value="">All Status</option>
            <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="expired" <?= $statusFilter === 'expired' ? 'selected' : '' ?>>Expired</option>
        </select>
        <button type="submit" class="btn btn-sm">Filter</button>
        <?php if ($companyFilter || $statusFilter || $search): ?>
            <a href="internships.php" class="btn btn-sm btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <div class="table-wrap">
    <form method="POST" id="bulkForm">
        <?= csrfField() ?>
        <div style="margin-bottom:10px;text-align:left;">
            <label><input type="checkbox" id="selectAll"> Select All</label>
            <button type="submit" name="bulk_delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected internships?');" style="margin-left:10px;">Delete Selected</button>
        </div>
        <table>
            <thead>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Title</th>
                <th>Company</th>
                <th>Deadline</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $internships->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><input type="checkbox" name="selected[]" value="<?= $row['internship_id'] ?>" class="rowCheckbox"></td>
                    <td data-label="ID"><?= htmlspecialchars($row['internship_id']) ?></td>
                    <td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
                    <td data-label="Company"><?= htmlspecialchars($row['company_name']) ?></td>
                    <td data-label="Deadline"><?= htmlspecialchars(date("M j, Y", strtotime($row['deadline']))) ?></td>
                    <td data-label="Actions">
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this internship?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="internship_id" value="<?= $row['internship_id'] ?>">
                            <button type="submit" name="delete_internship" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </form>
    </div>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
});
</script>

<?php include "../layouts/footer.php"; ?>
