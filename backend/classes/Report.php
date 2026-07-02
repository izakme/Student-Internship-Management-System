<?php

require_once __DIR__ . "/../config/database.php";

class Report
{
    private $conn;
    private $table = "reports";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /* =========================
       CREATE REPORT
    ========================= */
    public function createReport($report_name, $report_type, $generated_by, $report_data = null)
    {
        $sql = "INSERT INTO {$this->table}
                (report_name, report_type, generated_by, report_data)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $report_name,
            $report_type,
            $generated_by,
            $report_data
        ]);
    }

    /* =========================
       GET ALL REPORTS
    ========================= */
    public function getAllReports()
    {
        $sql = "SELECT r.*, u.full_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.generated_by = u.user_id
                ORDER BY r.generated_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    /* =========================
       GET REPORT BY ID
    ========================= */
    public function getReport($report_id)
    {
        $sql = "SELECT r.*, u.full_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.generated_by = u.user_id
                WHERE r.report_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$report_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       SEARCH REPORTS
    ========================= */
    public function searchReports($keyword)
    {
        $sql = "SELECT r.*, u.full_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.generated_by = u.user_id
                WHERE r.report_name LIKE ?
                OR r.report_type LIKE ?
                ORDER BY r.generated_date DESC";

        $search = "%" . $keyword . "%";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$search, $search]);

        return $stmt;
    }

    /* =========================
       GENERATE APPLICATIONS REPORT
    ========================= */
    public function generateApplicationsReport($generated_by)
    {
        $sql = "SELECT 
                    a.application_id,
                    u.full_name as student_name,
                    s.registration_no,
                    i.title as internship_title,
                    c.company_name,
                    a.status,
                    a.application_date
                FROM applications a
                INNER JOIN students s ON a.student_id = s.student_id
                INNER JOIN users u ON s.user_id = u.user_id
                INNER JOIN internships i ON a.internship_id = i.internship_id
                INNER JOIN companies c ON i.company_id = c.company_id
                ORDER BY a.application_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $report_name = "Applications Report - " . date('Y-m-d H:i:s');
        $report_data = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        $this->createReport($report_name, 'Applications', $generated_by, $report_data);

        return $stmt;
    }

    /* =========================
       GENERATE INTERNSHIPS REPORT
    ========================= */
    public function generateInternshipsReport($generated_by)
    {
        $sql = "SELECT 
                    i.internship_id,
                    i.title,
                    c.company_name,
                    i.description,
                    i.deadline,
                    COUNT(a.application_id) as applicant_count
                FROM internships i
                INNER JOIN companies c ON i.company_id = c.company_id
                LEFT JOIN applications a ON i.internship_id = a.internship_id
                GROUP BY i.internship_id
                ORDER BY i.deadline DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $report_name = "Internships Report - " . date('Y-m-d H:i:s');
        $report_data = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        $this->createReport($report_name, 'Internships', $generated_by, $report_data);

        return $stmt;
    }

    /* =========================
       GENERATE STUDENTS REPORT
    ========================= */
    public function generateStudentsReport($generated_by)
    {
        $sql = "SELECT 
                    u.full_name,
                    s.registration_no,
                    s.course,
                    s.year_of_study,
                    u.email,
                    COUNT(a.application_id) as applications
                FROM students s
                INNER JOIN users u ON s.user_id = u.user_id
                LEFT JOIN applications a ON s.student_id = a.student_id
                GROUP BY s.student_id
                ORDER BY u.full_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $report_name = "Students Report - " . date('Y-m-d H:i:s');
        $report_data = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        $this->createReport($report_name, 'Students', $generated_by, $report_data);

        return $stmt;
    }

    /* =========================
       DELETE REPORT
    ========================= */
    public function deleteReport($report_id)
    {
        $sql = "DELETE FROM {$this->table}
                WHERE report_id = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$report_id]);
    }

    /* =========================
       COUNT REPORTS
    ========================= */
    public function countReports()
    {
        return $this->conn->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }
}
