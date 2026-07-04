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

    <!-- Optional modern font -->
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>

<!-- TOP BAR -->
<div class="topbar">
    Student Internship Management System
</div>

<!-- HERO SECTION -->
<div class="content">

    <div class="card" style="text-align:center; padding:60px 30px; position:relative; overflow:hidden;">

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
                <div class="stat-number" style="font-size:28px;">🧑‍🎓</div>
                <p>Students register and build professional profiles.</p>
            </div>

            <div class="stat-card">
                <div class="stat-title">Step 2</div>
                <div class="stat-number" style="font-size:28px;">🏢</div>
                <p>Companies post internship opportunities easily.</p>
            </div>

            <div class="stat-card">
                <div class="stat-title">Step 3</div>
                <div class="stat-number" style="font-size:28px;">📩</div>
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
<div style="text-align:center; padding:20px; color:#94a3b8;">
    © <?php echo date("Y"); ?> Student Internship Management System
</div>

</body>
</html>