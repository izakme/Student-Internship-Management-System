<?php
session_start();

require_once "../../backend/classes/Student.php";
require_once "../../backend/helpers/csrf.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$student = new Student();
$data = $student->getStudentByUser($user_id);

if (!$data) {
    die("<h3 style='color:red;text-align:center;margin-top:50px;'>Student profile not found. Please contact admin.</h3>");
}

$student_id = $data['student_id'];

require_once "../../backend/helpers/App.php";

$uploadDir = __DIR__ . '/../../uploads/resumes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['error'] = "Invalid form submission.";
    } else {
        try {
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
require_once "../../backend/helpers/App.php";
                if (!App::validateFileType($_FILES['resume']['tmp_name'])) {
                    throw new Exception("Only PDF files are allowed.");
                }
                if ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
                    throw new Exception("File size must be under 5MB.");
                }
                $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
                $filename = 'resume_' . $student_id . '_' . time() . '.' . $ext;
                $destPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $destPath)) {
                    $student->updateResume($student_id, 'uploads/resumes/' . $filename);
                    $_SESSION['message'] = "Resume uploaded successfully.";
                    $data = $student->getStudentByUser($user_id);
                } else {
                    throw new Exception("Failed to upload file.");
                }
            } else {
                $registration_no = isset($_POST['registration_no']) ? trim($_POST['registration_no']) : '';
                $course = isset($_POST['course']) ? trim($_POST['course']) : '';
                $year_of_study = isset($_POST['year_of_study']) ? (int)$_POST['year_of_study'] : 0;
                $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
                $result = $student->updateStudent($student_id, $registration_no, $course, $year_of_study, $phone);
                if ($result) {
                    $_SESSION['message'] = "Profile updated successfully.";
                    $data = $student->getStudentByUser($user_id);
                } else {
                    $_SESSION['error'] = "Failed to update profile. Please try again.";
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
            error_log("Profile update error: " . $e->getMessage());
        }
    }
}

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card">
    <h2 class="center">My Profile</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="success-msg"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <p class="error-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" value="<?= htmlspecialchars($data['full_name'] ?? '') ?>" disabled>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" disabled>
        </div>
        <div class="form-group">
            <label>Registration Number</label>
            <input type="text" name="registration_no" value="<?= htmlspecialchars($data['registration_no'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Course</label>
            <input type="text" name="course" value="<?= htmlspecialchars($data['course'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Year of Study</label>
            <input type="number" name="year_of_study" value="<?= htmlspecialchars($data['year_of_study'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Resume/CV (PDF only, max 5MB)</label>
            <?php if (!empty($data['resume'])): ?>
                <p style="margin-bottom: 8px;">
                    <a href="<?= htmlspecialchars(App::baseUrl() . '/' . $data['resume']) ?>" target="_blank" class="btn btn-sm btn-success">View Resume</a>
                </p>
            <?php endif; ?>
            <input type="file" name="resume" accept=".pdf,application/pdf">
        </div>
        <button type="submit" class="btn" onclick="this.form.enctype.value='multipart/form-data'">Update Profile</button>
    </form>
</div>

<?php include "../layouts/footer.php"; ?>
