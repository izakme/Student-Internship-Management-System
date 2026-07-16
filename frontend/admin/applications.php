<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Application.php";
require_once "../../backend/helpers/Language.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$appObj = new Application();
$db = (new Database())->connect();

// Filtering
$statusFilter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

if ($statusFilter || $search) {
    $sql = "SELECT a.application_id, s.registration_no, u.username, i.title, a.status, a.application_date
            FROM applications a
            INNER JOIN students s ON a.student_id = s.student_id
            INNER JOIN users u ON s.user_id = u.user_id
            INNER JOIN internships i ON a.internship_id = i.internship_id
            WHERE 1=1";
    $params = [];
    if ($statusFilter) {
        $sql .= " AND a.status = ?";
        $params[] = $statusFilter;
    }
    if ($search) {
        $sql .= " AND (u.username LIKE ? OR i.title LIKE ? OR s.registration_no LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY a.application_date DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt;
} else {
    $applications = $appObj->getApplications();
}

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2><?= __('All Applications') ?></h2>
    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form method="GET" class="filter-form" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px;">
        <input type="text" name="search" placeholder="<?= __('Search student, internship, or reg no...') ?>" value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:200px;">
        <select name="status">
            <option value=""><?= __('All Status') ?></option>
            <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>><?= __('Pending') ?></option>
            <option value="Accepted" <?= $statusFilter === 'Accepted' ? 'selected' : '' ?>><?= __('Accepted') ?></option>
            <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>><?= __('Rejected') ?></option>
        </select>
        <button type="submit" class="btn btn-sm"><?= __('Filter') ?></button>
        <?php if ($statusFilter || $search): ?>
            <a href="applications.php" class="btn btn-sm btn-secondary"><?= __('Clear') ?></a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th><?= __('ID') ?></th>
            <th><?= __('Student') ?></th>
            <th><?= __('Registration No') ?></th>
            <th><?= __('Internship') ?></th>
            <th><?= __('Status') ?></th>
            <th><?= __('Cover Letter') ?></th>
            <th><?= __('Applied Date') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $applications->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td data-label="ID"><?= htmlspecialchars($row['application_id']) ?></td>
                <td data-label="Student"><?= htmlspecialchars($row['username']) ?></td>
                <td data-label="Reg No"><?= htmlspecialchars($row['registration_no']) ?></td>
                <td data-label="Internship"><?= htmlspecialchars($row['title']) ?></td>
                <td data-label="Status">
                    <span class="badge badge-<?php
                        if ($row['status'] === 'Accepted') echo 'success';
                        elseif ($row['status'] === 'Rejected') echo 'danger';
                        else echo 'pending';
                    ?>"><?= htmlspecialchars($row['status']) ?></span>
                </td>
                <td data-label="Cover Letter" style="max-width:200px;">
                    <?php if (!empty($row['cover_letter'])): ?>
                        <em><?= htmlspecialchars($row['cover_letter']) ?></em>
                    <?php else: ?>
                        <span style="color:#999;">—</span>
                    <?php endif; ?>
                </td>
                <td data-label="Date"><?= htmlspecialchars(date("M j, Y", strtotime($row['application_date']))) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../layouts/footer.php"; ?>
