<?php
namespace App\Controllers;

use App\Services\NotificationService;
use App\Models\User;

class NotificationController {
    private $notificationService;
    private $db;

    public function __construct($db) {
        $this->notificationService = new NotificationService();
        $this->db = $db;
    }

    public function sendTaskCompletion() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->task_id) || !isset($data->child_id)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields"]);
            return;
        }

        // Get task and child details
        $taskQuery = "SELECT t.*, u.first_name as child_name, u.email as child_email 
                     FROM tasks t 
                     JOIN users u ON t.user_id = u.id 
                     WHERE t.id = :task_id AND t.user_id = :child_id";
                     
        $stmt = $this->db->prepare($taskQuery);
        $stmt->execute([
            'task_id' => $data->task_id,
            'child_id' => $data->child_id
        ]);
        
        $taskData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$taskData) {
            http_response_code(404);
            echo json_encode(["message" => "Task not found"]);
            return;
        }

        // Add verification link
        $taskData['verification_link'] = $_ENV['APP_URL'] . '/verify-task/' . $taskData['id'];

        // Send notification
        if ($this->notificationService->notifyTaskCompletion($taskData)) {
            // Record notification in database
            $this->recordNotification($data->child_id, 'task_completed', "Task {$taskData['task_type']} marked as complete");
            
            http_response_code(200);
            echo json_encode(["message" => "Notification sent successfully"]);
            return;
        }

        http_response_code(500);
        echo json_encode(["message" => "Error sending notification"]);
    }

    public function sendTaskVerification() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->task_id) || !isset($data->verified) || !isset($data->parent_id)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields"]);
            return;
        }

        // Get task, child, and parent details
        $taskQuery = "SELECT t.*, 
                            c.first_name as child_name, 
                            c.email as child_email,
                            p.first_name as parent_name
                     FROM tasks t 
                     JOIN users c ON t.user_id = c.id 
                     JOIN users p ON p.id = :parent_id
                     WHERE t.id = :task_id";
                     
        $stmt = $this->db->prepare($taskQuery);
        $stmt->execute([
            'task_id' => $data->task_id,
            'parent_id' => $data->parent_id
        ]);
        
        $taskData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$taskData) {
            http_response_code(404);
            echo json_encode(["message" => "Task not found"]);
            return;
        }

        $taskData['verified'] = $data->verified;
        $taskData['verified_at'] = date('Y-m-d H:i:s');

        // Send notification
        if ($this->notificationService->notifyTaskVerification($taskData)) {
            // Record notification in database
            $status = $data->verified ? 'approved' : 'rejected';
            $this->recordNotification(
                $taskData['user_id'],
                'task_verified',
                "Task {$taskData['task_type']} was {$status} by {$taskData['parent_name']}"
            );
            
            http_response_code(200);
            echo json_encode(["message" => "Notification sent successfully"]);
            return;
        }

        http_response_code(500);
        echo json_encode(["message" => "Error sending notification"]);
    }

    public function sendPayoutNotification() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->child_id) || !isset($data->amount) || !isset($data->transaction_id)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields"]);
            return;
        }

        // Get child details
        $userModel = new User($this->db);
        if (!$userModel->findById($data->child_id)) {
            http_response_code(404);
            echo json_encode(["message" => "User not found"]);
            return;
        }

        $payoutData = [
            'child_name' => $userModel->first_name,
            'child_email' => $userModel->email,
            'amount' => $data->amount,
            'cashapp_username' => $userModel->cashapp_username,
            'transaction_id' => $data->transaction_id,
            'period_start' => date('Y-m-d', strtotime('-14 days')),
            'period_end' => date('Y-m-d')
        ];

        // Send notification
        if ($this->notificationService->notifyPayoutProcessed($payoutData)) {
            // Record notification in database
            $this->recordNotification(
                $data->child_id,
                'payout_processed',
                "Allowance payout of ${$data->amount} processed"
            );
            
            http_response_code(200);
            echo json_encode(["message" => "Notification sent successfully"]);
            return;
        }

        http_response_code(500);
        echo json_encode(["message" => "Error sending notification"]);
    }

    private function recordNotification($userId, $type, $message) {
        $query = "INSERT INTO notifications (user_id, type, message) 
                 VALUES (:user_id, :type, :message)";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message
        ]);
    }
}
