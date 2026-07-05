<?php
session_start();
require_once __DIR__ . "/../../backend/classes/Internship.php";
require_once __DIR__ . "/../../backend/classes/Application.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: /S-I-M-S/frontend/authentication/login.php");
    exit;
}

if (!isset($_SESSION['student_id'])) {
    die("Student ID not found in session. Please log in again.");
}

$internship = new Internship();
$application = new Application();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    $internship_id = filter_input(INPUT_POST, 'internship_id', FILTER_VALIDATE_INT);
    $student_id = $_SESSION['student_id'];

    if ($internship_id && !$application->hasApplied($student_id, $internship_id)) {
        if ($application->apply($student_id, $internship_id)) {
            $_SESSION['message'] = "Application submitted successfully.";
        } else {
            $_SESSION['error'] = "Failed to submit application. The internship may be expired.";
        }
    } else {
        $_SESSION['error'] = "You have already applied for this internship.";
    }

    header("Location: internships.php");
    exit;
}

$stmt = $internship->activeInternships();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Internships</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

<?php include '../layouts/header.php'; ?>

<div class="layout">
    <?php include '../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="card">
            <h2 class="center">Available Internships</h2>
            <?php if (isset($_SESSION['message'])): ?>
                <p class="success-msg"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error-msg"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
            <?php endif; ?>

            <?php if ($stmt->rowCount() > 0): ?>

            <table>
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Requirements</th>
                        <th>Deadline</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                    $has_applied = $application->hasApplied($_SESSION['student_id'], $row['internship_id']);
                ?>

                    <tr>
                        <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo htmlspecialchars($row['requirements']); ?></td>
                        <td><?php echo htmlspecialchars($row['deadline']); ?></td>
                        <td>
                            <?php if ($has_applied): ?>
                                <span style="color: #28a745; font-weight: bold;">✓ Applied</span>
                            <?php else: ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="internship_id" value="<?php echo $row['internship_id']; ?>">
                                    <button type="submit" name="apply" class="btn" style="padding: 8px 16px; font-size: 13px;">Apply</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

                </tbody>
            </table>

            <?php else: ?>
                <p style="text-align: center; color: #999; padding: 40px;">No internships available at the moment.</p>
            <?php endif; ?>

        </div>
    </div>
</div>

<?php include '../layouts/footer.php'; ?>
