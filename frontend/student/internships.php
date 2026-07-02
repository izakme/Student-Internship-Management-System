<?php
require_once __DIR__ . "/../../backend/classes/Internship.php";

$internship = new Internship();
$stmt = $internship->getInternships();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Internships</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            background:#f4f4f4;
            margin:20px;
        }

        h2{
            text-align:center;
            margin-bottom:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
            background:#fff;
        }

        th,td{
            padding:12px;
            border:1px solid #ddd;
        }

        th{
            background:#0d6efd;
            color:#fff;
        }

        tr:nth-child(even){
            background:#f9f9f9;
        }

        .no-data{
            text-align:center;
            color:red;
            font-weight:bold;
        }
    </style>

</head>
<body>

<div class="page-header">
    <a href="/S-I-M-S/frontend/student/dashboard.php" class="back-btn">
    ← Back to Dashboard
</a>

    <h2>Available Internships</h2>
</div>

<table>
    <thead>
        <tr>
            <th>Company</th>
            <th>Title</th>
            <th>Description</th>
            <th>Requirements</th>
            <th>Deadline</th>
        </tr>
    </thead>

    <tbody>

<?php

if($stmt->rowCount() > 0){

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){ ?>

        <tr>
            <td><?php echo htmlspecialchars($row['company_name']); ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['description']); ?></td>
            <td><?php echo htmlspecialchars($row['requirements']); ?></td>
            <td><?php echo htmlspecialchars($row['deadline']); ?></td>
        </tr>

<?php
    }

}else{
?>

<tr>
    <td colspan="5" class="no-data">
        No internships available.
    </td>
</tr>

<?php
}
?>

    </tbody>
</table>

</body>
</html>