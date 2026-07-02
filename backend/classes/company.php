<?php

require_once __DIR__ . "/../config/Database.php";

class Company
{
    private $conn;
    private $table = "companies";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Add Company
    public function addCompany($user_id, $company_name, $location, $phone)
    {
        $sql = "INSERT INTO {$this->table}
                (user_id, company_name, location, phone)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $user_id,
            $company_name,
            $location,
            $phone
        ]);
    }

    // Get All Companies
    public function getCompanies()
    {
        $sql = "SELECT c.*,u.email

                FROM companies c

                JOIN users u

                ON c.user_id=u.user_id

                ORDER BY company_name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt;
    }

    // Get One Company
    public function getCompany($company_id)
    {
        $sql = "SELECT *

                FROM companies

                WHERE company_id=?";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$company_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update Company
    public function updateCompany($company_id,$company_name,$location,$phone)
    {
        $sql = "UPDATE companies

                SET
                company_name=?,
                location=?,
                phone=?

                WHERE company_id=?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $company_name,
            $location,
            $phone,
            $company_id
        ]);
    }

    // Delete Company
    public function deleteCompany($company_id)
    {
        $stmt=$this->conn->prepare(
            "DELETE FROM companies
             WHERE company_id=?"
        );

        return $stmt->execute([$company_id]);
    }

    // Count Companies
    public function countCompanies()
    {
        $sql = "SELECT COUNT(*) FROM companies";
        return $this->conn->query($sql)->fetchColumn();
    }

    // Get Company by User ID
    public function getCompanyByUserId($user_id)
    {
        $sql = "SELECT *
                FROM companies
                WHERE user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Search Companies
    public function searchCompanies($keyword)
    {
        $sql = "SELECT c.*, u.email
                FROM companies c
                JOIN users u ON c.user_id = u.user_id
                WHERE c.company_name LIKE ?
                OR c.location LIKE ?
                ORDER BY c.company_name ASC";

        $search = "%" . $keyword . "%";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$search, $search]);

        return $stmt;
    }
}

    // Count Companies
    public function countCompanies()
    {
        $stmt=$this->conn->query(
            "SELECT COUNT(*) FROM companies"
        );

        return $stmt->fetchColumn();
    }

    // Search Companies
    public function searchCompany($keyword)
    {
        $search="%".$keyword."%";

        $sql="SELECT *

              FROM companies

              WHERE
              company_name LIKE ?
              OR location LIKE ?";

        $stmt=$this->conn->prepare($sql);

        $stmt->execute([
            $search,
            $search
        ]);

        return $stmt;
    }

    // Company Profile
    public function getCompanyProfile($user_id)
    {
        $sql="SELECT *

              FROM companies

              WHERE user_id=?";

        $stmt=$this->conn->prepare($sql);

        $stmt->execute([$user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}