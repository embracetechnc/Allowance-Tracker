<?php
namespace App\Controllers;

use App\Models\User;

class ProfileController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    public function uploadPhoto() {
        if (!isset($_FILES['photo'])) {
            http_response_code(400);
            echo json_encode(["message" => "No photo uploaded"]);
            return;
        }

        $file = $_FILES['photo'];
        $userId = $_REQUEST['user']->id;

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid file type"]);
            return;
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../../public/uploads/' . $filename;

        // Create uploads directory if it doesn't exist
        if (!file_exists(__DIR__ . '/../../public/uploads/')) {
            mkdir(__DIR__ . '/../../public/uploads/', 0777, true);
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Update user profile
            $this->user->id = $userId;
            $this->user->profile_photo = '/uploads/' . $filename;
            
            if ($this->user->updateProfilePhoto()) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Photo uploaded successfully",
                    "photo_url" => $this->user->profile_photo
                ]);
                return;
            }
        }

        http_response_code(500);
        echo json_encode(["message" => "Error uploading photo"]);
    }

    public function updateProfile() {
        $data = json_decode(file_get_contents("php://input"));
        $userId = $_REQUEST['user']->id;

        // Find user
        if (!$this->user->findById($userId)) {
            http_response_code(404);
            echo json_encode(["message" => "User not found"]);
            return;
        }

        // Update allowed fields
        $allowedFields = ['first_name', 'cashapp_username'];
        $updated = false;

        foreach ($allowedFields as $field) {
            if (isset($data->$field)) {
                $this->user->$field = $data->$field;
                $updated = true;
            }
        }

        if (!$updated) {
            http_response_code(400);
            echo json_encode(["message" => "No fields to update"]);
            return;
        }

        if ($this->user->update()) {
            http_response_code(200);
            echo json_encode([
                "message" => "Profile updated successfully",
                "user" => [
                    "id" => $this->user->id,
                    "email" => $this->user->email,
                    "first_name" => $this->user->first_name,
                    "profile_photo" => $this->user->profile_photo,
                    "cashapp_username" => $this->user->cashapp_username
                ]
            ]);
            return;
        }

        http_response_code(500);
        echo json_encode(["message" => "Error updating profile"]);
    }
}
