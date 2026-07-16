<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../helpers/Mailer.php";

class Application
{
    private $conn;
    private $table = "applications";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /* =========================
       APPLY FOR INTERNSHIP
    ========================= */
    public function apply($student_id, $internship_id, $cover_letter = '')
    {
        // Validate inputs
        if (!$student_id || !$internship_id) {
            return false;
        }

        // Check internship exists + still active
        $stmt = $this->conn->prepare("
            SELECT COUNT(*)
            FROM internships
            WHERE internship_id = ?
            AND deadline >= CURDATE()
        ");

        $stmt->execute([$internship_id]);

        if ($stmt->fetchColumn() == 0) {
            return false;
        }

        // Prevent duplicate applications
        if ($this->hasApplied($student_id, $internship_id)) {
            return false;
        }

        // Insert application
        $sql = "
            INSERT INTO {$this->table}
            (student_id, internship_id, cover_letter, status, application_date)
            VALUES (?, ?, ?, 'Pending', NOW())
        ";

        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([$student_id, $internship_id, $cover_letter]);

        if ($result) {
            $this->notifyCompanyNewApplication($internship_id, $student_id);
        }

        return $result;
    }

    private function notifyCompanyNewApplication($internship_id, $student_id)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.email, c.company_name, stu.username as student_name, i.title
                FROM internships i
                JOIN companies c ON i.company_id = c.company_id
                JOIN users u ON c.user_id = u.user_id
                JOIN students s ON s.student_id = ?
                JOIN users stu ON s.user_id = stu.user_id
                WHERE i.internship_id = ?
                LIMIT 1
            ");
            $stmt->execute([$student_id, $internship_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $mailer = new Mailer();
                $mailer->sendNewApplication(
                    $data['email'],
                    $data['company_name'],
                    $data['student_name'],
                    $data['title']
                );
            }
        } catch (Exception $e) {
            error_log("Failed to send new application notification: " . $e->getMessage());
        }
    }

    /* =========================
       CHECK DUPLICATE APPLY
    ========================= */
    public function hasApplied($student_id, $internship_id)
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE student_id = ?
            AND internship_id = ?
        ");

        $stmt->execute([$student_id, $internship_id]);

        return $stmt->fetchColumn() > 0;
    }

    /* =========================
       ADMIN: ALL APPLICATIONS
    ========================= */
    public function getApplications()
    {
        $stmt = $this->conn->prepare("
            SELECT
                a.application_id,
                s.registration_no,
                u.username,
                i.title,
                a.status,
                a.cover_letter,
                a.application_date
            FROM applications a
            INNER JOIN students s ON a.student_id = s.student_id
            INNER JOIN users u ON s.user_id = u.user_id
            INNER JOIN internships i ON a.internship_id = i.internship_id
            ORDER BY a.application_date DESC
        ");

        $stmt->execute();
        return $stmt;
    }

    /* =========================
       STUDENT APPLICATIONS
    ========================= */
    public function getStudentApplications($student_id)
    {
        $stmt = $this->conn->prepare("
            SELECT
                a.application_id,
                i.title,
                a.status,
                a.application_date
            FROM applications a
            INNER JOIN internships i ON a.internship_id = i.internship_id
            WHERE a.student_id = ?
            ORDER BY a.application_date DESC
        ");

        $stmt->execute([$student_id]);
        return $stmt;
    }

    /* =========================
       WITHDRAW APPLICATION
    ========================= */
    public function withdraw($application_id, $student_id)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM {$this->table}
            WHERE application_id = ?
            AND student_id = ?
            AND status = 'Pending'
        ");
        $stmt->execute([$application_id, $student_id]);
        return $stmt->rowCount() > 0;
    }

    /* =========================
       UPDATE STATUS
    ========================= */
    public function updateStatus($application_id, $status)
    {
        if (!$this->isValidStatus($status)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE {$this->table}
            SET status = ?
            WHERE application_id = ?
        ");

        $result = $stmt->execute([$status, $application_id]);

        if ($result) {
            $this->notifyStudentStatusChange($application_id);
        }

        return $result;
    }

    private function notifyStudentStatusChange($application_id)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.email, u.username, a.status, i.title
                FROM applications a
                JOIN students s ON a.student_id = s.student_id
                JOIN users u ON s.user_id = u.user_id
                JOIN internships i ON a.internship_id = i.internship_id
                WHERE a.application_id = ?
                LIMIT 1
            ");
            $stmt->execute([$application_id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                $mailer = new Mailer();
                $mailer->sendApplicationStatus(
                    $data['email'],
                    $data['username'],
                    $data['title'],
                    $data['status']
                );
            }
        } catch (Exception $e) {
            error_log("Failed to send status notification: " . $e->getMessage());
        }
    }

    public function updateCompanyApplicationStatus($application_id, $company_id, $status)
    {
        if (!$this->isValidStatus($status)) {
            return false;
        }

        $stmt = $this->conn->prepare("
            UPDATE applications a
            INNER JOIN internships i ON a.internship_id = i.internship_id
            SET a.status = ?
            WHERE a.application_id = ?
            AND i.company_id = ?
        ");

        $stmt->execute([$status, $application_id, $company_id]);
        $result = $stmt->rowCount() > 0;

        if ($result) {
            $this->notifyStudentStatusChange($application_id);
        }

        return $result;
    }

    /* =========================
       DELETE APPLICATION
    ========================= */
    public function deleteApplication($application_id)
    {
        $stmt = $this->conn->prepare("
            DELETE FROM {$this->table}
            WHERE application_id = ?
        ");

        return $stmt->execute([$application_id]);
    }

    /* =========================
       ADMIN DASHBOARD COUNTS
    ========================= */
    public function countApplications()
    {
        return $this->conn->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    public function countPending()
    {
        return $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE status='Pending'")->fetchColumn();
    }

    public function countAccepted()
    {
        return $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE status='Accepted'")->fetchColumn();
    }

    public function countRejected()
    {
        return $this->conn->query("SELECT COUNT(*) FROM {$this->table} WHERE status='Rejected'")->fetchColumn();
    }

    /* =========================
       STUDENT DASHBOARD COUNTS
    ========================= */
    public function countStudentApplications($student_id)
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE student_id = ?
        ");

        $stmt->execute([$student_id]);
        return $stmt->fetchColumn();
    }

    public function countAcceptedByStudent($student_id)
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE student_id = ?
            AND status = 'Accepted'
        ");

        $stmt->execute([$student_id]);
        return $stmt->fetchColumn();
    }

    /* =========================
       COMPANY APPLICANTS
    ========================= */
    public function getCompanyApplicants($company_id)
    {
        $stmt = $this->conn->prepare("
            SELECT
                a.application_id,
                u.username,
                s.registration_no,
                i.title,
                a.status,
                a.cover_letter,
                a.application_date
            FROM applications a
            INNER JOIN students s ON a.student_id = s.student_id
            INNER JOIN users u ON s.user_id = u.user_id
            INNER JOIN internships i ON a.internship_id = i.internship_id
            WHERE i.company_id = ?
            ORDER BY a.application_date DESC
        ");

        $stmt->execute([$company_id]);
        return $stmt;
    }

    private function isValidStatus($status)
    {
        return in_array($status, ['Pending', 'Accepted', 'Rejected'], true);
    }
}
