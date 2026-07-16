<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="sidebar" id="sidebar">

    <button class="sidebar-close" id="sidebarClose">&times;</button>

    <h3><?= __('Menu') ?></h3>

    <?php if (isset($_SESSION['role'])): ?>
        <?php if ($_SESSION['role'] === 'student'): ?>
            <a href="../student/dashboard.php"><?= __('Dashboard') ?></a>
            <a href="../student/internships.php"><?= __('Internships') ?></a>
            <a href="../student/search.php"><?= __('Search') ?></a>
            <a href="../student/applications.php"><?= __('Applications') ?></a>
            <a href="../student/profile.php"><?= __('Profile') ?></a>
        <?php elseif ($_SESSION['role'] === 'company'): ?>
            <a href="../company/dashboard.php"><?= __('Dashboard') ?></a>
            <a href="../company/internships.php"><?= __('Post Internship') ?></a>
            <a href="../company/applications.php"><?= __('Applications') ?></a>
            <a href="../company/profile.php"><?= __('Profile') ?></a>
        <?php elseif ($_SESSION['role'] === 'admin'): ?>
            <a href="../admin/dashboard.php"><?= __('Dashboard') ?></a>
            <a href="../admin/users.php"><?= __('Users') ?></a>
            <a href="../admin/students.php"><?= __('Students') ?></a>
            <a href="../admin/companies.php"><?= __('Companies') ?></a>
            <a href="../admin/internships.php"><?= __('Internships') ?></a>
            <a href="../admin/applications.php"><?= __('Applications') ?></a>
            <a href="../admin/reports.php"><?= __('Reports') ?></a>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['role'])): ?>
        <a href="../authentication/logout.php"><?= __('Logout') ?></a>
    <?php endif; ?>

</div>

<div class="content">