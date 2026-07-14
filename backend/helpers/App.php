<?php

class App
{
    public static function baseUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        $baseDir = preg_replace('#/backend/helpers|/frontend/.*#', '', $scriptDir);
        return $protocol . '://' . $host . $baseDir;
    }

    public static function validatePassword($password)
    {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = 'at least 8 characters';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'an uppercase letter';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'a digit';
        }
        return $errors;
    }

    public static function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateFileType($filePath, $allowedMimes = ['application/pdf'])
    {
        if (!file_exists($filePath)) return false;
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        return in_array($mime, $allowedMimes, true);
    }

    public static function rateLimitCheck($key, $maxAttempts = 5, $window = 900)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $storageKey = 'rate_limit_' . $key;
        $now = time();

        if (!isset($_SESSION[$storageKey])) {
            $_SESSION[$storageKey] = ['count' => 1, 'first' => $now];
            return true;
        }

        $data = $_SESSION[$storageKey];
        if ($now - $data['first'] > $window) {
            $_SESSION[$storageKey] = ['count' => 1, 'first' => $now];
            return true;
        }

        $data['count']++;
        $_SESSION[$storageKey] = $data;

        if ($data['count'] > $maxAttempts) {
            $retryAfter = $window - ($now - $data['first']);
            return $retryAfter;
        }

        return true;
    }
}
