<?php

require_once __DIR__ . "/../config/database.php";

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
    public function apply($student_id, $internship_id)
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
            (student_id, internship_id, status, application_date)
            VALUES (?, ?, 'Pending', NOW())
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$student_id, $internship_id]);
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
                u.full_name,
                i.title,
                a.status,
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

        return $stmt->execute([$status, $application_id]);
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

        return $stmt->rowCount() > 0;
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
                u.full_name,
                s.registration_no,
                i.title,
                a.status,
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
