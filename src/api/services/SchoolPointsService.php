<?php
namespace App\Services;

use App\Models\SchoolPoints;
use Exception;
use PDO;

class SchoolPointsService {
    private $db;
    private $schoolPoints;
    private $notificationService;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->schoolPoints = new SchoolPoints($db);
        $this->notificationService = new NotificationService($db);
    }

    public function addPoints($userId, $points, $reason, $assignedBy, $categoryId = null) {
        try {
            // Validate inputs
            $this->validatePointsInput($points, $reason);
            $this->validateUsers($userId, $assignedBy);

            // Add points
            $this->schoolPoints->addPoints($userId, $points, $reason, $assignedBy, $categoryId);

            // Get updated summary
            $summary = $this->schoolPoints->getWeeklySummary($userId);

            // Send notifications
            $this->sendPointNotifications($userId, $points, $reason, $assignedBy, $summary);

            return [
                'success' => true,
                'message' => 'Points added successfully',
                'summary' => $summary
            ];
        } catch (Exception $e) {
            error_log("Error adding points: " . $e->getMessage());
            throw new Exception('Failed to add points: ' . $e->getMessage());
        }
    }

    public function removePoints($userId, $points, $reason, $performedBy) {
        try {
            // Validate inputs
            $this->validatePointsInput($points, $reason);
            $this->validateUsers($userId, $performedBy);

            // Remove points
            $this->schoolPoints->removePoints($userId, $points, $reason, $performedBy);

            // Get updated summary
            $summary = $this->schoolPoints->getWeeklySummary($userId);

            // Send notifications
            $this->sendPointNotifications($userId, -$points, $reason, $performedBy, $summary);

            return [
                'success' => true,
                'message' => 'Points removed successfully',
                'summary' => $summary
            ];
        } catch (Exception $e) {
            error_log("Error removing points: " . $e->getMessage());
            throw new Exception('Failed to remove points: ' . $e->getMessage());
        }
    }

    private function validatePointsInput($points, $reason) {
        if (!is_numeric($points) || $points <= 0) {
            throw new Exception('Points must be a positive number');
        }

        if (empty(trim($reason))) {
            throw new Exception('Reason is required');
        }

        if (strlen($reason) > 500) {
            throw new Exception('Reason must be less than 500 characters');
        }
    }

    private function validateUsers($userId, $performedBy) {
        // Check if users exist and have correct roles
        $stmt = $this->db->prepare("
            SELECT id, role, first_name
            FROM users
            WHERE id IN (?, ?)
        ");
        $stmt->execute([$userId, $performedBy]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($users) !== 2) {
            throw new Exception('Invalid user IDs provided');
        }

        $child = null;
        $parent = null;
        foreach ($users as $user) {
            if ($user['id'] == $userId && $user['role'] === 'child') {
                $child = $user;
            }
            if ($user['id'] == $performedBy && $user['role'] === 'parent') {
                $parent = $user;
            }
        }

        if (!$child) {
            throw new Exception('Invalid child user ID');
        }
        if (!$parent) {
            throw new Exception('Points can only be assigned by parents');
        }
    }

    private function sendPointNotifications($userId, $points, $reason, $performedBy, $summary) {
        // Get user details
        $stmt = $this->db->prepare("
            SELECT u1.first_name as child_name, u2.first_name as parent_name
            FROM users u1
            JOIN users u2 ON u2.id = ?
            WHERE u1.id = ?
        ");
        $stmt->execute([$performedBy, $userId]);
        $names = $stmt->fetch(PDO::FETCH_ASSOC);

        // Prepare notification data
        $notificationData = [
            'user_id' => $userId,
            'type' => $points > 0 ? 'points_added' : 'points_removed',
            'message' => sprintf(
                '%s %s %d point%s. Reason: %s',
                $names['parent_name'],
                $points > 0 ? 'added' : 'removed',
                abs($points),
                abs($points) === 1 ? '' : 's',
                $reason
            ),
            'details' => [
                'points' => $points,
                'reason' => $reason,
                'weekly_total' => $summary['total_points'],
                'allowance_impact' => $summary['allowance_deduction']
            ]
        ];

        // Send notification
        $this->notificationService->sendNotification($notificationData);
    }

    public function getWeeklyReport($userId, $weekNumber = null, $year = null) {
        try {
            // Get points for the week
            $points = $this->schoolPoints->getWeeklyPoints($userId, $weekNumber, $year);

            // Get weekly summary
            $summary = $this->schoolPoints->getWeeklySummary($userId, $weekNumber, $year);

            return [
                'success' => true,
                'points' => $points,
                'summary' => $summary
            ];
        } catch (Exception $e) {
            error_log("Error getting weekly report: " . $e->getMessage());
            throw new Exception('Failed to get weekly report: ' . $e->getMessage());
        }
    }

    public function getPointHistory($userId, $limit = 50) {
        try {
            $history = $this->schoolPoints->getPointHistory($userId, $limit);

            return [
                'success' => true,
                'history' => $history
            ];
        } catch (Exception $e) {
            error_log("Error getting point history: " . $e->getMessage());
            throw new Exception('Failed to get point history: ' . $e->getMessage());
        }
    }

    public function getCategories() {
        try {
            $categories = $this->schoolPoints->getCategories();

            return [
                'success' => true,
                'categories' => $categories
            ];
        } catch (Exception $e) {
            error_log("Error getting categories: " . $e->getMessage());
            throw new Exception('Failed to get categories: ' . $e->getMessage());
        }
    }

    public function processWeeklyReset() {
        try {
            // Reset points for the week
            $this->schoolPoints->resetWeeklyPoints();

            return [
                'success' => true,
                'message' => 'Weekly points reset successfully'
            ];
        } catch (Exception $e) {
            error_log("Error resetting weekly points: " . $e->getMessage());
            throw new Exception('Failed to reset weekly points: ' . $e->getMessage());
        }
    }
}
