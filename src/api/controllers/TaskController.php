<?php
namespace App\Controllers;

use App\Services\NotificationService;

class TaskController {
    private $db;
    private $notificationService;

    public function __construct($db) {
        $this->db = $db;
        $this->notificationService = new NotificationService($db);
    }

    private function getUserFromToken() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        $auth = new AuthController($this->db);
        return $auth->validateToken($token);
    }

    public function getTasks() {
        $user = $this->getUserFromToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        $query = "SELECT * FROM tasks WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $user->id]);
        
        $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode(['tasks' => $tasks]);
    }

    public function completeTask() {
        $user = $this->getUserFromToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->task_type)) {
            http_response_code(400);
            echo json_encode(["message" => "Task type is required"]);
            return;
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Create or update task
            $query = "INSERT INTO tasks (user_id, task_type, status, completed_at, created_at) 
                     VALUES (:user_id, :task_type, 'completed', NOW(), NOW())";
                     
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'user_id' => $user->id,
                'task_type' => $data->task_type
            ]);

            if ($result) {
                $taskId = $this->db->lastInsertId();
                
                // Get task data for notification
                $query = "SELECT * FROM tasks WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['id' => $taskId]);
                $taskData = $stmt->fetch(\PDO::FETCH_ASSOC);

                // Send notification
                $this->notificationService->notifyTaskCompletion($taskData);

                $this->db->commit();
                
                http_response_code(200);
                echo json_encode(["message" => "Task marked as completed"]);
                return;
            }

            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["message" => "Error updating task"]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["message" => "Error: " . $e->getMessage()]);
        }
    }

    public function getPendingVerifications() {
        $user = $this->getUserFromToken();
        if (!$user || $user->role !== 'parent') {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        $query = "SELECT t.*, u.first_name, u.email 
                 FROM tasks t 
                 JOIN users u ON t.user_id = u.id 
                 WHERE t.status = 'completed' 
                 ORDER BY t.completed_at ASC";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $tasks = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        http_response_code(200);
        echo json_encode(['tasks' => $tasks]);
    }

    public function verifyTask() {
        $user = $this->getUserFromToken();
        if (!$user || $user->role !== 'parent') {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->task_id) || !isset($data->approved)) {
            http_response_code(400);
            echo json_encode(["message" => "Task ID and approval status are required"]);
            return;
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            $status = $data->approved ? 'verified' : 'rejected';
            
            $query = "UPDATE tasks 
                     SET status = :status, 
                         verified_by = :parent_id,
                         verified_at = NOW() 
                     WHERE id = :task_id";
                     
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                'status' => $status,
                'parent_id' => $user->id,
                'task_id' => $data->task_id
            ]);

            if ($result) {
                // Get task data for notification
                $query = "SELECT * FROM tasks WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->execute(['id' => $data->task_id]);
                $taskData = $stmt->fetch(\PDO::FETCH_ASSOC);

                // Add verification data
                $taskData['approved'] = $data->approved;
                $taskData['verified_by'] = $user->id;
                $taskData['verified_at'] = date('Y-m-d H:i:s');

                // Send notification
                $this->notificationService->notifyTaskVerification($taskData);

                $this->db->commit();
                
                http_response_code(200);
                echo json_encode(["message" => "Task verification updated"]);
                return;
            }

            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["message" => "Error updating task"]);
        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(["message" => "Error: " . $e->getMessage()]);
        }
    }

    public function getNotifications() {
        $user = $this->getUserFromToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        $notifications = $this->notificationService->getUnreadNotifications($user->id);
        
        http_response_code(200);
        echo json_encode(['notifications' => $notifications]);
    }

    public function markNotificationRead() {
        $user = $this->getUserFromToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->notification_id)) {
            http_response_code(400);
            echo json_encode(["message" => "Notification ID is required"]);
            return;
        }

        $result = $this->notificationService->markNotificationRead($data->notification_id, $user->id);
        
        if ($result) {
            http_response_code(200);
            echo json_encode(["message" => "Notification marked as read"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error updating notification"]);
        }
    }
}