<?php
session_start();

if (isset($_GET['lang'])) {
    require_once __DIR__ . "/../../backend/helpers/Language.php";
    setLanguage($_GET['lang']);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

require_once __DIR__ . "/../../backend/helpers/Language.php";
require_once "../../backend/config/database.php";
require_once "../../backend/classes/user.php";
require_once "../../backend/helpers/csrf.php";
require_once "../../backend/helpers/App.php";

$error = "";
$presetRole = isset($_GET['role']) && in_array($_GET['role'], ['student', 'company']) ? $_GET['role'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid form submission.";
    } else {

        $db = (new Database())->connect();
        $user = new User($db);

        $username         = trim($_POST['username'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $password         = trim($_POST['password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');
        $role             = $_POST['role'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {

            $error = "All fields are required.";

        } elseif ($password !== $confirm_password) {

            $error = "Passwords do not match.";

        } elseif (!App::isValidEmail($email)) {

            $error = "Please enter a valid email address.";

        } else {

            $pwErrors = App::validatePassword($password);
            if (!empty($pwErrors)) {
                $error = "Password must contain: " . implode(', ', $pwErrors) . ".";
            } else {

            try {
                $result = $user->register(
                    $username,
                    $email,
                    $password,
                    $role
                );

                if ($result) {

                    $_SESSION['success'] =
                        "Registration successful. You can now log in.";

                    header("Location: login.php");
                    exit();
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Create Account</title>

<link rel="stylesheet"
      href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script>if(localStorage.getItem('theme')==='dark')document.documentElement.setAttribute('data-theme','dark');</script>

<style>
.topbar .topbar-title {
    font-size: 32px;
    letter-spacing: 2px;
}
@media (max-width: 768px) {
    .topbar .topbar-title {
        font-size: 26px;
    }
}
@media (max-width: 480px) {
    .topbar .topbar-title {
        font-size: 22px;
    }
}
</style>

</head>

<body>

<div class="topbar">
    <span class="topbar-title">SIMS</span>
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme"><i class="fas fa-moon"></i></button>
    <a href="?lang=en" style="color:white;text-decoration:none;margin:0 5px;font-size:13px;">EN</a>
    <a href="?lang=sw" style="color:white;text-decoration:none;margin:0 5px;font-size:13px;">SW</a>
</div>

<div class="content">

    <div class="card"
         style="max-width:500px; margin-top:40px;">

        <h2 class="center">
            Create Account
        </h2>

        <p class="center"
           style="margin-bottom:25px;">

            Register to start using the platform.

        </p>

        <!-- Success -->

        <?php if(isset($_SESSION['success'])){ ?>

            <div
                style="
                    background:#d4edda;
                    color:#155724;
                    padding:12px;
                    border-radius:10px;
                    margin-bottom:20px;
                    text-align:center;
                ">

                <?php

                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);

                ?>

            </div>

        <?php } ?>

        <!-- Error -->

        <?php if(!empty($error)){ ?>

            <div
                style="
                    background:#fdecec;
                    color:#e74c3c;
                    padding:12px;
                    border-radius:10px;
                    margin-bottom:20px;
                    text-align:center;
                ">

                <?php echo htmlspecialchars($error); ?>

            </div>

        <?php } ?>

        <form method="POST">

            <?= csrfField() ?>

            <label>Full Name</label>

            <input
                type="text"
                name="username"
                placeholder="Enter your full name"
                required
            >

            <label>Email Address</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                id="regPassword"
                placeholder="Create a password"
                onkeyup="checkPasswordStrength()"
                required
            >
            <div style="font-size:12px;color:#888;margin-top:-8px;margin-bottom:12px;">
                Must be at least 8 characters with an uppercase letter and a digit.
            </div>
            <div id="passwordStrength" style="margin-top:5px;margin-bottom:12px;"></div>

            <label>Confirm Password</label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Re-enter your password"
                required
            >

            <label>Register As</label>

            <select name="role" required>
                <option value="">Select Role</option>
                <option value="student" <?= $presetRole === 'student' ? 'selected' : '' ?>>Student</option>
                <option value="company" <?= $presetRole === 'company' ? 'selected' : '' ?>>Company</option>
            </select>
            <?php if ($presetRole): ?>
                <div style="font-size:12px;color:#888;margin-top:-8px;margin-bottom:12px;">
                    Registering as <strong><?= htmlspecialchars(ucfirst($presetRole)) ?></strong>
                </div>
            <?php endif; ?>

            <button class="btn btn-block" type="submit">Create Account</button>

        </form>

        <hr
            style="
                margin:25px 0;
                border:1px solid #e5edf5;
            ">

        <p class="center">

            Already have an account?

            <br><br>

            <a
                href="login.php"
                style="
                    color:#5bbcff;
                    font-weight:600;
                ">

                Login Here

            </a>

        </p>

    </div>

</div>

<footer class="site-footer">
    <hr class="footer-separator">
    <p>&copy; <?php echo date("Y"); ?> Student Internship Management System. All rights reserved.</p>
    <p>Version 1.0</p>
    <p>Developer: Isaack Changawa (zak)</p>
</footer>

<script>
function checkPasswordStrength() {
    var pw = document.getElementById('regPassword').value;
    var el = document.getElementById('passwordStrength');
    var lenOk = pw.length >= 8;
    var upperOk = /[A-Z]/.test(pw);
    var digitOk = /[0-9]/.test(pw);
    var score = (lenOk ? 1 : 0) + (upperOk ? 1 : 0) + (digitOk ? 1 : 0);

    if (pw.length === 0) {
        el.innerHTML = '';
        return;
    }

    if (score === 3) {
        el.innerHTML = '<span style="color:#28a745;font-weight:600;"><i class="fas fa-shield-alt"></i> Strong</span>';
    } else if (score === 2) {
        el.innerHTML = '<span style="color:#e67e22;font-weight:600;"><i class="fas fa-exclamation-triangle"></i> Medium</span>';
    } else {
        el.innerHTML = '<span style="color:#e74c3c;font-weight:600;"><i class="fas fa-times-circle"></i> Weak</span>';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var themeToggle = document.getElementById('themeToggle');
    var html = document.documentElement;
    if (localStorage.getItem('theme') === 'dark') {
        html.setAttribute('data-theme', 'dark');
        if (themeToggle) themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    }
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            if (html.getAttribute('data-theme') === 'dark') {
                html.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            } else {
                html.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
        });
    }
});
</script>

</body>

</html>
