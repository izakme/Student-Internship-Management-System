<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/user.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$db = (new Database())->connect();
$userObj = new User($db);

// Delete user
if (isset($_GET['delete'])) {
    $user_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);
    if ($user_id === false || $user_id === null) {
        $_SESSION['error'] = "Invalid user ID.";
    } elseif ($user_id === (int)$_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account.";
    } elseif ($userObj->deleteUser($user_id)) {
        $_SESSION['message'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete user.";
    }
    header("Location: users.php");
    exit();
}

$users = $userObj->getAllUsers();

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
</div>

<div class="card">
    <table>
        <thead>
        <tr>
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
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['full_name']) ?></td>
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
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                    <a href="users.php?delete=<?php echo $row['user_id']; ?>" 
                       onclick="return confirm('Delete this user?');" 
                       class="btn btn-danger" style="padding:5px 10px;font-size:12px;">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../layouts/footer.php"; ?>
