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

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2>All Internship Opportunities</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="table-wrap">
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
                <td data-label="ID"><?= htmlspecialchars($row['internship_id']) ?></td>
                <td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
                <td data-label="Company"><?= htmlspecialchars($row['company_name']) ?></td>
                <td data-label="Deadline"><?= htmlspecialchars($row['deadline']) ?></td>
                <td data-label="Actions">
                    <a href="internships.php?delete=<?php echo $row['internship_id']; ?>"
                       onclick="return confirm('Delete this internship?');"
                       class="btn btn-danger btn-sm">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<?php include "../layouts/footer.php"; ?>
