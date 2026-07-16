<?php

require_once __DIR__ . "/../config/database.php";

class Student
{
    private $conn;
    private $table = "students";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /* =========================
       GET STUDENT BY USER_ID
    ========================= */
    public function getStudentByUser($user_id)
    {
        $sql = "SELECT s.*, u.email, u.username
                FROM {$this->table} s
                JOIN users u ON s.user_id = u.user_id
                WHERE s.user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       GET STUDENT BY ID
    ========================= */
    public function getStudent($student_id)
    {
        $sql = "SELECT s.*, u.email, u.username
                FROM {$this->table} s
                JOIN users u ON s.user_id = u.user_id
                WHERE s.student_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$student_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       GET ALL STUDENTS
    ========================= */
    public function getAllStudents()
    {
        $sql = "SELECT s.*, u.email, u.username
                FROM {$this->table} s
                JOIN users u ON s.user_id = u.user_id
                ORDER BY u.username ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    /* =========================
       UPDATE STUDENT PROFILE
    ========================= */
    public function updateStudent($student_id, $registration_no, $course, $year_of_study, $phone)
    {
        $sql = "UPDATE {$this->table}
                SET registration_no = ?,
                    course = ?,
                    year_of_study = ?,
                    phone = ?
                WHERE student_id = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $registration_no,
            $course,
            $year_of_study,
            $phone,
            $student_id
        ]);
    }

    /* =========================
       UPDATE RESUME PATH
    ========================= */
    public function updateResume($student_id, $resumePath)
    {
        $sql = "UPDATE {$this->table}
                SET resume = ?
                WHERE student_id = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$resumePath, $student_id]);
    }

    /* =========================
       COUNT STUDENTS
    ========================= */
    public function countStudents()
    {
        return $this->conn->query("SELECT COUNT(*) FROM {$this->table}")->fetchColumn();
    }

    /* =========================
       DELETE STUDENT
    ========================= */
    public function deleteStudent($student_id)
    {
        $sql = "DELETE FROM {$this->table}
                WHERE student_id = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$student_id]);
    }
}
