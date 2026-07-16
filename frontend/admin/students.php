<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Student.php";
require_once "../../backend/classes/user.php";
require_once "../../backend/helpers/csrf.php";
require_once "../../backend/helpers/Language.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$db = (new Database())->connect();
$studentObj = new Student();

// Delete student
if (isset($_POST['delete_student']) && isset($_POST['student_id'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = __("Invalid form submission.");
    } else {
        $studentId = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
        $userObj = new User($db);
        if ($studentId) {
            $stmt = $db->prepare("SELECT user_id FROM students WHERE student_id = ?");
            $stmt->execute([$studentId]);
            $stu = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($stu && $studentObj->deleteStudent($studentId)) {
                $userObj->deleteUser($stu['user_id']);
                $_SESSION['message'] = __("Student deleted successfully.");
            } else {
                $_SESSION['error'] = __("Failed to delete student.");
            }
        } else {
            $_SESSION['error'] = __("Invalid student ID.");
        }
    }
    header("Location: students.php");
    exit();
}

$students = $studentObj->getAllStudents();

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2><?= __('Registered Students') ?></h2>
    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th><?= __('ID') ?></th>
            <th><?= __('Name') ?></th>
            <th><?= __('Email') ?></th>
            <th><?= __('Registration No') ?></th>
            <th><?= __('Course') ?></th>
            <th><?= __('Year') ?></th>
            <th><?= __('Phone') ?></th>
            <th><?= __('Actions') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php while ($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['student_id']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['registration_no'] ?? __('N/A')) ?></td>
                <td><?= htmlspecialchars($row['course'] ?? __('N/A')) ?></td>
                <td><?= htmlspecialchars($row['year_of_study'] ?? __('N/A')) ?></td>
                <td><?= htmlspecialchars($row['phone'] ?? __('N/A')) ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('<?= __('Delete this student?') ?>');">
                        <?= csrfField() ?>
                        <input type="hidden" name="student_id" value="<?= $row['student_id'] ?>">
                        <button type="submit" name="delete_student" class="btn btn-danger btn-sm"><?= __('Delete') ?></button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include "../layouts/footer.php"; ?>
