<?php
session_start();
require_once "../../backend/config/database.php";
require_once "../../backend/helpers/csrf.php";
require_once "../../backend/helpers/App.php";

$message = "";
$token = $_GET['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid form submission.";
    } else {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';

        $pwErrors = App::validatePassword($password);
        if (!empty($pwErrors)) {
            $message = "Password must contain: " . implode(', ', $pwErrors) . ".";
        } else {
            $db = (new Database())->connect();
            $stmt = $db->prepare("SELECT pr.user_id FROM password_resets pr WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = 0 LIMIT 1");
            $stmt->execute([$token]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($reset) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->execute([$hash, $reset['user_id']]);

                $stmt = $db->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
                $stmt->execute([$token]);

                $_SESSION['message'] = "Password reset successful. You can now log in.";
                header("Location: login.php");
                exit();
            } else {
                $message = "Invalid or expired reset token.";
            }
        }
    }
}

if ($token) {
    $db = (new Database())->connect();
    $stmt = $db->prepare("SELECT COUNT(*) FROM password_resets WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->execute([$token]);
    $valid = $stmt->fetchColumn() > 0;
    if (!$valid) {
        $message = "This reset link is invalid or has expired.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password | SIMS</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="topbar"><span class="topbar-title">SIMS</span></div>
<div class="content">
<div class="card" style="max-width:450px;margin-top:40px;">
<h2 class="center">Reset Password</h2>
<?php if ($message): ?>
<div style="background:#fdecec;color:#e74c3c;padding:12px;border-radius:10px;margin-bottom:20px;text-align:center;">
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
<?php if ($token && !$message): ?>
<form method="POST">
<?= csrfField() ?>
<input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
<label>New Password</label>
<input type="password" name="password" placeholder="Min 8 chars, 1 uppercase, 1 digit" required>
<div style="font-size:12px;color:#888;margin-top:-8px;margin-bottom:12px;">Must be at least 8 characters with an uppercase letter and a digit.</div>
<button type="submit" class="btn btn-block">Reset Password</button>
</form>
<?php endif; ?>
<p class="center" style="margin-top:15px;"><a href="login.php" style="color:#5bbcff;">Back to Login</a></p>
</div>
</div>
</body>
</html>
