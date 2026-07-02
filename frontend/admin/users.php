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
    $user_id = $_GET['delete'];
    if ($userObj->deleteUser($user_id)) {
        $_SESSION['message'] = "User deleted successfully.";
    }
    header("Location: users.php");
    exit();
}

$users = $userObj->getAllUsers();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="topbar">
    Internship Management System
</div>

<div class="layout">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="users.php" class="active">Users</a>
        <a href="students.php">Students</a>
        <a href="companies.php">Companies</a>
        <a href="internships.php">Internships</a>
        <a href="applications.php">Applications</a>
        <a href="reports.php">Reports</a>
        <a href="../authentication/logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="card">
            <h2>Manage Users</h2>
            <?php if (isset($_SESSION['message'])): ?>
                <p class="success-msg"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
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
    </div>
</div>

<?php include "../layouts/footer.php"; ?>
</body>
</html>
