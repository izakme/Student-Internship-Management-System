<?php

session_start();

require_once "../../backend/config/database.php";

$database = new Database();

$conn = $database->connect();

$sql="SELECT

s.student_id,
u.full_name,
u.email,
s.registration_no,
s.course,
s.year_of_study,
s.phone

FROM students s

JOIN users u

ON s.user_id=u.user_id

ORDER BY u.full_name";

$stmt=$conn->query($sql);

?>

<!DOCTYPE html>

<html>

<head>

<title>Students</title>

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

<table>

<tr>

<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Registration</th>
<th>Course</th>
<th>Year</th>
<th>Phone</th>

</tr>

<?php while($row=$stmt->fetch(PDO::FETCH_ASSOC)): ?>

<tr>

<td><?= $row['student_id']; ?></td>

<td><?= $row['full_name']; ?></td>

<td><?= $row['email']; ?></td>

<td><?= $row['registration_no']; ?></td>

<td><?= $row['course']; ?></td>

<td><?= $row['year_of_study']; ?></td>

<td><?= $row['phone']; ?></td>

</tr>

<?php endwhile; ?>

</table>

</div>

</div>

</div>

</body>

</html>