<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/../config/database.php";

class Internship
{
    // Encapsulation
    private $conn;
    private $table = "internships";

    // Constructor
    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /* CREATE INTERNSHIP */
    public function addInternship($company_id, $title, $description, $requirements, $deadline)
    {
        $sql = "INSERT INTO {$this->table}
                (company_id, title, description, requirements, deadline)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $company_id,
            $title,
            $description,
            $requirements,
            $deadline
        ]);
    }

    /* GET ALL INTERNSHIPS */
    public function getInternships()
    {
        $sql = "SELECT internships.*,
                       companies.company_name
                FROM internships
                INNER JOIN companies
                ON internships.company_id = companies.company_id
                ORDER BY deadline ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    /* GET SINGLE INTERNSHIP */
    public function getInternship($id)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE internship_id = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* UPDATE INTERNSHIP */
    public function updateInternship(
        $id,
        $title,
        $description,
        $requirements,
        $deadline
    )
    {
        $sql = "UPDATE {$this->table}

                SET
                title=?,
                description=?,
                requirements=?,
                deadline=?

                WHERE internship_id=?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $title,
            $description,
            $requirements,
            $deadline,
            $id
        ]);
    }

    /* DELETE INTERNSHIP */
    public function deleteInternship($id)
    {
        $sql = "DELETE FROM {$this->table}
                WHERE internship_id=?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$id]);
    }

    /* SEARCH INTERNSHIPS */
    public function searchInternships($keyword)
    {
        $sql = "SELECT internships.*,
                       companies.company_name

                FROM internships

                INNER JOIN companies

                ON internships.company_id=companies.company_id

                WHERE

                title LIKE ?

                OR description LIKE ?

                OR company_name LIKE ?

                ORDER BY deadline ASC";

        $search = "%" . $keyword . "%";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            $search,
            $search,
            $search
        ]);

        return $stmt;
    }

    /* COUNT INTERNSHIPS */
    public function countInternships()
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /* GET LATEST INTERNSHIPS */
    public function latestInternships($limit = 5)
    {
        $sql = "SELECT internships.*,
                       companies.company_name

                FROM internships

                INNER JOIN companies

                ON internships.company_id=companies.company_id

                ORDER BY internship_id DESC

                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt;
    }

    /* ===========================
       EXPIRED INTERNSHIPS
    =========================== */
    public function expiredInternships()
    {
        $sql = "SELECT *

                FROM internships

                WHERE deadline < CURDATE()";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt;
    }

    /* ===========================
       ACTIVE INTERNSHIPS
    =========================== */
    public function activeInternships()
    {
        $sql = "SELECT *

                FROM internships

                WHERE deadline >= CURDATE()

                ORDER BY deadline ASC";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt;
    }
}