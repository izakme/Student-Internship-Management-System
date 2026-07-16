<?php
session_start();
require_once __DIR__ . "/../../backend/helpers/Language.php";
require_once __DIR__ . "/../../backend/config/database.php";
require_once __DIR__ . "/../../backend/helpers/csrf.php";
require_once __DIR__ . "/../../backend/helpers/App.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = __("Invalid form submission.");
    } else {
        $email = trim($_POST['email'] ?? '');
        if (!App::isValidEmail($email)) {
            $message = __("Please enter a valid email address.");
        } else {
            $db = (new Database())->connect();
            $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                $stmt = $db->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$user['user_id'], $token, $expires]);

                $resetLink = App::baseUrl() . "/frontend/authentication/reset_password.php?token=" . $token;
                $subject = __("Password Reset") . " - SIMS";
                $body = "<h2>" . __("Password Reset Request") . "</h2>
                         <p>" . __("Click the link below to reset your password. This link expires in 1 hour.") . "</p>
                         <p><a href='{$resetLink}'>" . __("Reset Password") . "</a></p>
                         <p>" . __("If you didn't request this, ignore this email.") . "</p>";

                require_once __DIR__ . "/../../backend/helpers/Mailer.php";
                $mailer = new Mailer();
                $mailer->send($email, $subject, $body);
            }

            $message = __("If that email exists, a reset link has been sent.");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= __('Forgot Password') ?> | SIMS</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="topbar"><span class="topbar-title">SIMS</span></div>
<div class="content">
<div class="card" style="max-width:450px;margin-top:40px;">
<h2 class="center"><?= __('Forgot Password') ?></h2>
<?php if ($message): ?>
<div style="background:#fdecec;color:#e74c3c;padding:12px;border-radius:10px;margin-bottom:20px;text-align:center;">
<?= htmlspecialchars($message) ?>
</div>
<?php endif; ?>
<form method="POST">
<?= csrfField() ?>
<label><?= __('Email Address') ?></label>
<input type="email" name="email" placeholder="<?= __('Enter your email') ?>" required>
<button type="submit" class="btn btn-block"><?= __('Send Reset Link') ?></button>
</form>
<p class="center" style="margin-top:15px;"><a href="login.php" style="color:#5bbcff;"><?= __('Back to Login') ?></a></p>
</div>
</div>
</body>
</html>
