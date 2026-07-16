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
        try {
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
        } catch (PDOException $e) {
            error_log("Report creation error: " . $e->getMessage());
            throw new Exception("Failed to create report: " . $e->getMessage());
        }
    }

    /* =========================
       GET ALL REPORTS
    ========================= */
    public function getAllReports()
    {
        $sql = "SELECT r.*, u.username
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
        $sql = "SELECT r.*, u.username
                FROM {$this->table} r
                LEFT JOIN users u ON r.generated_by = u.user_id
                WHERE r.report_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$report_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       DOWNLOAD REPORT DATA
    ========================= */
    public function outputReportCsv($report_id)
    {
        $report = $this->getReport($report_id);

        if (!$report) {
            return false;
        }

        $rows = json_decode($report['report_data'] ?? '[]', true);

        if (!is_array($rows)) {
            $rows = [];
        }

        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '_', $report['report_name']);
        $filename = trim($filename, '_') ?: 'report';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        if (!empty($rows)) {
            $headerMap = [
                'username'        => 'FULL NAME',
                'year_of_study'   => 'YOS',
                'registration_no' => 'REG NO',
                'application_id'  => 'ID',
                'student_name'    => 'STUDENT NAME',
                'internship_title'=> 'INTERNSHIP TITLE',
                'company_name'    => 'COMPANY',
                'applicant_count' => 'APPLICANTS',
                'application_date'=> 'APPLIED DATE',
                'description'     => 'DESCRIPTION',
                'title'           => 'TITLE',
                'course'          => 'COURSE',
                'email'           => 'EMAIL',
                'status'          => 'STATUS',
                'deadline'        => 'DEADLINE',
                'internship_id'   => 'ID',
                'applications'    => 'APPLICATIONS',
            ];

            $keys = array_keys($rows[0]);
            $displayKeys = array_map(function($k) use ($headerMap) {
                return $headerMap[$k] ?? strtoupper(str_replace('_', ' ', $k));
            }, $keys);

            fputcsv($output, $displayKeys);

            foreach ($rows as $row) {
                fputcsv($output, $row);
            }
        } else {
            fputcsv($output, ['No data available']);
        }

        fclose($output);
        return true;
    }

    /* =========================
       SEARCH REPORTS
    ========================= */
    public function searchReports($keyword)
    {
        $sql = "SELECT r.*, u.username
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
        try {
            $sql = "SELECT 
                        a.application_id,
                        u.username as student_name,
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
        } catch (Exception $e) {
            error_log("Applications report generation error: " . $e->getMessage());
            throw new Exception("Failed to generate applications report: " . $e->getMessage());
        }
    }

    /* =========================
       GENERATE INTERNSHIPS REPORT
    ========================= */
    public function generateInternshipsReport($generated_by)
    {
        try {
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
        } catch (Exception $e) {
            error_log("Internships report generation error: " . $e->getMessage());
            throw new Exception("Failed to generate internships report: " . $e->getMessage());
        }
    }

    /* =========================
       GENERATE STUDENTS REPORT
    ========================= */
    public function generateStudentsReport($generated_by)
    {
        try {
            $sql = "SELECT 
                        u.username,
                        s.registration_no,
                        s.course,
                        s.year_of_study,
                        u.email,
                        COUNT(a.application_id) as applications
                    FROM students s
                    INNER JOIN users u ON s.user_id = u.user_id
                    LEFT JOIN applications a ON s.student_id = a.student_id
                    GROUP BY s.student_id
                    ORDER BY u.username ASC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $report_name = "Students Report - " . date('Y-m-d H:i:s');
            $report_data = json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

            $this->createReport($report_name, 'Students', $generated_by, $report_data);

            return $stmt;
        } catch (Exception $e) {
            error_log("Students report generation error: " . $e->getMessage());
            throw new Exception("Failed to generate students report: " . $e->getMessage());
        }
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

    /* =========================
       GET STUDENT INTERNSHIP DETAILS
    ========================= */
    public function getStudentInternshipDetails($search)
    {
        $sql = "SELECT
                    s.student_id,
                    s.registration_no,
                    s.course,
                    s.year_of_study,
                    s.phone as student_phone,
                    u.username as full_name,
                    u.email as student_email,
                    a.status as application_status,
                    a.application_date,
                    i.title as internship_title,
                    i.description as internship_description,
                    i.deadline as internship_deadline,
                    c.company_name,
                    c.location as company_location,
                    c.phone as company_phone
                FROM students s
                INNER JOIN users u ON s.user_id = u.user_id
                LEFT JOIN applications a ON s.student_id = a.student_id AND a.status = 'Accepted'
                LEFT JOIN internships i ON a.internship_id = i.internship_id
                LEFT JOIN companies c ON i.company_id = c.company_id
                WHERE (u.username LIKE ? OR s.registration_no LIKE ?)
                LIMIT 1";

        $searchTerm = "%{$search}%";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       SEARCH STUDENTS FOR INTERNSHIP DETAILS
    ========================= */
    public function searchStudentsForDetails($search)
    {
        $sql = "SELECT
                    s.student_id,
                    s.registration_no,
                    s.course,
                    s.year_of_study,
                    u.username as full_name,
                    u.email
                FROM students s
                INNER JOIN users u ON s.user_id = u.user_id
                WHERE u.username LIKE ? OR s.registration_no LIKE ?
                ORDER BY u.username ASC
                LIMIT 20";

        $searchTerm = "%{$search}%";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
