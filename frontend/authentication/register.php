<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/user.php";
require_once "../../backend/helpers/csrf.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid form submission.";
    } else {

        $db = (new Database())->connect();
        $user = new User($db);

        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = trim($_POST['password'] ?? '');
        $role      = $_POST['role'] ?? '';

        if (empty($full_name) || empty($email) || empty($password) || empty($role)) {

            $error = "All fields are required.";

        } elseif (strlen($password) < 6) {

            $error = "Password must be at least 6 characters long.";

        } else {

            try {
                $result = $user->register(
                    $full_name,
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

</head>

<body>

<div class="topbar">
    Student Internship Management System
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
                name="full_name"
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
                placeholder="Create a password"
                required
            >

            <label>Register As</label>

            <select name="role" required>

                <option value="">
                    Select Role
                </option>

                <option value="student">
                    Student
                </option>

                <option value="company">
                    Company
                </option>

            </select>

            <button
                class="btn"
                type="submit"
                style="width:100%;">

                Create Account

            </button>

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
    <p>Contact: +255754553483</p>
    <p>Prepared by Wanginyi Tech (zak)</p>
</footer>

</body>

</html>
