<?php

class User
{
    private $conn;
    private $table = "users";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /* =========================
       REGISTER USER
    ========================= */
    public function register($full_name, $email, $password, $role)
    {
        $query = "INSERT INTO users (full_name, email, password, role)
                  VALUES (:full_name, :email, :password, :role)";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt->execute([
            ":full_name" => $full_name,
            ":email" => $email,
            ":password" => $hashedPassword,
            ":role" => $role
        ]);

        $user_id = $this->conn->lastInsertId();

        /* =========================
           AUTO CREATE STUDENT PROFILE
        ========================= */
        if ($role === "student") {

            $stmt2 = $this->conn->prepare("
                INSERT INTO students (user_id, registration_no, course, year_of_study, phone)
                VALUES (?, NULL, NULL, NULL, NULL)
            ");

            $stmt2->execute([$user_id]);
        }

        return true;
    }

    /* =========================
       GET STUDENT BY USER ID
    ========================= */
    public function getStudentByUserId($user_id)
    {
        $sql = "SELECT student_id, registration_no, course, year_of_study
                FROM students
                WHERE user_id = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       FIND USER BY EMAIL
    ========================= */
    public function findByEmail($email)
    {
        $query = "SELECT * FROM users
                  WHERE email = :email
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":email", $email);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       GET USER BY ID
    ========================= */
    public function getUserById($user_id)
    {
        $query = "SELECT * FROM users
                  WHERE user_id = ?
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$user_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /* =========================
       GET ALL USERS
    ========================= */
    public function getAllUsers()
    {
        $query = "SELECT * FROM users ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /* =========================
       COUNT USERS
    ========================= */
    public function countUsers()
    {
        $query = "SELECT COUNT(*) FROM users";

        return $this->conn->query($query)->fetchColumn();
    }

    /* =========================
       COUNT BY ROLE
    ========================= */
    public function countByRole($role)
    {
        $query = "SELECT COUNT(*) FROM users WHERE role = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$role]);

        return $stmt->fetchColumn();
    }

    /* =========================
       DELETE USER
    ========================= */
    public function deleteUser($user_id)
    {
        $query = "DELETE FROM users WHERE user_id = ?";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([$user_id]);
    }

    /* =========================
       UPDATE USER
    ========================= */
    public function updateUser($user_id, $full_name, $email)
    {
        $query = "UPDATE users 
                  SET full_name = ?, email = ?
                  WHERE user_id = ?";

        $stmt = $this->conn->prepare($query);

        return $stmt->execute([$full_name, $email, $user_id]);
    }
}