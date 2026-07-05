<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../backend/classes/Student.php";

/* =========================
   LOGIN CHECK
========================= */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../authentication/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$student = new Student();
$data = $student->getStudentByUser($user_id);

/* =========================
   HANDLE MISSING PROFILE
========================= */
if (!$data) {
    die("
        <h3 style='color:red;text-align:center;margin-top:50px;'>
            Student profile not found. Please contact admin.
        </h3>
    ");
}

$student_id = $data['student_id'];

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        error_log("Profile update error: " . $e->getMessage());
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-page">

<?php include '../layouts/header.php'; ?>

<div class="layout">
    <?php include '../layouts/sidebar.php'; ?>

    <div class="content">
        <div class="card">
            <h2 class="center">My Profile</h2>

            <?php if (isset($_SESSION['message'])): ?>
                <p style="color: #28a745; background: #d4edda; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                </p>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <p style="color: #dc3545; background: #f8d7da; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </p>
            <?php endif; ?>

            <form method="POST">
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

                <button type="submit" class="btn">Update Profile</button>
            </form>

        </div>
    </div>
</div>

<?php include "../layouts/footer.php"; ?>
