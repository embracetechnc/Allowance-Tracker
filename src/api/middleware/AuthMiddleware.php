<?php
namespace App\Middleware;

use App\Controllers\AuthController;

class AuthMiddleware {
    private $auth;
    private $allowedRoles;

    public function __construct($db, $allowedRoles = []) {
        $this->auth = new AuthController($db);
        $this->allowedRoles = $allowedRoles;
    }

    public function handle() {
        // Get all headers
        $headers = getallheaders();
        
        // Check for Authorization header
        if(!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["message" => "No token provided"]);
            exit();
        }

        // Get token from header
        $authHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authHeader);

        // Validate token
        $decoded = $this->auth->validateToken($token);
        if(!$decoded) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid or expired token"]);
            exit();
        }

        // Check role if roles are specified
        if(!empty($this->allowedRoles) && !in_array($decoded->role, $this->allowedRoles)) {
            http_response_code(403);
            echo json_encode(["message" => "Insufficient permissions"]);
            exit();
        }

        // Add user data to request
        $_REQUEST['user'] = $decoded;
        return true;
    }
}
