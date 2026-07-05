<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Internship.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$internshipObj = new Internship();

if (isset($_GET['delete'])) {

    $internship_id = filter_input(INPUT_GET, 'delete', FILTER_VALIDATE_INT);

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

    header("Location: internships.php");
    exit();
}
$internships = $internshipObj->getInternships();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Internships</title>
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
        <a href="users.php">Users</a>
        <a href="students.php">Students</a>
        <a href="companies.php">Companies</a>
        <a href="internships.php" class="active">Internships</a>
        <a href="applications.php">Applications</a>
        <a href="reports.php">Reports</a>
        <a href="../authentication/logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="card">
            <h2>All Internship Opportunities</h2>
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
                    <th>Title</th>
                    <th>Company</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $internships->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['internship_id']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['company_name']) ?></td>
                        <td><?= htmlspecialchars($row['deadline']) ?></td>
                        <td>
                            <a href="internships.php?delete=<?php echo $row['internship_id']; ?>" 
                               onclick="return confirm('Delete this internship?');" 
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
