<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Internship System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="navbar">
    Student Internship Management System
</div>

<div class="container">

    <div class="card" style="text-align:center;">

        <h1>Welcome to Internship System</h1>

        <p>
            A web-based platform that connects students,
            companies, and administrators for internship management.
        </p>

        <br>

        <a href="authentication/login.php">
            <button class="btn">Login</button>
        </a>

        <a href="authentication/register.php">
            <button class="btn">Register</button>
        </a>

    </div>

    <div class="card">

        <h3>System Features</h3>

        <ul>
            <li>Student Registration & Login</li>
            <li>Internship Posting by Companies</li>
            <li>Online Internship Applications</li>
            <li>Admin Management System</li>
            <li>Search & Reporting</li>
            <li>Secure Data Encryption</li>
        </ul>

    </div>

</div>

</body>
</html>