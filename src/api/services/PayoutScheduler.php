<?php
namespace App\Services;

use Exception;
use PDO;
use DateTime;
use DateInterval;

class PayoutScheduler {
    private $db;
    private $cashAppService;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->cashAppService = new CashAppService($db);
    }

    public function schedulePayouts() {
        try {
            $this->db->beginTransaction();

            // Get all active children
            $children = $this->getActiveChildren();

            foreach ($children as $child) {
                // Calculate next payout date (bi-weekly on Saturday)
                $nextPayoutDate = $this->calculateNextPayoutDate();
                
                // Calculate allowance amount
                $amount = $this->calculateAllowance($child['id']);

                // Schedule the payment
                if ($amount > 0) {
                    $this->cashAppService->schedulePayment(
                        $child['id'],
                        $amount,
                        $nextPayoutDate->format('Y-m-d H:i:s')
                    );

                    // Log the scheduled payment
                    $this->logScheduledPayment($child['id'], $amount, $nextPayoutDate);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error scheduling payouts: " . $e->getMessage());
            throw $e;
        }
    }

    private function getActiveChildren() {
        $stmt = $this->db->prepare("
            SELECT id, first_name, email
            FROM users
            WHERE role = 'child'
            AND status = 'active'
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function calculateNextPayoutDate() {
        $now = new DateTime();
        $nextSaturday = new DateTime('next saturday');

        // If today is Saturday, use today
        if ($now->format('l') === 'Saturday') {
            $nextSaturday = $now;
        }

        // Set time to 9:00 AM
        $nextSaturday->setTime(9, 0, 0);

        return $nextSaturday;
    }

    private function calculateAllowance($userId) {
        try {
            // Get completed tasks since last payout
            $stmt = $this->db->prepare("
                SELECT 
                    t.task_type,
                    COUNT(*) as completed_count
                FROM tasks t
                LEFT JOIN payment_history ph ON ph.user_id = t.user_id
                WHERE t.user_id = ?
                AND t.status = 'verified'
                AND t.verified_at > COALESCE(
                    (SELECT MAX(payment_date) 
                     FROM payment_history 
                     WHERE user_id = t.user_id),
                    DATE_SUB(NOW(), INTERVAL 2 WEEK)
                )
                GROUP BY t.task_type
            ");
            $stmt->execute([$userId]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate base amount from tasks
            $amount = 0;
            foreach ($tasks as $task) {
                switch ($task['task_type']) {
                    case 'room_cleaning':
                    case 'bathroom_cleaning':
                    case 'kitchen_cleaning':
                    case 'laundry':
                        $amount += 5 * $task['completed_count'];
                        break;
                }
            }

            // Check for deductions (school points)
            $deductions = $this->calculateDeductions($userId);
            $amount = max(0, $amount - $deductions);

            // Cap at maximum weekly allowance ($20)
            return min($amount, 20);
        } catch (Exception $e) {
            error_log("Error calculating allowance: " . $e->getMessage());
            throw $e;
        }
    }

    private function calculateDeductions($userId) {
        try {
            // Get school points since last payout
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as point_count
                FROM school_points
                WHERE user_id = ?
                AND created_at > COALESCE(
                    (SELECT MAX(payment_date) 
                     FROM payment_history 
                     WHERE user_id = ?),
                    DATE_SUB(NOW(), INTERVAL 2 WEEK)
                )
            ");
            $stmt->execute([$userId, $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Deduct $5 if more than 3 points
            return $result['point_count'] > 3 ? 5 : 0;
        } catch (Exception $e) {
            error_log("Error calculating deductions: " . $e->getMessage());
            throw $e;
        }
    }

    private function logScheduledPayment($userId, $amount, DateTime $payoutDate) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO scheduled_payouts (
                    user_id,
                    amount,
                    scheduled_date,
                    status,
                    created_at
                ) VALUES (
                    ?, ?, ?, 'pending', NOW()
                )
            ");
            $stmt->execute([
                $userId,
                $amount,
                $payoutDate->format('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            error_log("Error logging scheduled payment: " . $e->getMessage());
            throw $e;
        }
    }

    public function processScheduledPayouts() {
        try {
            $this->db->beginTransaction();

            // Get all pending payouts scheduled for today or earlier
            $stmt = $this->db->prepare("
                SELECT sp.*, u.email, u.first_name
                FROM scheduled_payouts sp
                JOIN users u ON sp.user_id = u.id
                WHERE sp.status = 'pending'
                AND sp.scheduled_date <= NOW()
            ");
            $stmt->execute();
            $payouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [];
            foreach ($payouts as $payout) {
                try {
                    // Process the payment
                    $this->cashAppService->processPayment(
                        $payout['user_id'],
                        $payout['amount'],
                        'Bi-weekly allowance payment'
                    );

                    // Update payout status
                    $this->updatePayoutStatus($payout['id'], 'completed');

                    $results[] = [
                        'success' => true,
                        'user' => $payout['first_name'],
                        'amount' => $payout['amount']
                    ];
                } catch (Exception $e) {
                    // Log failure but continue processing other payouts
                    $this->updatePayoutStatus($payout['id'], 'failed', $e->getMessage());
                    $results[] = [
                        'success' => false,
                        'user' => $payout['first_name'],
                        'amount' => $payout['amount'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            $this->db->commit();
            return $results;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error processing scheduled payouts: " . $e->getMessage());
            throw $e;
        }
    }

    private function updatePayoutStatus($payoutId, $status, $errorMessage = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE scheduled_payouts
                SET status = ?,
                    error_message = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$status, $errorMessage, $payoutId]);
        } catch (Exception $e) {
            error_log("Error updating payout status: " . $e->getMessage());
            throw $e;
        }
    }
}
