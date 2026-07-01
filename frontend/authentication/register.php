<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/user.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {
        $db = (new Database())->connect();
        $user = new User($db);

        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = trim($_POST['password'] ?? '');
        $role      = $_POST['role'] ?? '';

        // VALIDATION
        if (empty($full_name) || empty($email) || empty($password) || empty($role)) {
            $error = "All fields are required.";
        } else {

            $result = $user->register(
                $full_name,
                $email,
                $password,
                $role
            );

            if ($result) {
                $_SESSION['success'] = "Registration successful. You can now log in.";
            } else {
                $error = "Registration failed. Email may already exist.";
            }
        }

    } catch (Exception $e) {
        $error = "System error. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="auth-wrapper">

    <div class="auth-card">

        <h2>Create Account</h2>

        <!-- SUCCESS MESSAGE -->
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success-msg">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </p>

        <?php endif; ?>

        <!-- ERROR MESSAGE -->
        <?php if (!empty($error)): ?>
            <p class="error-msg">
                <?php echo $error; ?>
            </p>
        <?php endif; ?>

        <!-- FORM -->
        <form method="POST">

            <input type="text" name="full_name" placeholder="Full Name" required>

            <input type="email" name="email" placeholder="Email" required>

            <input type="password" name="password" placeholder="Password" required>

            <select name="role" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="company">Company</option>
            </select>

            <button class="btn" type="submit">Register</button>

        </form>
            <span>Already have an account? <a href="login.php">Login</a></span>
    </div>

</div>

</body>
</html>