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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.getElementById('menuToggle');
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        var closeBtn = document.getElementById('sidebarClose');
        function openSidebar() {
            sidebar.classList.add('open');
            if (overlay) overlay.classList.add('show');
        }
        function closeSidebar() {
            sidebar.classList.remove('open');
            if (overlay) overlay.classList.remove('show');
        }
        if (toggle) toggle.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
    });
    </script>
</head>

<body>

<div class="topbar">
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">&#9776;</button>
    <span class="topbar-title">Internship Management System</span>
</div>

<div class="layout">
