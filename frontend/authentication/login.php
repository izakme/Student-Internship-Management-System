<?php

session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/user.php";
require_once "../../backend/classes/auth.php";

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){

    $db =
        (new database())->connect();

    $user =
        new user($db);

    $auth =
        new auth($user);

    if(
        $auth->login(
            $_POST['email'],
            $_POST['password']
        )
    ){

        if(
            $_SESSION['role']
            == 'student'
        ){
            header(
                "Location: ../student/dashboard.php"
            );
        }

        elseif(
            $_SESSION['role']
            == 'company'
        ){
            header(
                "Location: ../company/dashboard.php"
            );
        }

        else{
            header(
                "Location: ../admin/dashboard.php"
            );
        }

        exit();
    }

    $message =
        "Invalid Credentials";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
        <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">

    <div class="auth-card">

        <h2>Login</h2>

        <p class="error-msg"><?php echo $message; ?></p>

        <form method="POST">

            <input type="email" name="email" placeholder="Email" required>

            <input type="password" name="password" placeholder="Password" required>

            <button class="btn" type="submit">Login</button>

        </form>

    </div>

    </div>
</body>
</html>