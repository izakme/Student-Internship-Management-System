<?php

require_once "../../backend/config/database.php";
require_once "../../backend/classes/user.php";

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $db =
        (new Database())->connect();

    $user =
        new User($db);

    $full_name =
        trim($_POST['full_name']);

    $email =
        trim($_POST['email']);

    $password =
        trim($_POST['password']);

    $role =
        $_POST['role'];

    if(
        $user->register(
            $full_name,
            $email,
            $password,
            $role
        )
    ){
        $message =
            "Registration Successful";
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

        <p class="success-msg"><?php echo $message; ?></p>

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

    </div>

</div>

</body>
</html>