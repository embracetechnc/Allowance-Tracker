<?php
namespace App\Controllers;

use App\Config\Config;
use Firebase\JWT\JWT;

class CashAppController {
    private $db;
    private $clientId;
    private $clientSecret;
    private $redirectUri;
    private $scope;

    public function __construct($db) {
        $this->db = $db;
        $this->clientId = Config::get('cashapp.client_id');
        $this->clientSecret = Config::get('cashapp.client_secret');
        $this->redirectUri = Config::get('cashapp.redirect_uri');
        $this->scope = 'PAYMENTS_TRANSFER_READ PAYMENTS_TRANSFER_WRITE USER_PROFILE_READ';
    }

    public function getAuthUrl() {
        try {
            // Generate and store state parameter for CSRF protection
            $state = bin2hex(random_bytes(16));
            $this->storeState($state);

            // Build authorization URL
            $authUrl = 'https://connect.squareupsandbox.com/oauth2/authorize?' . http_build_query([
                'client_id' => $this->clientId,
                'response_type' => 'code',
                'scope' => $this->scope,
                'redirect_uri' => $this->redirectUri,
                'state' => $state
            ]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'auth_url' => $authUrl
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to generate authorization URL',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function handleCallback() {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $code = $input['code'] ?? null;
            $state = $input['state'] ?? null;

            // Verify state parameter
            if (!$this->verifyState($state)) {
                throw new \Exception('Invalid state parameter');
            }

            // Exchange code for access token
            $response = $this->exchangeCodeForToken($code);

            if (!isset($response['access_token'])) {
                throw new \Exception('Failed to obtain access token');
            }

            // Get user info from Cash App
            $userInfo = $this->getCashAppUserInfo($response['access_token']);

            // Store Cash App credentials
            $this->storeCashAppCredentials(
                $userInfo['cashtag'],
                $response['access_token'],
                $response['refresh_token'],
                $response['expires_in']
            );

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Cash App account linked successfully'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to link Cash App account',
                'error' => $e->getMessage()
            ]);
        }
    }

    private function exchangeCodeForToken($code) {
        $ch = curl_init('https://connect.squareupsandbox.com/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri
        ]));

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('Failed to exchange code: ' . $error);
        }

        return json_decode($response, true);
    }

    private function getCashAppUserInfo($accessToken) {
        $ch = curl_init('https://api.squareupsandbox.com/v2/cash/me');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('Failed to get user info: ' . $error);
        }

        return json_decode($response, true);
    }

    private function storeState($state) {
        $stmt = $this->db->prepare("
            INSERT INTO oauth_states (state, created_at)
            VALUES (?, NOW())
        ");
        $stmt->execute([$state]);
    }

    private function verifyState($state) {
        // Remove expired states (older than 10 minutes)
        $stmt = $this->db->prepare("
            DELETE FROM oauth_states
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)
        ");
        $stmt->execute();

        // Verify state
        $stmt = $this->db->prepare("
            SELECT 1 FROM oauth_states
            WHERE state = ?
        ");
        $stmt->execute([$state]);
        
        return $stmt->rowCount() > 0;
    }

    private function storeCashAppCredentials($cashtag, $accessToken, $refreshToken, $expiresIn) {
        $user = $this->getUserFromToken();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        $stmt = $this->db->prepare("
            INSERT INTO cashapp_credentials (
                user_id,
                cashtag,
                access_token,
                refresh_token,
                expires_at,
                created_at,
                updated_at
            ) VALUES (
                ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND), NOW(), NOW()
            ) ON DUPLICATE KEY UPDATE
                cashtag = VALUES(cashtag),
                access_token = VALUES(access_token),
                refresh_token = VALUES(refresh_token),
                expires_at = VALUES(expires_at),
                updated_at = NOW()
        ");

        $stmt->execute([
            $user->id,
            $cashtag,
            $accessToken,
            $refreshToken,
            $expiresIn
        ]);
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

    public function refreshToken() {
        try {
            $user = $this->getUserFromToken();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            // Get current credentials
            $stmt = $this->db->prepare("
                SELECT refresh_token
                FROM cashapp_credentials
                WHERE user_id = ?
            ");
            $stmt->execute([$user->id]);
            $credentials = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$credentials) {
                throw new \Exception('No Cash App credentials found');
            }

            // Exchange refresh token for new access token
            $ch = curl_init('https://connect.squareupsandbox.com/oauth2/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $credentials['refresh_token'],
                'grant_type' => 'refresh_token'
            ]));

            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                throw new \Exception('Failed to refresh token: ' . $error);
            }

            $tokenData = json_decode($response, true);
            if (!isset($tokenData['access_token'])) {
                throw new \Exception('Invalid response from Cash App');
            }

            // Update stored credentials
            $this->storeCashAppCredentials(
                $tokenData['cashtag'],
                $tokenData['access_token'],
                $tokenData['refresh_token'],
                $tokenData['expires_in']
            );

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Token refreshed successfully'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to refresh token',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function unlinkAccount() {
        try {
            $user = $this->getUserFromToken();
            if (!$user) {
                throw new \Exception('User not authenticated');
            }

            $stmt = $this->db->prepare("
                DELETE FROM cashapp_credentials
                WHERE user_id = ?
            ");
            $stmt->execute([$user->id]);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Cash App account unlinked successfully'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to unlink Cash App account',
                'error' => $e->getMessage()
            ]);
        }
    }
}
