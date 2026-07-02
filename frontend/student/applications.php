<?php
session_start();

require_once "../../backend/config/database.php";

/* =========================
   LOGIN CHECK
========================= */
if (empty($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

/* =========================
   GET student_id (CRITICAL FIX)
========================= */
$stmt = $conn->prepare("
    SELECT student_id
    FROM students
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['user_id']]);
$student_id = $stmt->fetchColumn();

/* =========================
   VALIDATION
========================= */
if (!$student_id) {
    die("
        <div style='text-align:center;margin-top:60px;color:red;font-size:18px;'>
            Student profile not found.<br>
            Please ensure your account is linked to a student profile.
        </div>
    ");
}

/* =========================
   FETCH APPLICATIONS
========================= */
$stmt = $conn->prepare("
    SELECT
        a.application_id,
        i.title,
        a.status,
        a.application_date
    FROM applications a
    INNER JOIN internships i
        ON a.internship_id = i.internship_id
    WHERE a.student_id = ?
    ORDER BY a.application_date DESC
");

$stmt->execute([$student_id]);

$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Applications</title>

<link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="dashboard-page">

<?php include "../layouts/header.php"; ?>
<?php include "../layouts/sidebar.php"; ?>

<div class="content">

<div class="card">

<div class="page-header">
    <h2>My Internship Applications</h2>
</div>

<table>

<thead>
<tr>
    <th>ID</th>
    <th>Internship</th>
    <th>Status</th>
    <th>Date</th>
</tr>
</thead>

<tbody>

<?php if (!empty($applications)): ?>

    <?php foreach ($applications as $row): ?>

        <tr>
            <td><?= htmlspecialchars($row['application_id']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>

            <td>
                <?php if ($row['status'] === "Accepted"): ?>
                    <span class="badge badge-success">Accepted</span>
                <?php elseif ($row['status'] === "Rejected"): ?>
                    <span class="badge badge-danger">Rejected</span>
                <?php else: ?>
                    <span class="badge badge-pending">Pending</span>
                <?php endif; ?>
            </td>

            <td><?= htmlspecialchars($row['application_date']) ?></td>
        </tr>

    <?php endforeach; ?>

<?php else: ?>

    <tr>
        <td colspan="4" class="center">
            No applications found.
        </td>
    </tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

<?php include "../layouts/footer.php"; ?>

</body>
</html>