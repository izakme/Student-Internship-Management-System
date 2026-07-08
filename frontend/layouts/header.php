<?php
session_start();
$timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: ../authentication/login.php");
    exit();
}
$_SESSION['last_activity'] = time();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="topbar">
    Internship Management System
</div>

<div class="layout">
