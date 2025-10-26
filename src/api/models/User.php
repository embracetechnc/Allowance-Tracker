<?php
namespace App\Models;

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $email;
    public $password;
    public $role;
    public $first_name;
    public $profile_photo;
    public $cashapp_username;
    public $birthday;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . "
                SET
                    email = :email,
                    password = :password,
                    role = :role,
                    first_name = :first_name,
                    birthday = :birthday,
                    created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->role = htmlspecialchars(strip_tags($this->role));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));

        // Bind parameters
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':birthday', $this->birthday);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if($row) {
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->password = $row['password'];
            $this->role = $row['role'];
            $this->first_name = $row['first_name'];
            $this->profile_photo = $row['profile_photo'];
            $this->cashapp_username = $row['cashapp_username'];
            $this->birthday = $row['birthday'];
            $this->created_at = $row['created_at'];
            return true;
        }
        return false;
    }

    public function updateCashApp() {
        $query = "UPDATE " . $this->table . "
                SET
                    cashapp_username = :cashapp_username
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cashapp_username', $this->cashapp_username);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function updateProfilePhoto() {
        $query = "UPDATE " . $this->table . "
                SET
                    profile_photo = :profile_photo
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':profile_photo', $this->profile_photo);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }
}
