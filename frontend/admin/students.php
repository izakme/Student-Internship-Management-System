<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Student.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$db = (new Database())->connect();
$studentObj = new Student();

$students = $studentObj->getAllStudents();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
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
        <a href="students.php" class="active">Students</a>
        <a href="companies.php">Companies</a>
        <a href="internships.php">Internships</a>
        <a href="applications.php">Applications</a>
        <a href="reports.php">Reports</a>
        <a href="../authentication/logout.php">Logout</a>
    </div>

    <div class="content">
        <div class="card">
            <h2>Registered Students</h2>
        </div>

        <div class="card">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Registration No</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Phone</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['student_id']) ?></td>
                        <td><?= htmlspecialchars($row['full_name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['registration_no'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['course'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['year_of_study'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../layouts/footer.php"; ?>
