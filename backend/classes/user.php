<?php

class User
{
    private $conn;
    private $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function register(
        $full_name,
        $email,
        $password,
        $role
    )
    {

        $query = "INSERT INTO users
                 (full_name,email,password,role)
                 VALUES
                 (:full_name,:email,:password,:role)";

        $stmt = $this->conn->prepare($query);

        $hashedPassword =
            password_hash($password,PASSWORD_DEFAULT);

        $stmt->bindParam(":full_name",$full_name);
        $stmt->bindParam(":email",$email);
        $stmt->bindParam(":password",$hashedPassword);
        $stmt->bindParam(":role",$role);

        return $stmt->execute();
    }

    public function findByEmail($email)
    {

        $query = "SELECT * FROM users
                 WHERE email=:email
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":email",$email);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}