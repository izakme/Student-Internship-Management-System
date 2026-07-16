<?php
/**
 * Deadline Reminder Script
 * Run via cron: 0 9 * * * php /path/to/scripts/remind_deadlines.php
 */

require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/helpers/Mailer.php';

$db = (new Database())->connect();

// Find internships expiring in 3 days
$stmt = $db->prepare("
    SELECT i.internship_id, i.title, i.deadline, c.company_name, u.email as company_email
    FROM internships i
    JOIN companies c ON i.company_id = c.company_id
    JOIN users u ON c.user_id = u.user_id
    WHERE i.deadline = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
");
$stmt->execute();
$expiring = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mailer = new Mailer();

foreach ($expiring as $internship) {
    $subject = "Reminder: Internship deadline approaching";
    $body = "<h2>Deadline Reminder</h2>
             <p>Your internship <strong>{$internship['title']}</strong> is expiring in 3 days.</p>
             <p>Deadline: {$internship['deadline']}</p>
             <p>Log in to review applications before the deadline passes.</p>";
    $mailer->send($internship['company_email'], $subject, $body);

    // Notify students who applied
    $stuStmt = $db->prepare("
        SELECT DISTINCT u.email, u.username
        FROM applications a
        JOIN students s ON a.student_id = s.student_id
        JOIN users u ON s.user_id = u.user_id
        WHERE a.internship_id = ?
    ");
    $stuStmt->execute([$internship['internship_id']]);
    while ($student = $stuStmt->fetch(PDO::FETCH_ASSOC)) {
        $body = "<h2>Deadline Reminder</h2>
                 <p>Dear {$student['username']},</p>
                 <p>The internship <strong>{$internship['title']}</strong> at {$internship['company_name']} is closing in 3 days.</p>
                 <p>Deadline: {$internship['deadline']}</p>";
        $mailer->send($student['email'], $subject, $body);
    }
}

echo date('Y-m-d H:i:s') . " - Sent " . count($expiring) . " reminders.\n";
