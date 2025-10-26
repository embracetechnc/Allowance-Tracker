<?php
namespace App\Controllers;

use App\Services\SchoolPointsService;

class SchoolPointsController {
    private $schoolPointsService;
    private $authController;

    public function __construct($db) {
        $this->schoolPointsService = new SchoolPointsService($db);
        $this->authController = new AuthController($db);
    }

    private function getUserFromToken() {
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            return null;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);
        return $this->authController->validateToken($token);
    }

    public function addPoints() {
        try {
            // Verify user is authenticated and is a parent
            $user = $this->getUserFromToken();
            if (!$user || $user->role !== 'parent') {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized. Only parents can add points.'
                ]);
                return;
            }

            // Get request data
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['user_id']) || !isset($data['points']) || !isset($data['reason'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required fields'
                ]);
                return;
            }

            // Add points
            $result = $this->schoolPointsService->addPoints(
                $data['user_id'],
                $data['points'],
                $data['reason'],
                $user->id,
                $data['category_id'] ?? null
            );

            http_response_code(200);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function removePoints() {
        try {
            // Verify user is authenticated and is a parent
            $user = $this->getUserFromToken();
            if (!$user || $user->role !== 'parent') {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized. Only parents can remove points.'
                ]);
                return;
            }

            // Get request data
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['user_id']) || !isset($data['points']) || !isset($data['reason'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Missing required fields'
                ]);
                return;
            }

            // Remove points
            $result = $this->schoolPointsService->removePoints(
                $data['user_id'],
                $data['points'],
                $data['reason'],
                $user->id
            );

            http_response_code(200);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getWeeklyReport() {
        try {
            // Verify user is authenticated
            $user = $this->getUserFromToken();
            if (!$user) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
                return;
            }

            // Get query parameters
            $userId = $_GET['user_id'] ?? $user->id;
            $weekNumber = $_GET['week'] ?? null;
            $year = $_GET['year'] ?? null;

            // If requesting another user's report, verify parent role
            if ($userId !== $user->id && $user->role !== 'parent') {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Forbidden. Can only view own points unless parent.'
                ]);
                return;
            }

            // Get weekly report
            $result = $this->schoolPointsService->getWeeklyReport($userId, $weekNumber, $year);

            http_response_code(200);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getPointHistory() {
        try {
            // Verify user is authenticated
            $user = $this->getUserFromToken();
            if (!$user) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
                return;
            }

            // Get query parameters
            $userId = $_GET['user_id'] ?? $user->id;
            $limit = $_GET['limit'] ?? 50;

            // If requesting another user's history, verify parent role
            if ($userId !== $user->id && $user->role !== 'parent') {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Forbidden. Can only view own points unless parent.'
                ]);
                return;
            }

            // Get point history
            $result = $this->schoolPointsService->getPointHistory($userId, $limit);

            http_response_code(200);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getCategories() {
        try {
            // Verify user is authenticated
            $user = $this->getUserFromToken();
            if (!$user) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
                return;
            }

            // Get categories
            $result = $this->schoolPointsService->getCategories();

            http_response_code(200);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function processWeeklyReset() {
        try {
            // This endpoint should only be called by a cron job with a secret key
            $headers = getallheaders();
            if (!isset($headers['X-Cron-Key']) || $headers['X-Cron-Key'] !== $_ENV['CRON_SECRET_KEY']) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Unauthorized'
                ]);
                return;
            }

            // Process weekly reset
            $result = $this->schoolPointsService->processWeeklyReset();

            http_response_code(200);
            echo json_encode($result);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
