<?php
session_start();

require_once __DIR__ . "/../../backend/classes/Application.php";

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
header("Location: ../authentication/login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

$application = new Application();
$applications = $application->getStudentApplications($student_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Applications</title>

<style>
body{
    font-family:Arial,sans-serif;
    background:#f4f4f4;
    margin:20px;
}

.container{
    width:95%;
    margin:auto;
}

h2{
    text-align:center;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th,td{
    padding:12px;
    border:1px solid #ddd;
    text-align:left;
}

th{
    background:#0d6efd;
    color:white;
}

tr:nth-child(even){
    background:#f9f9f9;
}

.pending{
    color:orange;
    font-weight:bold;
}

.accepted{
    color:green;
    font-weight:bold;
}

.rejected{
    color:red;
    font-weight:bold;
}

.no-record{
    text-align:center;
    color:red;
    font-weight:bold;
}
</style>

</head>
<body>

<div class="container">

<h2>My Internship Applications</h2>

<table>

<thead>
<tr>
    <th>Application ID</th>
    <th>Internship</th>
    <th>Status</th>
    <th>Application Date</th>
</tr>
</thead>

<tbody>

<?php
if ($applications->rowCount() > 0) {

    while ($row = $applications->fetch(PDO::FETCH_ASSOC)) {

        $statusClass = strtolower($row['status']);

        echo "<tr>";
        echo "<td>".$row['application_id']."</td>";
        echo "<td>".htmlspecialchars($row['title'])."</td>";
        echo "<td class='$statusClass'>".$row['status']."</td>";
        echo "<td>".$row['application_date']."</td>";
        echo "</tr>";
    }

} else {
?>

<tr>
<td colspan="4" class="no-record">
No internship applications found.
</td>
</tr>

<?php
}
?>

</tbody>

</table>

</div>

</body>
</html>