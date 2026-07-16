<?php
session_start();

require_once __DIR__ . "/../../backend/helpers/Language.php";
require_once __DIR__ . "/../../backend/config/database.php";
require_once __DIR__ . "/../../backend/classes/user.php";
require_once __DIR__ . "/../../backend/helpers/csrf.php";
require_once __DIR__ . "/../../backend/helpers/App.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$error = "";
$message = "";
$db = (new Database())->connect();
$userObj = new User($db);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = __("Invalid form submission.");
    } else {
        $current = $_POST['current_password'] ?? '';
        $newPw = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($newPw) || empty($confirm)) {
            $error = __("All fields are required.");
        } elseif ($newPw !== $confirm) {
            $error = __("Passwords do not match.");
        } else {
            $pwErrors = App::validatePassword($newPw);
            if (!empty($pwErrors)) {
                $error = __("Password must contain:") . " " . implode(', ', $pwErrors) . ".";
            } else {
                $user = $userObj->getUserById($_SESSION['user_id']);
                if ($user && password_verify($current, $user['password'])) {
                    $userObj->updatePassword($_SESSION['user_id'], $newPw);
                    $message = __("Password updated successfully.");
                } else {
                    $error = __("Current password is incorrect.");
                }
            }
        }
    }
}

$role = $_SESSION['role'] ?? '';
$backLink = match($role) {
    'student' => '../student/profile.php',
    'company' => '../company/profile.php',
    default   => '../admin/dashboard.php',
};

include "../layouts/header.php";
include "../layouts/sidebar.php";
?>

<div class="card" style="max-width:500px;margin:30px auto;">
    <h2 class="center"><?= __('Change Password') ?></h2>

    <?php if ($message): ?>
        <p class="success-msg"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error-msg"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <?= csrfField() ?>

        <div class="form-group">
            <label><?= __('Current Password') ?></label>
            <input type="password" name="current_password" required>
        </div>

        <div class="form-group">
            <label><?= __('New Password') ?></label>
            <input type="password" name="new_password" id="newPw" onkeyup="checkStrength()" required>
            <div style="font-size:12px;color:#888;margin-top:4px;">
                <?= __('Must be at least 8 characters with an uppercase letter and a digit.') ?>
            </div>
            <div id="pwStrength" style="margin-top:4px;"></div>
        </div>

        <div class="form-group">
            <label><?= __('Confirm New Password') ?></label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit" class="btn btn-block"><?= __('Update Password') ?></button>
    </form>

    <p class="center" style="margin-top:15px;">
        <a href="<?= $backLink ?>"><?= __('Back to Profile') ?></a>
    </p>
</div>

<script>
function checkStrength() {
    var pw = document.getElementById('newPw').value;
    var el = document.getElementById('pwStrength');
    var lenOk = pw.length >= 8;
    var upperOk = /[A-Z]/.test(pw);
    var digitOk = /[0-9]/.test(pw);
    var score = (lenOk ? 1 : 0) + (upperOk ? 1 : 0) + (digitOk ? 1 : 0);
    if (pw.length === 0) { el.innerHTML = ''; return; }
    if (score === 3) el.innerHTML = '<span style="color:#28a745;font-weight:600;"><i class="fas fa-shield-alt"></i> Strong</span>';
    else if (score === 2) el.innerHTML = '<span style="color:#e67e22;font-weight:600;"><i class="fas fa-exclamation-triangle"></i> Medium</span>';
    else el.innerHTML = '<span style="color:#e74c3c;font-weight:600;"><i class="fas fa-times-circle"></i> Weak</span>';
}
</script>

<?php include "../layouts/footer.php"; ?>
