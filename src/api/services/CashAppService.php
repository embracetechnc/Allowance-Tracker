<?php
namespace App\Services;

use App\Config\Config;
use Exception;

class CashAppService {
    private $db;
    private $clientId;
    private $clientSecret;

    public function __construct($db) {
        $this->db = $db;
        $this->clientId = Config::get('cashapp.client_id');
        $this->clientSecret = Config::get('cashapp.client_secret');
    }

    public function processPayment($userId, $amount, $note = '') {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Get user's Cash App credentials
            $credentials = $this->getCashAppCredentials($userId);
            if (!$credentials) {
                throw new Exception('Cash App account not linked');
            }

            // Check if token needs refresh
            if ($this->tokenNeedsRefresh($credentials['expires_at'])) {
                $credentials = $this->refreshToken($userId, $credentials['refresh_token']);
            }

            // Create payment in Cash App
            $payment = $this->createCashAppPayment($credentials['access_token'], $credentials['cashtag'], $amount, $note);

            // Record payment in our database
            $this->recordPayment($userId, $amount, $payment['id']);

            $this->db->commit();
            return $payment;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->recordFailedPayment($userId, $amount, $e->getMessage());
            throw $e;
        }
    }

    private function getCashAppCredentials($userId) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM cashapp_credentials
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function tokenNeedsRefresh($expiresAt) {
        // Refresh if token expires in less than 1 hour
        return strtotime($expiresAt) - time() < 3600;
    }

    private function refreshToken($userId, $refreshToken) {
        $ch = curl_init('https://connect.squareupsandbox.com/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ]));

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Failed to refresh token: ' . $error);
        }

        $tokenData = json_decode($response, true);
        if (!isset($tokenData['access_token'])) {
            throw new Exception('Invalid response from Cash App');
        }

        // Update stored credentials
        $stmt = $this->db->prepare("
            UPDATE cashapp_credentials
            SET access_token = ?,
                refresh_token = ?,
                expires_at = DATE_ADD(NOW(), INTERVAL ? SECOND),
                updated_at = NOW()
            WHERE user_id = ?
        ");

        $stmt->execute([
            $tokenData['access_token'],
            $tokenData['refresh_token'],
            $tokenData['expires_in'],
            $userId
        ]);

        return $this->getCashAppCredentials($userId);
    }

    private function createCashAppPayment($accessToken, $cashtag, $amount, $note) {
        $ch = curl_init('https://api.squareupsandbox.com/v2/cash/payments');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        $payload = [
            'payment' => [
                'amount' => [
                    'amount' => $amount * 100, // Convert to cents
                    'currency' => 'USD'
                ],
                'recipient' => [
                    'cashtag' => $cashtag
                ],
                'note' => $note ?: 'Allowance payment'
            ]
        ];

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception('Failed to create payment: ' . $error);
        }

        $result = json_decode($response, true);
        if (!isset($result['payment']['id'])) {
            throw new Exception('Invalid response from Cash App');
        }

        return $result['payment'];
    }

    private function recordPayment($userId, $amount, $transactionId) {
        $stmt = $this->db->prepare("
            INSERT INTO payment_history (
                user_id,
                amount,
                status,
                payment_date,
                transaction_id,
                created_at,
                updated_at
            ) VALUES (
                ?, ?, 'completed', NOW(), ?, NOW(), NOW()
            )
        ");

        $stmt->execute([$userId, $amount, $transactionId]);
    }

    private function recordFailedPayment($userId, $amount, $errorMessage) {
        $stmt = $this->db->prepare("
            INSERT INTO payment_history (
                user_id,
                amount,
                status,
                payment_date,
                error_message,
                created_at,
                updated_at
            ) VALUES (
                ?, ?, 'failed', NOW(), ?, NOW(), NOW()
            )
        ");

        $stmt->execute([$userId, $amount, $errorMessage]);
    }

    public function getPaymentHistory($userId, $limit = 10) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM payment_history
            WHERE user_id = ?
            ORDER BY payment_date DESC
            LIMIT ?
        ");

        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPendingPayments() {
        $stmt = $this->db->prepare("
            SELECT ph.*, u.email, u.first_name
            FROM payment_history ph
            JOIN users u ON ph.user_id = u.id
            WHERE ph.status = 'pending'
            ORDER BY ph.payment_date ASC
        ");

        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function schedulePayment($userId, $amount, $paymentDate) {
        $stmt = $this->db->prepare("
            INSERT INTO payment_history (
                user_id,
                amount,
                status,
                payment_date,
                created_at,
                updated_at
            ) VALUES (
                ?, ?, 'pending', ?, NOW(), NOW()
            )
        ");

        $stmt->execute([$userId, $amount, $paymentDate]);
        return $this->db->lastInsertId();
    }

    public function processScheduledPayments() {
        $pendingPayments = $this->getPendingPayments();
        $results = [];

        foreach ($pendingPayments as $payment) {
            try {
                if (strtotime($payment['payment_date']) <= time()) {
                    $this->processPayment(
                        $payment['user_id'],
                        $payment['amount'],
                        'Scheduled allowance payment'
                    );
                    $results[] = [
                        'success' => true,
                        'user' => $payment['first_name'],
                        'amount' => $payment['amount']
                    ];
                }
            } catch (Exception $e) {
                $results[] = [
                    'success' => false,
                    'user' => $payment['first_name'],
                    'amount' => $payment['amount'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }
}
