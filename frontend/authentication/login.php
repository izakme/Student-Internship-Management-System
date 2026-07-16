<?php

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

if (isset($_GET['lang'])) {
    require_once __DIR__ . "/../../backend/helpers/Language.php";
    setLanguage($_GET['lang']);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}

require_once __DIR__ . "/../../backend/helpers/Language.php";
require_once __DIR__ . "/../../backend/config/database.php";
require_once __DIR__ . "/../../backend/classes/user.php";
require_once __DIR__ . "/../../backend/classes/auth.php";
require_once __DIR__ . "/../../backend/helpers/csrf.php";
require_once __DIR__ . "/../../backend/helpers/App.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = __("Invalid form submission.");
    } else {

    $rateCheck = App::rateLimitCheck('login_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 900);
    if ($rateCheck !== true) {
        $message = __("Too many attempts. Try again in") . " " . ceil($rateCheck / 60) . " " . __("minutes.");
    } else {

    $db = (new Database())->connect();

    $user = new User($db);
    $auth = new Auth($user);

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $login = $auth->login($email, $password);

    if ($login) {

        if (isset($_SESSION['role'])) {

            switch ($_SESSION['role']) {

                case 'student':
                    header("Location: ../student/dashboard.php");
                    exit();

                case 'company':
                    header("Location: ../company/dashboard.php");
                    exit();

                case 'admin':
                    header("Location: ../admin/dashboard.php");
                    exit();
            }
        }

        $message = __("Login successful but no role found.");
    } else {
        $message = __("Invalid email or password.");
    }
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title><?= __('Login') ?> | <?= __('Student Internship Management System') ?></title>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script>if(localStorage.getItem('theme')==='dark')document.documentElement.setAttribute('data-theme','dark');</script>

<style>
.topbar .topbar-title {
    font-size: 32px;
    letter-spacing: 2px;
}
@media (max-width: 768px) {
    .topbar .topbar-title {
        font-size: 26px;
    }
}
@media (max-width: 480px) {
    .topbar .topbar-title {
        font-size: 22px;
    }
}
.password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}
.password-wrapper input {
    padding-right: 40px;
}
.toggle-password {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 18px;
    color: var(--text-light);
    padding: 4px;
    line-height: 1;
}
.toggle-password:hover {
    color: var(--text);
}
.field-error {
    color: #e74c3c;
    font-size: 13px;
    margin-top: -8px;
    margin-bottom: 12px;
    display: none;
}
.field-error.show {
    display: block;
}
input.error {
    border-color: #e74c3c;
}
@media (max-width: 768px) {
    .card[style*="margin-top:40px"] {
        margin-top: 20px !important;
    }
}
</style>

</head>

<body>

<div class="topbar">
    <span class="topbar-title">SIMS</span>
    <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme"><i class="fas fa-moon"></i></button>
    <a href="?lang=en" style="color:white;text-decoration:none;margin:0 5px;font-size:13px;">EN</a>
    <a href="?lang=sw" style="color:white;text-decoration:none;margin:0 5px;font-size:13px;">SW</a>
</div>

<div class="content">

    <div class="card" style="max-width:450px; margin-top:40px;">

        <h2 class="center"><?= __('Welcome Back') ?></h2>

        <p class="center" style="margin-bottom:25px;">
            <?= __('Login to continue to your account.') ?>
        </p>

        <?php if (!empty($message)) { ?>

            <div
                style="
                    background:#fdecec;
                    color:#e74c3c;
                    padding:12px;
                    border-radius:10px;
                    margin-bottom:20px;
                    text-align:center;
                ">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>

        <form method="POST">

            <?= csrfField() ?>

            <label><?= __('Email Address') ?></label>

            <input
                type="email"
                name="email"
                id="email"
                placeholder="<?= __('Enter your email') ?>"
                required
            >
            <div class="field-error" id="emailError"><?= __('Please enter a valid email address.') ?></div>

            <label><?= __('Password') ?></label>

            <div class="password-wrapper">
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="<?= __('Enter your password') ?>"
                    required
                >
                <button type="button" class="toggle-password" id="togglePassword" tabindex="-1"><i class="fas fa-eye"></i></button>
            </div>
            <div class="field-error" id="passwordError"><?= __('Password is required.') ?></div>
            <div style="font-size:12px;color:#888;margin-top:-8px;margin-bottom:12px;">
                <?= __('Must be at least 8 characters with an uppercase letter and a digit.') ?>
            </div>

            <button class="btn btn-block" type="submit" id="loginBtn"><?= __('Login') ?></button>

            <p class="center" style="margin-top:12px;">
                <a href="forgot_password.php" style="color:#5bbcff;font-size:14px;"><?= __('Forgot Password?') ?></a>
            </p>

        </form>

        <hr style="margin:25px 0; border:1px solid #e5edf5;">

        <p class="center">

            <?= __("Don't have an account?") ?>

            <br><br>

            <a
                href="register.php"
                style="color:#5bbcff;font-weight:600;">

                <?= __('Register Here') ?>

            </a>

        </p>

    </div>

</div>

<footer class="site-footer">
    <hr class="footer-separator">
    <p>&copy; <?php echo date("Y"); ?> <?= __('Student Internship Management System. All rights reserved.') ?></p>
    <p>Version 1.0</p>
    <p>Developer: Isaack Changawa (zak)</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.querySelector('form');
    var emailInput = document.getElementById('email');
    var passwordInput = document.getElementById('password');
    var emailError = document.getElementById('emailError');
    var passwordError = document.getElementById('passwordError');
    var toggleBtn = document.getElementById('togglePassword');

    form.addEventListener('submit', function(e) {
        var valid = true;
        var email = emailInput.value.trim();
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailRegex.test(email)) {
            emailInput.classList.add('error');
            emailError.classList.add('show');
            valid = false;
        } else {
            emailInput.classList.remove('error');
            emailError.classList.remove('show');
        }

        var pw = passwordInput.value.trim();
        if (pw === '') {
            passwordError.textContent = '<?= __('Password is required.') ?>';
            passwordInput.classList.add('error');
            passwordError.classList.add('show');
            valid = false;
        } else if (pw.length < 8 || !/[A-Z]/.test(pw) || !/[0-9]/.test(pw)) {
            passwordError.textContent = '<?= __('Must be at least 8 characters with an uppercase letter and a digit.') ?>';
            passwordInput.classList.add('error');
            passwordError.classList.add('show');
            valid = false;
        } else {
            passwordInput.classList.remove('error');
            passwordError.classList.remove('show');
        }

        if (!valid) e.preventDefault();
    });

    emailInput.addEventListener('blur', function() {
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (this.value.trim() && !emailRegex.test(this.value.trim())) {
            this.classList.add('error');
            emailError.classList.add('show');
        }
    });

    emailInput.addEventListener('input', function() {
        this.classList.remove('error');
        emailError.classList.remove('show');
    });

    passwordInput.addEventListener('blur', function() {
        var val = this.value.trim();
        if (val === '') {
            passwordError.textContent = '<?= __('Password is required.') ?>';
            this.classList.add('error');
            passwordError.classList.add('show');
        } else if (val.length < 8 || !/[A-Z]/.test(val) || !/[0-9]/.test(val)) {
            passwordError.textContent = '<?= __('Must be at least 8 characters with an uppercase letter and a digit.') ?>';
            this.classList.add('error');
            passwordError.classList.add('show');
        }
    });

    passwordInput.addEventListener('input', function() {
        this.classList.remove('error');
        passwordError.classList.remove('show');
    });

    toggleBtn.addEventListener('click', function() {
        var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
    });

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
