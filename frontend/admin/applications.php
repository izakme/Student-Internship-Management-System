<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Application.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$appObj = new Application();

// Update status
if (isset($_POST['application_id']) && isset($_POST['status'])) {
    $app_id = filter_input(INPUT_POST, 'application_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'];
    
    if ($appObj->updateStatus($app_id, $status)) {
        $_SESSION['message'] = "Application status updated.";
    } else {
        $_SESSION['error'] = "Invalid application or status.";
    }
    header("Location: applications.php");
    exit();
}

// Delete application
if (isset($_GET['delete'])) {
    $app_id = $_GET['delete'];
    if ($appObj->deleteApplication($app_id)) {
        $_SESSION['message'] = "Application deleted.";
    }
    header("Location: applications.php");
    exit();
}

$applications = $appObj->getApplications();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Applications</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .status-form {
            display: inline-flex;
            gap: 5px;
        }
        .status-form select {
            padding: 5px;
            border-radius: 3px;
            border: 1px solid #ccc;
        }
        .status-form button {
            padding: 5px 10px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="topbar">
    Internship Management System
</div>

<div class="layout">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <a href="dashboard.php">Dashboard</a>
        <a href="users.php">Users</a>
        <a href="students.php">Students</a>
        <a href="companies.php">Companies</a>
        <a href="internships.php">Internships</a>
        <a href="applications.php" class="active">Applications</a>
        <a href="reports.php">Reports</a>
        <a href="../authentication/logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="card">
            <h2>All Applications</h2>
            <?php if (isset($_SESSION['message'])): ?>
                <p class="success-msg"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error-msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Registration No</th>
                    <th>Internship</th>
                    <th>Status</th>
                    <th>Applied Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $applications->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['application_id']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['registration_no']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">
                                <select name="status">
                                    <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Accepted" <?= $row['status'] === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                                    <option value="Rejected" <?= $row['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                                <button type="submit">Update</button>
                            </form>
                        </td>
                        <td><?= htmlspecialchars($row['application_date']) ?></td>
                        <td>
                            <a href="applications.php?delete=<?php echo $row['application_id']; ?>" 
                               onclick="return confirm('Delete this application?');" 
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
