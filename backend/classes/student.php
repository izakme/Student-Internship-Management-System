<?php

require_once __DIR__ . "/../config/database.php";

class Student
{
    // Encapsulation
    private $conn;
    private $table = "students";

    // Constructor
    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /* ADD STUDENT */

    public function addStudent(
        $user_id,
        $registration_no,
        $course,
        $year_of_study,
        $phone
    )
    {
        $sql = "INSERT INTO {$this->table}
                (user_id, registration_no, course, year_of_study, phone)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $user_id,
            $registration_no,
            $course,
            $year_of_study,
            $phone
        ]);
    }

    /* GET ALL STUDENTS */

    public function getStudents()
    {
        $sql = "SELECT

                    s.student_id,
                    u.full_name,
                    u.email,
                    s.registration_no,
                    s.course,
                    s.year_of_study,
                    s.phone

                FROM students s

                JOIN users u

                ON s.user_id = u.user_id

                ORDER BY u.full_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    /* GET ONE STUDENT */

    public function getStudent($student_id)
    {
        $sql = "SELECT

                    s.*,
                    u.full_name,
                    u.email

                FROM students s

                JOIN users u

                ON s.user_id=u.user_id

                WHERE s.student_id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$student_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* GET STUDENT BY USER ID*/

    public function getStudentByUser($user_id)
    {
        $sql = "SELECT *

                FROM students

                WHERE user_id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* UPDATE PROFILE*/

    public function updateStudent(
        $student_id,
        $registration_no,
        $course,
        $year_of_study,
        $phone
    )
    {
        $sql = "UPDATE {$this->table}

                SET

                registration_no=?,
                course=?,
                year_of_study=?,
                phone=?

                WHERE student_id=?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $registration_no,
            $course,
            $year_of_study,
            $phone,
            $student_id
        ]);
    }

    /* DELETE STUDENT */

    public function deleteStudent($student_id)
    {
        $sql = "DELETE FROM {$this->table}

                WHERE student_id=?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$student_id]);
    }

    /* COUNT STUDENTS */

    public function countStudents()
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM students");

        return $stmt->fetchColumn();
    }

    /*SEARCH STUDENTS */

    public function searchStudents($keyword)
    {
        $sql = "SELECT

                    s.student_id,
                    u.full_name,
                    u.email,
                    s.registration_no,
                    s.course,
                    s.year_of_study

                FROM students s

                JOIN users u

                ON s.user_id=u.user_id

                WHERE

                u.full_name LIKE ?

                OR s.registration_no LIKE ?

                OR s.course LIKE ?";

        $search = "%" . $keyword . "%";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            $search,
            $search,
            $search
        ]);

        return $stmt;
    }

    /* RECENT STUDENTS */

    public function latestStudents($limit = 5)
    {
        $sql = "SELECT

                    s.student_id,
                    u.full_name,
                    s.registration_no,
                    s.course

                FROM students s

                JOIN users u

                ON s.user_id=u.user_id

                ORDER BY s.student_id DESC

                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt;
    }

}