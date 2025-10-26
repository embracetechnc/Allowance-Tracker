<?php
namespace App\Controllers;

class ReportController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
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

    public function getEarnings() {
        $user = $this->getUserFromToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        // Get target user ID (parents can view children's reports)
        $targetUserId = $user->id;
        if ($user->role === 'parent' && isset($_GET['user_id'])) {
            // Verify that the requested user is a child
            $query = "SELECT id FROM users WHERE id = :id AND role = 'child'";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $_GET['user_id']]);
            if ($childUser = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $targetUserId = $childUser['id'];
            }
        }
        
        // Get earnings for the last 6 months
        $query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as earnings,
                    SUM(CASE WHEN type = 'deduction' THEN amount ELSE 0 END) * -1 as deductions
                 FROM transactions 
                 WHERE user_id = :user_id 
                 AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                 ORDER BY month ASC";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $targetUserId]);
        $earnings = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get total earnings and deductions
        $totalsQuery = "SELECT 
                        SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_earnings,
                        SUM(CASE WHEN type = 'deduction' THEN amount ELSE 0 END) * -1 as total_deductions
                       FROM transactions 
                       WHERE user_id = :user_id";
                       
        $stmt = $this->db->prepare($totalsQuery);
        $stmt->execute(['user_id' => $targetUserId]);
        $totals = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Format the response
        $formattedEarnings = array_map(function($month) {
            return [
                'month' => $month['month'],
                'earnings' => floatval($month['earnings']),
                'deductions' => floatval($month['deductions']),
                'net' => floatval($month['earnings']) - floatval($month['deductions'])
            ];
        }, $earnings);

        $formattedTotals = [
            'total_earnings' => floatval($totals['total_earnings']),
            'total_deductions' => floatval($totals['total_deductions']),
            'net_total' => floatval($totals['total_earnings']) - floatval($totals['total_deductions'])
        ];

        http_response_code(200);
        echo json_encode([
            "monthly_earnings" => $formattedEarnings,
            "totals" => $formattedTotals
        ]);
    }

    public function getTaskStats() {
        $user = $this->getUserFromToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        // Get target user ID (parents can view children's stats)
        $targetUserId = $user->id;
        if ($user->role === 'parent' && isset($_GET['user_id'])) {
            // Verify that the requested user is a child
            $query = "SELECT id FROM users WHERE id = :id AND role = 'child'";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $_GET['user_id']]);
            if ($childUser = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $targetUserId = $childUser['id'];
            }
        }
        
        // Get task completion stats for the last 30 days
        $query = "SELECT 
                    DATE(completed_at) as date,
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified_tasks
                 FROM tasks 
                 WHERE user_id = :user_id 
                 AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                 GROUP BY DATE(completed_at)
                 ORDER BY date ASC";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $targetUserId]);
        $taskStats = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get task type breakdown
        $typeQuery = "SELECT 
                        task_type,
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified
                     FROM tasks 
                     WHERE user_id = :user_id 
                     AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                     GROUP BY task_type";
                     
        $stmt = $this->db->prepare($typeQuery);
        $stmt->execute(['user_id' => $targetUserId]);
        $taskTypes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            "daily_stats" => $taskStats,
            "task_types" => $taskTypes
        ]);
    }

    public function getSchoolPoints() {
        $user = $this->getUserFromToken();
        if (!$user) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }

        // Get target user ID (parents can view children's points)
        $targetUserId = $user->id;
        if ($user->role === 'parent' && isset($_GET['user_id'])) {
            // Verify that the requested user is a child
            $query = "SELECT id FROM users WHERE id = :id AND role = 'child'";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['id' => $_GET['user_id']]);
            if ($childUser = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $targetUserId = $childUser['id'];
            }
        }
        
        $query = "SELECT 
                    week_start,
                    points
                 FROM school_points 
                 WHERE user_id = :user_id 
                 AND week_start >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
                 ORDER BY week_start ASC";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute(['user_id' => $targetUserId]);
        $points = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Calculate average points
        $avgQuery = "SELECT AVG(points) as average_points
                    FROM school_points 
                    WHERE user_id = :user_id 
                    AND week_start >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)";
                    
        $stmt = $this->db->prepare($avgQuery);
        $stmt->execute(['user_id' => $targetUserId]);
        $average = $stmt->fetch(\PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode([
            "weekly_points" => $points,
            "average_points" => floatval($average['average_points'])
        ]);
    }
}