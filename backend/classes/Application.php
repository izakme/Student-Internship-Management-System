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

    /* APPLY FOR INTERNSHIP */
    public function apply($student_id, $internship_id)
    {
        // Check if student has already applied
        $check = $this->conn->prepare("
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE student_id = ?
            AND internship_id = ?
        ");

        $check->execute([$student_id, $internship_id]);

        if ($check->fetchColumn() > 0) {
            return false;
        }

        $sql = "INSERT INTO {$this->table}
                (student_id, internship_id)
                VALUES (?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $student_id,
            $internship_id
        ]);
    }

    /* GET ALL APPLICATIONS */

    public function getApplications()
    {
        $sql = "SELECT
                    a.application_id,
                    s.registration_no,
                    u.full_name,
                    i.title,
                    a.status,
                    a.application_date

                FROM applications a

                JOIN students s
                ON a.student_id = s.student_id

                JOIN users u
                ON s.user_id = u.user_id

                JOIN internships i
                ON a.internship_id = i.internship_id

                ORDER BY a.application_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    /* GET STUDENT APPLICATIONS */

    public function getStudentApplications($student_id)
    {
        $sql = "SELECT
                    a.application_id,
                    i.title,
                    a.status,
                    a.application_date

                FROM applications a

                JOIN internships i

                ON a.internship_id=i.internship_id

                WHERE a.student_id=?

                ORDER BY a.application_date DESC";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$student_id]);

        return $stmt;
    }

    /* UPDATE STATUS */

    public function updateStatus($application_id, $status)
    {
        $sql = "UPDATE {$this->table}

                SET status=?

                WHERE application_id=?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $status,
            $application_id
        ]);
    }

    /* DELETE APPLICATION */

    public function deleteApplication($application_id)
    {
        $sql = "DELETE FROM {$this->table}

                WHERE application_id=?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$application_id]);
    }

    /* DASHBOARD COUNTS */

    public function countApplications()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*)
            FROM {$this->table}
        ");

        return $stmt->fetchColumn();
    }

    public function countPending()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE status='Pending'
        ");

        return $stmt->fetchColumn();
    }

    public function countAccepted()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE status='Accepted'
        ");

        return $stmt->fetchColumn();
    }

    public function countRejected()
    {
        $stmt = $this->conn->query("
            SELECT COUNT(*)
            FROM {$this->table}
            WHERE status='Rejected'
        ");

        return $stmt->fetchColumn();
    }

    /* COMPANY APPLICANTS */

    public function getCompanyApplicants($company_id)
    {
        $sql = "SELECT

                    u.full_name,
                    s.registration_no,
                    i.title,
                    a.status

                FROM applications a

                JOIN students s
                ON a.student_id=s.student_id

                JOIN users u
                ON s.user_id=u.user_id

                JOIN internships i
                ON a.internship_id=i.internship_id

                WHERE i.company_id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$company_id]);

        return $stmt;
    }

}