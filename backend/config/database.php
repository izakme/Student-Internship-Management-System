<?php

class Database
{
    private $host;
    private $dbname;
    private $username;
    private $password;

    public function __construct()
    {
        $this->loadEnv();
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->dbname = getenv('DB_NAME') ?: 'internship_db';
        $this->username = getenv('DB_USERNAME') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
    }

    private function loadEnv()
    {
        $envFile = __DIR__ . '/../../.env';
        if (!file_exists($envFile)) return;
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $value = trim($value, '"\'');
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }

    public $conn;

    public function connect()
    {
        try {

            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname}",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            return $this->conn;

        } catch(PDOException $e) {

            die("Connection Failed: " . $e->getMessage());

        }
    }
}