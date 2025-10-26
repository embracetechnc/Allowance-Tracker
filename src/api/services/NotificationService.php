<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Config\Config;

class NotificationService {
    private $mailer = null;
    private $db;
    private $emailEnabled = false;

    public function __construct($db) {
        $this->db = $db;
        $this->initializeMailer();
    }

    private function initializeMailer() {
        try {
            // Check if email configuration exists
            if (!Config::has('email.host') || !Config::has('email.username') || !Config::has('email.password')) {
                error_log("Email configuration missing. Email notifications will be disabled.");
                return;
            }

            $this->mailer = new PHPMailer(true);

            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = Config::get('email.host');
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = Config::get('email.username');
            $this->mailer->Password = Config::get('email.password');
            $this->mailer->SMTPSecure = Config::get('email.encryption');
            $this->mailer->Port = Config::get('email.port');

            // Default settings
            $this->mailer->isHTML(true);
            $this->mailer->setFrom(
                Config::get('email.from_email'),
                Config::get('email.from_name')
            );

            $this->emailEnabled = true;
        } catch (Exception $e) {
            error_log("Mailer initialization error: " . $e->getMessage());
        }
    }

    public function notifyTaskCompletion($taskData) {
        try {
            // Get parent emails
            $query = "SELECT email FROM users WHERE role = 'parent'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $parents = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get child's name
            $query = "SELECT first_name FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['user_id' => $taskData['user_id']]);
            $child = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Send email if enabled
            if ($this->emailEnabled && $this->mailer) {
                $this->mailer->clearAddresses();
                
                // Add parent recipients
                foreach ($parents as $parent) {
                    $this->mailer->addAddress($parent['email']);
                }

                $this->mailer->Subject = "Task Completion: {$child['first_name']} - {$taskData['task_type']}";
                
                // Create email body
                $body = $this->getTaskCompletionTemplate([
                    'child_name' => $child['first_name'],
                    'task_type' => $this->formatTaskType($taskData['task_type']),
                    'completed_at' => date('F j, Y g:i A', strtotime($taskData['completed_at'])),
                    'verification_link' => Config::get('app.url') . '/verify-task/' . $taskData['id']
                ]);
                $this->mailer->Body = $body;

                $this->mailer->send();
            }

            // Record notification in database
            return $this->recordNotification(
                $taskData['user_id'],
                'task_completed',
                "Task {$taskData['task_type']} marked as complete"
            );
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            // Still record the notification even if email fails
            return $this->recordNotification(
                $taskData['user_id'],
                'task_completed',
                "Task {$taskData['task_type']} marked as complete"
            );
        }
    }

    public function notifyTaskVerification($taskData) {
        try {
            // Get child's email
            $query = "SELECT u.email, u.first_name, p.first_name as parent_name 
                     FROM users u 
                     JOIN users p ON p.id = :parent_id
                     WHERE u.id = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'user_id' => $taskData['user_id'],
                'parent_id' => $taskData['verified_by']
            ]);
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            // Send email if enabled
            if ($this->emailEnabled && $this->mailer) {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($data['email']);

                $status = $taskData['approved'] ? 'Approved' : 'Needs Improvement';
                $this->mailer->Subject = "Task {$status}: {$this->formatTaskType($taskData['task_type'])}";
                
                // Create email body
                $body = $this->getTaskVerificationTemplate([
                    'child_name' => $data['first_name'],
                    'task_type' => $this->formatTaskType($taskData['task_type']),
                    'status' => $status,
                    'parent_name' => $data['parent_name'],
                    'verified_at' => date('F j, Y g:i A', strtotime($taskData['verified_at'])),
                    'approved' => $taskData['approved']
                ]);
                $this->mailer->Body = $body;

                $this->mailer->send();
            }

            // Record notification in database
            return $this->recordNotification(
                $taskData['user_id'],
                'task_verified',
                "Task {$taskData['task_type']} was " . ($taskData['approved'] ? 'approved' : 'rejected')
            );
        } catch (Exception $e) {
            error_log("Email Error: " . $e->getMessage());
            // Still record the notification even if email fails
            return $this->recordNotification(
                $taskData['user_id'],
                'task_verified',
                "Task {$taskData['task_type']} was " . ($taskData['approved'] ? 'approved' : 'rejected')
            );
        }
    }

    private function formatTaskType($taskType) {
        return ucwords(str_replace('_', ' ', $taskType));
    }

    private function getTaskCompletionTemplate($data) {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2563eb;'>Task Completion Notification</h2>
            <p>Hello Parents,</p>
            <p>{$data['child_name']} has marked the following task as complete:</p>
            <div style='background-color: #f3f4f6; padding: 15px; border-radius: 5px;'>
                <p><strong>Task:</strong> {$data['task_type']}</p>
                <p><strong>Completed at:</strong> {$data['completed_at']}</p>
            </div>
            <p>Please verify this task's completion in the app.</p>
            <a href='{$data['verification_link']}' style='display: inline-block; background-color: #2563eb; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 15px;'>
                Verify Task
            </a>
        </div>";
    }

    private function getTaskVerificationTemplate($data) {
        $color = $data['approved'] ? '#059669' : '#dc2626';
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: {$color};'>Task {$data['status']}</h2>
            <p>Hi {$data['child_name']},</p>
            <p>Your task has been reviewed:</p>
            <div style='background-color: #f3f4f6; padding: 15px; border-radius: 5px;'>
                <p><strong>Task:</strong> {$data['task_type']}</p>
                <p><strong>Status:</strong> {$data['status']}</p>
                <p><strong>Verified by:</strong> {$data['parent_name']}</p>
                <p><strong>Verified at:</strong> {$data['verified_at']}</p>
            </div>
            " . ($data['approved'] ? "
            <p style='color: #059669;'>Great job! Keep up the good work!</p>
            " : "
            <p style='color: #dc2626;'>Please review the task requirements and try again. You can do it!</p>
            ") . "
        </div>";
    }

    private function recordNotification($userId, $type, $message) {
        $query = "INSERT INTO notifications (user_id, type, message) 
                 VALUES (:user_id, :type, :message)";
                 
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message
        ]);
    }

    public function getUnreadNotifications($userId) {
        $query = "SELECT * FROM notifications 
                 WHERE user_id = :user_id 
                 AND read_at IS NULL 
                 ORDER BY created_at DESC";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function markNotificationRead($notificationId, $userId) {
        $query = "UPDATE notifications 
                 SET read_at = NOW() 
                 WHERE id = :id AND user_id = :user_id";
                 
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }
}