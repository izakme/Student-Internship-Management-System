<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/user.php";
require_once "../../backend/helpers/csrf.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$db = (new Database())->connect();
$userObj = new User($db);

// Bulk delete
if (isset($_POST['bulk_delete']) && isset($_POST['selected'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
        $ids = array_filter($_POST['selected'], 'is_numeric');
        $deleted = 0;
        foreach ($ids as $id) {
            if ((int)$id !== (int)$_SESSION['user_id'] && $userObj->deleteUser((int)$id)) {
                $deleted++;
            }
        }
        $_SESSION['message'] = "$deleted user(s) deleted successfully.";
    }
    header("Location: users.php");
    exit();
}

// Single delete
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    if ($user_id === false || $user_id === null) {
        $_SESSION['error'] = "Invalid user ID.";
    } elseif ($user_id === (int)$_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account.";
    } elseif ($userObj->deleteUser($user_id)) {
        $_SESSION['message'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user.";
    }
    }
    header("Location: users.php");
    exit();
}

// Filtering
$roleFilter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';

if ($roleFilter || $search) {
    $sql = "SELECT * FROM users WHERE 1=1";
    $params = [];
    if ($roleFilter) {
        $sql .= " AND role = ?";
        $params[] = $roleFilter;
    }
    if ($search) {
        $sql .= " AND (username LIKE ? OR email LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $users = $stmt;
} else {
    $users = $userObj->getAllUsers();
}

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2>Manage Users</h2>
    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form method="GET" class="filter-form" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px;">
        <input type="text" name="search" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>" style="flex:1;min-width:200px;">
        <select name="role">
            <option value="">All Roles</option>
            <option value="student" <?= $roleFilter === 'student' ? 'selected' : '' ?>>Student</option>
            <option value="company" <?= $roleFilter === 'company' ? 'selected' : '' ?>>Company</option>
            <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <button type="submit" class="btn btn-sm">Filter</button>
        <?php if ($roleFilter || $search): ?>
            <a href="users.php" class="btn btn-sm btn-secondary">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <form method="POST" id="bulkForm">
        <?= csrfField() ?>
        <div style="margin-bottom:10px;text-align:left;">
            <label><input type="checkbox" id="selectAll"> Select All</label>
            <button type="submit" name="bulk_delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete selected users?');" style="margin-left:10px;">Delete Selected</button>
        </div>
        <table>
            <thead>
            <tr>
                <th></th>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><input type="checkbox" name="selected[]" value="<?= $row['user_id'] ?>" class="rowCheckbox" <?= $row['user_id'] === $_SESSION['user_id'] ? 'disabled' : '' ?>></td>
                    <td><?= htmlspecialchars($row['user_id']) ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <span class="badge badge-<?php
                            if ($row['role'] === 'admin') echo 'danger';
                            elseif ($row['role'] === 'company') echo 'warning';
                            else echo 'success';
                        ?>">
                            <?= ucfirst($row['role']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars(date("M j, Y", strtotime($row['created_at']))) ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Delete this user?');">
                            <?= csrfField() ?>
                            <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>">
                            <button type="submit" name="delete_user" class="btn btn-danger btn-sm" <?= $row['user_id'] === $_SESSION['user_id'] ? 'disabled' : '' ?>>Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </form>
</div>

<script>
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.rowCheckbox').forEach(cb => {
        if (!cb.disabled) cb.checked = this.checked;
    });
});
</script>

<?php include "../layouts/footer.php"; ?>
