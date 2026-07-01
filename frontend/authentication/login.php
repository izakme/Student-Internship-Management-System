<?php

session_start();

/* Show errors (REMOVE in production later) */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../../backend/config/database.php";
require_once __DIR__ . "/../../backend/classes/user.php";
require_once __DIR__ . "/../../backend/classes/auth.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $db = (new Database())->connect();

    $user = new User($db);
    $auth = new Auth($user);

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $login = $auth->login($email, $password);

    if ($login) {

        // IMPORTANT: ensure session exists before redirect
        if (isset($_SESSION['role'])) {

            if ($_SESSION['role'] === 'student') {
                header("Location: /S-I-M-S/frontend/student/dashboard.php");
                exit();
            }

            if ($_SESSION['role'] === 'company') {
                header("Location: /S-I-M-S/frontend/company/dashboard.php");
                exit();
            }

            if ($_SESSION['role'] === 'admin') {
                header("Location: /S-I-M-S/frontend/admin/dashboard.php");
                exit();
            }
        }

        // fallback if role missing
        $message = "Login successful but role not found in session.";
    } else {
        $message = "Invalid email or password.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="/S-I-M-S/frontend/assets/css/style.css">
</head>

<body>

<div class="auth-wrapper">

    <div class="auth-card">

        <h2>Login</h2>

        <?php if (!empty($message)) { ?>
            <p class="error-msg">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php } ?>

        <form method="POST">

            <input type="email" name="email" placeholder="Email" required>

            <input type="password" name="password" placeholder="Password" required>

            <button class="btn" type="submit">Login</button>

        </form>

        <span>
            Don't have an account?
            <a href="/S-I-M-S/frontend/authentication/register.php">Register</a>
        </span>

    </div>

</div>

</body>
</html>