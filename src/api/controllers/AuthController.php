<?php
namespace App\Controllers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $db;
    private $jwtSecret = 'your-secret-key-here'; // In production, use a secure secret from environment variables

    public function __construct($db) {
        $this->db = $db;
    }

    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return $decoded->data;
        } catch(\Exception $e) {
            return null;
        }
    }

    public function login() {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        if(!isset($data->email) || !isset($data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields"]);
            return;
        }

        // Find user
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['email' => $data->email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Debug output
        error_log("Login attempt for: " . $data->email);
        error_log("Password provided: " . $data->password);
        error_log("User found: " . ($user ? "yes" : "no"));
        if ($user) {
            error_log("Stored hash: " . $user['password']);
            error_log("Password verify result: " . (password_verify($data->password, $user['password']) ? "true" : "false"));
        }

        if($user && password_verify($data->password, $user['password'])) {
            $token = JWT::encode([
                'data' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'iat' => time(),
                'exp' => time() + 3600
            ], $this->jwtSecret, 'HS256');
            
            http_response_code(200);
            echo json_encode([
                "message" => "Login successful",
                "token" => $token,
                "user" => [
                    "id" => $user['id'],
                    "email" => $user['email'],
                    "role" => $user['role'],
                    "first_name" => $user['first_name'],
                    "profile_photo" => $user['profile_photo'],
                    "cashapp_username" => $user['cashapp_username']
                ]
            ]);
            return;
        }

        http_response_code(401);
        echo json_encode(["message" => "Invalid credentials"]);
    }

    public function register() {
        // Get posted data
        $data = json_decode(file_get_contents("php://input"));
        
        if(!isset($data->email) || !isset($data->password) || !isset($data->role) || !isset($data->first_name)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields"]);
            return;
        }

        // For child accounts, validate birthday
        if($data->role === 'child') {
            if(!isset($data->birthday) || $data->birthday !== '2012-01-23') {
                http_response_code(400);
                echo json_encode(["message" => "Invalid or missing birthday"]);
                return;
            }
        }

        // Check if email already exists
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['email' => $data->email]);
        if($stmt->fetch()) {
            http_response_code(400);
            echo json_encode(["message" => "Email already exists"]);
            return;
        }

        // Create user
        $query = "INSERT INTO users (email, password, role, first_name, birthday) 
                 VALUES (:email, :password, :role, :first_name, :birthday)";
        $stmt = $this->db->prepare($query);
        
        $hashedPassword = password_hash($data->password, PASSWORD_BCRYPT);
        $birthday = $data->birthday ?? null;

        $result = $stmt->execute([
            'email' => $data->email,
            'password' => $hashedPassword,
            'role' => $data->role,
            'first_name' => $data->first_name,
            'birthday' => $birthday
        ]);

        if($result) {
            $userId = $this->db->lastInsertId();
            
            $token = JWT::encode([
                'data' => [
                    'id' => $userId,
                    'email' => $data->email,
                    'role' => $data->role
                ],
                'iat' => time(),
                'exp' => time() + 3600
            ], $this->jwtSecret, 'HS256');

            http_response_code(201);
            echo json_encode([
                "message" => "User created successfully",
                "token" => $token,
                "user" => [
                    "id" => $userId,
                    "email" => $data->email,
                    "role" => $data->role,
                    "first_name" => $data->first_name
                ]
            ]);
            return;
        }

        http_response_code(500);
        echo json_encode(["message" => "Unable to create user"]);
    }
}