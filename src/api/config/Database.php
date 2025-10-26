<?php
namespace App\Config;

class Database {
    private $host = 'localhost';
    private $database = 'allowance_tracker';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        try {
            $this->conn = new \PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(\PDOException $e) {
            http_response_code(500);
            echo json_encode(["message" => "Database connection error: " . $e->getMessage()]);
            return null;
        }
    }
}