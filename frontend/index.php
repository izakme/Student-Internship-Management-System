<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Internship Management System</title>

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>if(localStorage.getItem('theme')==='dark')document.documentElement.setAttribute('data-theme','dark');</script>

    <!-- Optional modern font -->
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>

<!-- TOP BAR -->
<div class="topbar">
    <span class="topbar-title">Student Internship Management System</span>
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme"><i class="fas fa-moon"></i></button>
</div>

<!-- HERO SECTION -->
<div class="content">

    <div class="card hero-card" style="text-align:center; padding:60px 30px; position:relative; overflow:hidden;">

        <h1 style="font-size:38px; margin-bottom:15px; color:#0f172a;">
            Find Internships. Apply Easily. Build Your Future.
        </h1>

        <p style="font-size:16px; color:#64748b; max-width:700px; margin:0 auto 25px;">
            A modern platform connecting students, companies, and administrators for seamless internship management,
            application tracking, and opportunity discovery.
        </p>

        <div style="margin-top:20px;">
            <a href="authentication/login.php" class="btn">Login</a>
            <a href="authentication/register.php" class="btn" style="background: var(--secondary);">Register</a>
        </div>

    </div>

    <!-- FEATURES SECTION -->
    <div class="card">

        <h2 class="center" style="margin-bottom:20px;">How It Works</h2>

        <div class="grid">

            <div class="stat-card">
                <div class="stat-title">Step 1</div>
                <div class="stat-number" style="font-size:28px;"><i class="fas fa-graduation-cap"></i></div>
                <p>Students register and build professional profiles.</p>
            </div>

            <div class="stat-card">
                <div class="stat-title">Step 2</div>
                <div class="stat-number" style="font-size:28px;"><i class="fas fa-building"></i></div>
                <p>Companies post internship opportunities easily.</p>
            </div>

            <div class="stat-card">
                <div class="stat-title">Step 3</div>
                <div class="stat-number" style="font-size:28px;"><i class="fas fa-file-alt"></i></div>
                <p>Students apply and get selected online.</p>
            </div>

        </div>

    </div>

    <!-- BENEFITS SECTION -->
    <div class="card">

        <h2 class="center" style="margin-bottom:20px;">Why Use This Platform?</h2>

        <div class="grid">

            <div class="stat-card">
                <div class="stat-title">Fast Applications</div>
                <p>No paperwork. Apply in seconds.</p>
            </div>

            <div class="stat-card">
                <div class="stat-title">Verified Companies</div>
                <p>Only trusted internship providers.</p>
            </div>

            <div class="stat-card">
                <div class="stat-title">Easy Tracking</div>
                <p>Track application status in real-time.</p>
            </div>

        </div>

    </div>

</div>

<!-- FOOTER -->
<footer class="site-footer">
    <hr class="footer-separator">
    <p>&copy; <?php echo date("Y"); ?> Student Internship Management System. All rights reserved.</p>
    <p>Version 1.0</p>
    <p>Developer: Isaack Changawa (zak)</p>
</footer>

<script>
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
