<?php
namespace App\Controllers;

use App\Services\PaymentService;
use App\Models\User;

class PaymentController {
    private $paymentService;
    private $db;

    public function __construct($db) {
        $this->paymentService = new PaymentService();
        $this->db = $db;
    }

    public function processBiWeeklyPayouts() {
        // Get all child users
        $userModel = new User($this->db);
        $query = "SELECT * FROM users WHERE role = 'child'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $children = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $results = [];

        foreach ($children as $child) {
            // Skip if no Cash App username
            if (!$child['cashapp_username']) {
                $results[] = [
                    'user_id' => $child['id'],
                    'success' => false,
                    'error' => 'No Cash App username configured'
                ];
                continue;
            }

            // Calculate payout amount
            $amount = $this->paymentService->calculateBiWeeklyPayout($child['id']);

            // Skip if no payout due
            if ($amount <= 0) {
                $results[] = [
                    'user_id' => $child['id'],
                    'success' => true,
                    'message' => 'No payout due'
                ];
                continue;
            }

            // Process payment
            $result = $this->paymentService->processPayout(
                $child['id'],
                $amount,
                $child['cashapp_username']
            );

            if ($result['success']) {
                // Record transaction
                $this->recordTransaction($child['id'], $amount, $result['transaction_id']);
                
                $results[] = [
                    'user_id' => $child['id'],
                    'success' => true,
                    'amount' => $amount,
                    'transaction_id' => $result['transaction_id']
                ];
            } else {
                $results[] = [
                    'user_id' => $child['id'],
                    'success' => false,
                    'error' => $result['error']
                ];
            }
        }

        http_response_code(200);
        echo json_encode(['results' => $results]);
    }

    public function validateCashApp() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->username)) {
            http_response_code(400);
            echo json_encode(["message" => "Username is required"]);
            return;
        }

        $result = $this->paymentService->validateCashAppAccount($data->username);
        
        if ($result['success']) {
            http_response_code(200);
            echo json_encode([
                "valid" => $result['valid']
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "message" => "Error validating Cash App account",
                "error" => $result['error']
            ]);
        }
    }

    private function recordTransaction($userId, $amount, $transactionId) {
        $query = "INSERT INTO transactions (user_id, amount, type, reason, external_id) 
                 VALUES (:user_id, :amount, 'credit', 'Bi-weekly allowance payout', :transaction_id)";
                 
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'user_id' => $userId,
            'amount' => $amount,
            'transaction_id' => $transactionId
        ]);
    }
}
