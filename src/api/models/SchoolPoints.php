<?php
namespace App\Models;

class SchoolPoints {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function addPoints($userId, $points, $reason, $assignedBy, $categoryId = null) {
        try {
            $this->db->beginTransaction();

            // Get current week number and year
            $weekNumber = date('W');
            $year = date('Y');

            // Add points
            $stmt = $this->db->prepare("
                INSERT INTO school_points (
                    user_id,
                    points,
                    reason,
                    assigned_by,
                    week_number,
                    year,
                    created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, NOW()
                )
            ");

            $stmt->execute([
                $userId,
                $points,
                $reason,
                $assignedBy,
                $weekNumber,
                $year
            ]);

            // Update or create weekly summary
            $this->updateWeeklySummary($userId, $weekNumber, $year);

            // Record in history
            $this->recordHistory($userId, 'added', $points, $reason, $assignedBy);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error adding points: " . $e->getMessage());
            throw $e;
        }
    }

    public function removePoints($userId, $points, $reason, $performedBy) {
        try {
            $this->db->beginTransaction();

            // Get current week number and year
            $weekNumber = date('W');
            $year = date('Y');

            // Remove points by adding a negative entry
            $stmt = $this->db->prepare("
                INSERT INTO school_points (
                    user_id,
                    points,
                    reason,
                    assigned_by,
                    week_number,
                    year,
                    created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, NOW()
                )
            ");

            $stmt->execute([
                $userId,
                -$points,
                $reason,
                $performedBy,
                $weekNumber,
                $year
            ]);

            // Update weekly summary
            $this->updateWeeklySummary($userId, $weekNumber, $year);

            // Record in history
            $this->recordHistory($userId, 'removed', $points, $reason, $performedBy);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error removing points: " . $e->getMessage());
            throw $e;
        }
    }

    private function updateWeeklySummary($userId, $weekNumber, $year) {
        // Calculate total points for the week
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(points), 0) as total_points
            FROM school_points
            WHERE user_id = ?
            AND week_number = ?
            AND year = ?
        ");
        $stmt->execute([$userId, $weekNumber, $year]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $totalPoints = $result['total_points'];

        // Calculate allowance deduction ($5 if more than 3 points)
        $deduction = $totalPoints > 3 ? 5.00 : 0.00;

        // Update or insert summary
        $stmt = $this->db->prepare("
            INSERT INTO point_summaries (
                user_id,
                week_number,
                year,
                total_points,
                allowance_deduction,
                created_at,
                updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, NOW(), NOW()
            ) ON DUPLICATE KEY UPDATE
                total_points = VALUES(total_points),
                allowance_deduction = VALUES(allowance_deduction),
                updated_at = NOW()
        ");

        $stmt->execute([
            $userId,
            $weekNumber,
            $year,
            $totalPoints,
            $deduction
        ]);
    }

    private function recordHistory($userId, $action, $points, $reason, $performedBy) {
        $stmt = $this->db->prepare("
            INSERT INTO point_history (
                user_id,
                action,
                points,
                reason,
                performed_by,
                created_at
            ) VALUES (
                ?, ?, ?, ?, ?, NOW()
            )
        ");

        $stmt->execute([
            $userId,
            $action,
            $points,
            $reason,
            $performedBy
        ]);
    }

    public function getWeeklyPoints($userId, $weekNumber = null, $year = null) {
        if ($weekNumber === null) {
            $weekNumber = date('W');
        }
        if ($year === null) {
            $year = date('Y');
        }

        $stmt = $this->db->prepare("
            SELECT 
                sp.*,
                pc.name as category_name,
                u.first_name as assigned_by_name
            FROM school_points sp
            LEFT JOIN point_categories pc ON pc.id = sp.category_id
            LEFT JOIN users u ON u.id = sp.assigned_by
            WHERE sp.user_id = ?
            AND sp.week_number = ?
            AND sp.year = ?
            ORDER BY sp.created_at DESC
        ");

        $stmt->execute([$userId, $weekNumber, $year]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getWeeklySummary($userId, $weekNumber = null, $year = null) {
        if ($weekNumber === null) {
            $weekNumber = date('W');
        }
        if ($year === null) {
            $year = date('Y');
        }

        $stmt = $this->db->prepare("
            SELECT *
            FROM point_summaries
            WHERE user_id = ?
            AND week_number = ?
            AND year = ?
        ");

        $stmt->execute([$userId, $weekNumber, $year]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getPointHistory($userId, $limit = 50) {
        $stmt = $this->db->prepare("
            SELECT 
                ph.*,
                u.first_name as performed_by_name
            FROM point_history ph
            LEFT JOIN users u ON u.id = ph.performed_by
            WHERE ph.user_id = ?
            ORDER BY ph.created_at DESC
            LIMIT ?
        ");

        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getCategories() {
        $stmt = $this->db->prepare("
            SELECT *
            FROM point_categories
            ORDER BY name ASC
        ");

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function resetWeeklyPoints() {
        try {
            $this->db->beginTransaction();

            // Get all users with points this week
            $weekNumber = date('W');
            $year = date('Y');

            $stmt = $this->db->prepare("
                SELECT DISTINCT user_id
                FROM school_points
                WHERE week_number = ?
                AND year = ?
            ");
            $stmt->execute([$weekNumber, $year]);
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($users as $user) {
                // Record the reset in history
                $this->recordHistory(
                    $user['user_id'],
                    'reset',
                    0,
                    'Weekly points reset',
                    0 // System action
                );
            }

            // Mark summaries as processed
            $stmt = $this->db->prepare("
                UPDATE point_summaries
                SET processed = true,
                    updated_at = NOW()
                WHERE week_number = ?
                AND year = ?
                AND processed = false
            ");
            $stmt->execute([$weekNumber, $year]);

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error resetting points: " . $e->getMessage());
            throw $e;
        }
    }
}
