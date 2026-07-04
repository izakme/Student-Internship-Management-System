<?php
session_start();
?>

<!DOCTYPE html>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Internship Management System</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="topbar">
    Student Internship Management System
</div>

<div class="content">

<div class="card">

    <h1 class="center">Welcome to Student Internship Management System</h1>

    <p style="margin:15px 0;text-align:center;">
        A web-based platform that connects students,
        companies, and administrators for efficient internship management.
    </p>

    <div style="text-align:center;margin-top:20px;">
        <a href="authentication/login.php" class="btn">
            Login
        </a>

        <a href="authentication/register.php" class="btn" style="margin-left:10px;">
            Register
        </a>
    </div>

</div>

<div class="card">

    <h2 class="center">Platform Overview</h2>

    <div class="grid">

        <div class="stat-card">
            <div class="stat-title">Step 1</div>
            <p>Students register and create profiles.</p>
        </div>

        <div class="stat-card">
            <div class="stat-title">Step 2</div>
            <p>Companies post internship opportunities.</p>
        </div>

        <div class="stat-card">
            <div class="stat-title">Step 3</div>
            <p>Students apply and get selected online.</p>
        </div>

    </div>

</div>

</div>

</body>
</html>
