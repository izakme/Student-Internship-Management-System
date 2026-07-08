<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">

    <button class="sidebar-close" id="sidebarClose">&times;</button>

    <h3>Menu</h3>

    <?php if (isset($_SESSION['role'])): ?>
        <?php if ($_SESSION['role'] === 'student'): ?>
            <a href="../student/dashboard.php">Dashboard</a>
            <a href="../student/internships.php">Internships</a>
            <a href="../student/search.php">Search</a>
            <a href="../student/applications.php">Applications</a>
            <a href="../student/profile.php">Profile</a>
        <?php elseif ($_SESSION['role'] === 'company'): ?>
            <a href="../company/dashboard.php">Dashboard</a>
            <a href="../company/internships.php">Post Internship</a>
            <a href="../company/applications.php">Applications</a>
            <a href="../company/profile.php">Profile</a>
        <?php elseif ($_SESSION['role'] === 'admin'): ?>
            <a href="../admin/dashboard.php">Dashboard</a>
            <a href="../admin/users.php">Users</a>
            <a href="../admin/students.php">Students</a>
            <a href="../admin/companies.php">Companies</a>
            <a href="../admin/internships.php">Internships</a>
            <a href="../admin/applications.php">Applications</a>
            <a href="../admin/reports.php">Reports</a>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['role'])): ?>
        <a href="../authentication/logout.php">Logout</a>
    <?php endif; ?>

</div>

<div class="content">