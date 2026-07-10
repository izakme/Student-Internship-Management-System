<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$timeout = 3600;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: ../authentication/login.php");
    exit();
}
$_SESSION['last_activity'] = time();
$loggedInName = htmlspecialchars($_SESSION['name'] ?? 'User');
$loggedInRole = $_SESSION['role'] ?? '';

/* Fetch notification data for admin if page didn't already set it */
if ($loggedInRole === 'admin' && !isset($pendingCount)) {
    try {
        require_once __DIR__ . "/../../backend/config/database.php";
        require_once __DIR__ . "/../../backend/classes/Application.php";
        $appObj = new Application();
        $pendingCount = (int)$appObj->countPending();
        $stmt = $appObj->getApplications();
        $allApps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $recentApps = array_slice($allApps, 0, 10);
    } catch (Exception $e) {
        $pendingCount = 0;
        $recentApps = [];
    }
}
if (!isset($pendingCount)) $pendingCount = 0;
if (!isset($recentApps)) $recentApps = [];
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>if (localStorage.getItem('theme')==='dark') document.documentElement.setAttribute('data-theme','dark');</script>
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

        /* Clock */
        function updateClock() {
            var now = new Date();
            var options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
            var el = document.getElementById('topbarClock');
            if (el) el.textContent = now.toLocaleDateString('en-US', options);
        }
        updateClock();
        setInterval(updateClock, 1000);

        /* Theme toggle */
        var themeToggle = document.getElementById('themeToggle');
        var html = document.documentElement;
        if (localStorage.getItem('theme') === 'dark') {
            html.setAttribute('data-theme', 'dark');
            if (themeToggle) themeToggle.textContent = '\u2600';
        }
        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                if (html.getAttribute('data-theme') === 'dark') {
                    html.removeAttribute('data-theme');
                    localStorage.setItem('theme', 'light');
                    themeToggle.textContent = '\u263E';
                } else {
                    html.setAttribute('data-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                    themeToggle.textContent = '\u2600';
                }
            });
        }

        /* Notification panel */
        var notifBtn = document.getElementById('notifBtn');
        var notifPanel = document.getElementById('notifPanel');
        if (notifBtn && notifPanel) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notifPanel.classList.toggle('show');
            });
            document.addEventListener('click', function() {
                notifPanel.classList.remove('show');
            });
            notifPanel.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }

        /* Search navigation */
        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    var q = this.value.trim().toLowerCase();
                    if (!q) return;
                    var pages = {
                        'dashboard': '../admin/dashboard.php',
                        'users': '../admin/users.php',
                        'user': '../admin/users.php',
                        'students': '../admin/students.php',
                        'student': '../admin/students.php',
                        'companies': '../admin/companies.php',
                        'company': '../admin/companies.php',
                        'internships': '../admin/internships.php',
                        'internship': '../admin/internships.php',
                        'applications': '../admin/applications.php',
                        'application': '../admin/applications.php',
                        'reports': '../admin/reports.php',
                        'report': '../admin/reports.php'
                    };
                    for (var key in pages) {
                        if (key.indexOf(q) !== -1 || q.indexOf(key) !== -1) {
                            window.location.href = pages[key];
                            return;
                        }
                    }
                }
            });
        }
    });
    </script>
</head>

<body>

<div class="topbar">
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">&#9776;</button>

    <div class="search-bar">
        <span class="search-icon">&#128269;</span>
        <input type="text" placeholder="Search pages..." id="searchInput">
    </div>

    <span class="topbar-title">Internship Management System</span>

    <div class="topbar-datetime" id="topbarClock"></div>

    <div class="topbar-user">
        <span class="topbar-user-icon">&#128100;</span>
        <span class="topbar-user-name"><?php echo $loggedInName; ?></span>
    </div>

    <?php if ($loggedInRole === 'admin' && isset($pendingCount)): ?>
    <div class="notification-wrapper">
        <button class="notification-btn" id="notifBtn">
            &#128276;
            <?php if ($pendingCount > 0): ?>
            <span class="notification-badge"><?php echo $pendingCount; ?></span>
            <?php endif; ?>
        </button>
        <div class="notification-panel" id="notifPanel">
            <div class="notification-panel-header">
                New Applications (<?php echo $pendingCount; ?>)
            </div>
            <?php if (!empty($recentApps)): ?>
                <?php foreach ($recentApps as $app): ?>
                <div class="notification-item">
                    <span class="notif-icon">&#128100;</span>
                    <div class="notif-text">
                        <strong><?php echo htmlspecialchars($app['full_name']); ?></strong>
                        applied for <em><?php echo htmlspecialchars($app['title']); ?></em>
                        <div class="notif-time"><?php echo date('M j, g:i A', strtotime($app['application_date'])); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="notification-item">
                    <span class="notif-icon">&#9989;</span>
                    <div class="notif-text">No new applications</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">&#9790;</button>
</div>

<div class="layout">
