<?php

session_start();

require_once __DIR__ . "/../../backend/config/database.php";
require_once __DIR__ . "/../../backend/classes/user.php";
require_once __DIR__ . "/../../backend/classes/auth.php";
require_once __DIR__ . "/../../backend/helpers/csrf.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid form submission.";
    } else {
    $db = (new Database())->connect();

    $user = new User($db);
    $auth = new Auth($user);

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $login = $auth->login($email, $password);

    if ($login) {

        if (isset($_SESSION['role'])) {

            switch ($_SESSION['role']) {

                case 'student':
                    header("Location: ../student/dashboard.php");
                    exit();

                case 'company':
                    header("Location: ../company/dashboard.php");
                    exit();

                case 'admin':
                    header("Location: ../admin/dashboard.php");
                    exit();
            }
        }

        $message = "Login successful but no role found.";
    } else {
        $message = "Invalid email or password.";
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login | Student Internship Management System</title>

<link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="topbar">
    Student Internship Management System
</div>

<div class="content">

    <div class="card" style="max-width:450px; margin-top:40px;">

        <h2 class="center">Welcome Back</h2>

        <p class="center" style="margin-bottom:25px;">
            Login to continue to your account.
        </p>

        <?php if (!empty($message)) { ?>

            <div
                style="
                    background:#fdecec;
                    color:#e74c3c;
                    padding:12px;
                    border-radius:10px;
                    margin-bottom:20px;
                    text-align:center;
                ">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>

        <form method="POST">

            <?= csrfField() ?>

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
                placeholder="Enter your password"
                required
            >

            <button
                class="btn"
                type="submit"
                style="width:100%;">
                Login
            </button>

        </form>

        <hr style="margin:25px 0; border:1px solid #e5edf5;">

        <p class="center">

            Don't have an account?

            <br><br>

            <a
                href="register.php"
                style="color:#5bbcff;font-weight:600;">

                Register Here

            </a>

        </p>

    </div>

</div>

<footer class="site-footer">
    <hr class="footer-separator">
    <p>&copy; <?php echo date("Y"); ?> Student Internship Management System. All rights reserved.</p>
    <p>Contact: +255754553483</p>
    <p>Prepared by Wanginyi Tech (zak)</p>
</footer>

</body>

</html>
