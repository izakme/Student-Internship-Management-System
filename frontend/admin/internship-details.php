<?php
session_start();

require_once "../../backend/config/database.php";
require_once "../../backend/classes/Report.php";
require_once "../../backend/helpers/Language.php";

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../authentication/login.php");
    exit();
}

$reportObj = new Report();
$student = null;
$search = $_GET['search'] ?? '';

if ($search) {
    $student = $reportObj->getStudentInternshipDetails($search);
}

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<style>
@media print {
    .topbar, .sidebar, .sidebar-overlay, .site-footer, .no-print { display: none !important; }
    .layout, .content { margin: 0 !important; padding: 0 !important; }
    .detail-report { box-shadow: none !important; margin: 0 !important; }
    body { background: white !important; }
}
.detail-report {
    max-width: 800px;
    margin: 30px auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.1);
    padding: 40px 50px;
    font-size: 14px;
    line-height: 1.6;
    color: #333;
}
.detail-report .header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 3px double #0d6efd;
}
.detail-report .logo-area {
    font-size: 40px;
    color: #0d6efd;
    margin-bottom: 10px;
}
.detail-report .header h1 {
    font-size: 20px;
    color: #0d6efd;
    margin: 5px 0;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.detail-report .header h2 {
    font-size: 16px;
    color: #555;
    margin: 5px 0;
    font-weight: 400;
}
.detail-report .header .meta {
    margin-top: 15px;
    font-size: 13px;
    color: #666;
}
.detail-report .header .meta span {
    display: inline-block;
    margin: 0 15px;
}
.detail-report .section {
    margin: 25px 0;
}
.detail-report .section-title {
    font-size: 15px;
    font-weight: 700;
    color: #0d6efd;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    padding-bottom: 5px;
    border-bottom: 2px solid #0d6efd;
}
.detail-report .divider {
    border: none;
    border-top: 1px dashed #ccc;
    margin: 20px 0;
}
.detail-report table.info {
    width: 100%;
    border-collapse: collapse;
}
.detail-report table.info td {
    padding: 6px 10px;
    vertical-align: top;
}
.detail-report table.info td.label {
    width: 180px;
    font-weight: 600;
    color: #555;
}
.detail-report table.info td.value {
    color: #333;
}
.detail-report .remarks-box {
    background: #f8f9fa;
    border-left: 4px solid #0d6efd;
    padding: 15px 20px;
    border-radius: 4px;
    color: #555;
    min-height: 60px;
}
.detail-report .signature-area {
    margin-top: 50px;
    text-align: right;
}
.detail-report .signature-area .line {
    width: 250px;
    border-top: 1px solid #333;
    margin: 5px 0;
    display: inline-block;
}
.detail-report .signature-area .title {
    font-size: 13px;
    color: #666;
}
</style>

<div class="detail-report">
    <div class="header">
        <div class="logo-area"><i class="fas fa-university"></i></div>
        <h1><?= __('Student Internship Management System') ?></h1>
        <h2><?= __('Internship Details') ?></h2>
        <div class="meta">
            <span><?= __('Generated On') ?>: <?= date('j F Y') ?></span>
        </div>
    </div>

    <?php if (!$search): ?>
        <div style="text-align:center;padding:40px 0;">
            <form method="GET" style="max-width:500px;margin:0 auto;">
                <h3 style="margin-bottom:15px;"><?= __('Search Student') ?></h3>
                <div style="display:flex;gap:10px;">
                    <input type="text" name="search" placeholder="<?= __('Search by student name or registration number...') ?>" style="flex:1;padding:10px;border:1px solid #ddd;border-radius:6px;" required>
                    <button type="submit" class="btn"><?= __('Search') ?></button>
                </div>
                <p style="margin-top:15px;color:#888;font-size:13px;"><?= __('Select a student to view their internship details.') ?></p>
            </form>
        </div>
    <?php elseif (!$student): ?>
        <div style="text-align:center;padding:40px 0;">
            <p style="color:#e74c3c;font-size:16px;"><?= __('No student found.') ?></p>
            <a href="internship-details.php" class="btn no-print" style="margin-top:15px;display:inline-block;"><?= __('Back to Reports') ?></a>
        </div>
    <?php else: ?>

        <div class="no-print" style="text-align:right;margin-bottom:20px;">
            <button onclick="window.print()" class="btn btn-sm" style="display:inline-flex;align-items:center;gap:6px;">
                <i class="fas fa-print"></i> <?= __('Print') ?>
            </button>
            <a href="reports.php" class="btn btn-sm btn-secondary" style="display:inline-flex;align-items:center;gap:6px;margin-left:8px;">
                <i class="fas fa-arrow-left"></i> <?= __('Back to Reports') ?>
            </a>
        </div>

        <!-- Student Information -->
        <div class="section">
            <div class="section-title"><?= __('Student Information') ?></div>
            <hr class="divider">
            <table class="info">
                <tr>
                    <td class="label"><?= __('Student ID') ?>:</td>
                    <td class="value"><?= htmlspecialchars($student['registration_no'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Full Name') ?>:</td>
                    <td class="value"><?= htmlspecialchars($student['full_name'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Programme') ?>:</td>
                    <td class="value"><?= htmlspecialchars($student['course'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Year') ?>:</td>
                    <td class="value"><?= htmlspecialchars($student['year_of_study'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Phone') ?>:</td>
                    <td class="value"><?= htmlspecialchars($student['student_phone'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Email') ?>:</td>
                    <td class="value"><?= htmlspecialchars($student['student_email'] ?? '—') ?></td>
                </tr>
            </table>
        </div>

        <?php if ($student['application_status'] === 'Accepted'): ?>

        <hr class="divider">

        <!-- Internship Details -->
        <div class="section">
            <div class="section-title"><?= __('Internship Details') ?></div>
            <hr class="divider">
            <table class="info">
                <tr>
                    <td class="label"><?= __('Company Name') ?>:</td>
                    <td class="value"><?= htmlspecialchars($student['company_name'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Internship Title') ?>:</td>
                    <td class="value"><?= htmlspecialchars($student['internship_title'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Location') ?>:</td>
                    <td class="value"><?= htmlspecialchars($student['company_location'] ?? '—') ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Applied Date') ?>:</td>
                    <td class="value"><?= $student['application_date'] ? date('d/m/Y', strtotime($student['application_date'])) : '—' ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Deadline') ?>:</td>
                    <td class="value"><?= $student['internship_deadline'] ? date('d/m/Y', strtotime($student['internship_deadline'])) : '—' ?></td>
                </tr>
                <tr>
                    <td class="label"><?= __('Status') ?>:</td>
                    <td class="value"><span style="color:#28a745;font-weight:600;"><?= __('Accepted') ?></span></td>
                </tr>
            </table>
        </div>

        <!-- Remarks -->
        <div class="section">
            <div class="section-title"><?= __('Remarks') ?></div>
            <hr class="divider">
            <div class="remarks-box">
                <?= __('Student has successfully secured an internship placement and is required to submit weekly progress reports.') ?>
            </div>
        </div>

        <?php else: ?>
        <div style="text-align:center;padding:30px 0;color:#e67e22;">
            <p><strong><?= __('No accepted internship found for this student.') ?></strong></p>
        </div>
        <?php endif; ?>

        <hr class="divider">

        <!-- Signature -->
        <div class="signature-area">
            <p style="margin-bottom:5px;"><?= __('Authorized Signature') ?></p>
            <div class="line"></div>
            <p class="title"><?= __('Internship Coordinator') ?></p>
        </div>

    <?php endif; ?>
</div>

<?php include "../layouts/footer.php"; ?>
