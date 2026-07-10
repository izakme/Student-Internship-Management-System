<?php
session_start();

require_once "../../backend/config/database.php";

if (empty($_SESSION['user_id'])) {
    header("Location: ../authentication/login.php");
    exit();
}

$database = new Database();
$conn = $database->connect();

$stmt = $conn->prepare("SELECT student_id FROM students WHERE user_id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$student_id = $stmt->fetchColumn();

if (!$student_id) {
    die("<div style='text-align:center;margin-top:60px;color:red;font-size:18px;'>Student profile not found.<br>Please ensure your account is linked to a student profile.</div>");
}

$stmt = $conn->prepare("
    SELECT a.application_id, i.title, a.status, a.application_date
    FROM applications a
    INNER JOIN internships i ON a.internship_id = i.internship_id
    WHERE a.student_id = ?
    ORDER BY a.application_date DESC
");
$stmt->execute([$student_id]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2 class="center">My Internship Applications</h2>

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
                        <td data-label="ID"><?= htmlspecialchars($row['application_id']) ?></td>
                        <td data-label="Internship"><?= htmlspecialchars($row['title']) ?></td>
                        <td data-label="Status">
                            <?php if ($row['status'] === "Accepted"): ?>
                                <span class="badge badge-success">Accepted</span>
                            <?php elseif ($row['status'] === "Rejected"): ?>
                                <span class="badge badge-danger">Rejected</span>
                            <?php else: ?>
                                <span class="badge badge-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td data-label="Date"><?= htmlspecialchars($row['application_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="center">No applications found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../layouts/footer.php"; ?>
