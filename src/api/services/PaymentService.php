<?php
namespace App\Services;

use GuzzleHttp\Client;

class PaymentService {
    private $client;
    private $clientId;
    private $clientSecret;

    public function __construct() {
        $this->clientId = $_ENV['CASHAPP_CLIENT_ID'];
        $this->clientSecret = $_ENV['CASHAPP_CLIENT_SECRET'];
        
        $this->client = new Client([
            'base_uri' => 'https://api.cash.app/v1/',
            'auth' => [$this->clientId, $this->clientSecret]
        ]);
    }

    public function processPayout($userId, $amount, $cashappUsername) {
        try {
            $response = $this->client->post('payments', [
                'json' => [
                    'amount' => $amount,
                    'currency' => 'USD',
                    'recipient' => $cashappUsername,
                    'note' => 'Bi-weekly allowance payout'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            return [
                'success' => true,
                'transaction_id' => $data['id']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function validateCashAppAccount($username) {
        try {
            $response = $this->client->get("users/$username/validate");
            $data = json_decode($response->getBody(), true);
            return [
                'success' => true,
                'valid' => $data['valid']
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function calculateBiWeeklyPayout($userId) {
        // Get tasks completed in the last 2 weeks
        $db = new \PDO(/* database connection */);
        
        $query = "SELECT COUNT(*) as completed_tasks 
                 FROM tasks 
                 WHERE user_id = :user_id 
                 AND status = 'verified' 
                 AND verified_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)";
                 
        $stmt = $db->prepare($query);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Calculate base amount ($5 per task)
        $baseAmount = $result['completed_tasks'] * 5;
        
        // Check for deductions
        $deductionsQuery = "SELECT SUM(amount) as total_deductions 
                          FROM transactions 
                          WHERE user_id = :user_id 
                          AND type = 'deduction' 
                          AND created_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)";
                          
        $stmt = $db->prepare($deductionsQuery);
        $stmt->execute(['user_id' => $userId]);
        $deductions = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        // Calculate final amount
        $finalAmount = $baseAmount - ($deductions['total_deductions'] ?? 0);
        
        return max(0, $finalAmount); // Ensure we don't return negative amount
    }
}
