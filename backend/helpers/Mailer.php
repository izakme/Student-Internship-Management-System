<?php

class Mailer
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $from;
    private $fromName;

    public function __construct()
    {
        $this->loadEnv();
        $this->host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $this->port = getenv('MAIL_PORT') ?: '587';
        $this->username = getenv('MAIL_USERNAME') ?: '';
        $this->password = getenv('MAIL_PASSWORD') ?: '';
        $this->from = getenv('MAIL_FROM') ?: $this->username;
        $this->fromName = getenv('MAIL_FROM_NAME') ?: 'SIMS';
    }

    private function loadEnv()
    {
        $envFile = __DIR__ . '/../../.env';
        if (!file_exists($envFile)) return;
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, '"\'');
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    public function send($to, $subject, $body)
    {
        if (empty($this->username) || empty($this->password)) {
            error_log("Mailer: SMTP credentials not configured");
            return false;
        }

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: ' . $this->fromName . ' <' . $this->from . '>',
            'Reply-To: ' . $this->from,
            'X-Mailer: PHP/' . phpversion(),
        ];

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; padding: 20px; background: #f4f8fb;">
    <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #0d6efd, #6610f2); padding: 20px; text-align: center;">
            <h1 style="color: white; margin: 0; font-size: 24px;">Student Internship Management System</h1>
        </div>
        <div style="padding: 30px;">
            {$body}
            <hr style="border: none; border-top: 1px solid #e5edf5; margin: 20px 0;">
            <p style="color: #888; font-size: 12px;">This is an automated message from SIMS. Please do not reply directly.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return mail($to, $subject, $htmlBody, implode("\r\n", $headers));
    }

    public function sendApplicationStatus($to, $studentName, $internshipTitle, $status)
    {
        $statusColors = [
            'Accepted' => '#28a745',
            'Rejected' => '#dc3545',
            'Pending'  => '#ffc107',
        ];
        $color = $statusColors[$status] ?? '#6c757d';
        $loginUrl = (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'http://localhost') . '/authentication/login.php';

        $body = <<<BODY
            <h2 style="color: #333;">Application Status Update</h2>
            <p>Dear <strong>{$studentName}</strong>,</p>
            <p>The status of your application for <strong>{$internshipTitle}</strong> has been updated.</p>
            <div style="text-align: center; padding: 20px;">
                <span style="display: inline-block; padding: 10px 30px; border-radius: 20px; background: {$color}; color: white; font-size: 18px; font-weight: bold;">
                    {$status}
                </span>
            </div>
            <p>Log in to your student dashboard to view more details.</p>
            <p style="margin-top: 20px;">
                <a href="{$loginUrl}"
                   style="display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 6px;">
                    Login to Dashboard
                </a>
            </p>
BODY;

        return $this->send($to, "Application Status: {$status}", $body);
    }

    public function sendNewApplication($to, $companyName, $studentName, $internshipTitle)
    {
        $loginUrl = (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'http://localhost') . '/authentication/login.php';

        $body = <<<BODY
            <h2 style="color: #333;">New Application Received</h2>
            <p>Dear <strong>{$companyName}</strong>,</p>
            <p>A new application has been submitted for your internship listing.</p>
            <table style="width: 100%; border-collapse: collapse; margin: 15px 0;">
                <tr><td style="padding: 8px; background: #f4f8fb; font-weight: bold;">Student</td><td style="padding: 8px;">{$studentName}</td></tr>
                <tr><td style="padding: 8px; background: #f4f8fb; font-weight: bold;">Internship</td><td style="padding: 8px;">{$internshipTitle}</td></tr>
            </table>
            <p>Log in to your company dashboard to review this application.</p>
            <p style="margin-top: 20px;">
                <a href="{$loginUrl}"
                   style="display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 6px;">
                    Login to Dashboard
                </a>
            </p>
BODY;

        return $this->send($to, "New Application: {$studentName}", $body);
    }
}
