<?php
// Enable error reporting in development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Load configuration
$config = require __DIR__ . '/../config.php';

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Handle CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Parse the request URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rawurldecode($uri);

// API routes
if (strpos($uri, '/api/') === 0) {
    // Remove '/api' from the URI
    $uri = substr($uri, 4);
    
    // Get database connection
    $db = new \App\Config\Database();
    $conn = $db->connect();
    
    // Route the request
    switch ($uri) {
        // Auth routes
        case '/auth/login':
            $auth = new \App\Controllers\AuthController($conn);
            $auth->login();
            break;

        case '/auth/register':
            $auth = new \App\Controllers\AuthController($conn);
            $auth->register();
            break;

        // Task routes
        case '/tasks':
            $tasks = new \App\Controllers\TaskController($conn);
            $tasks->getTasks();
            break;

        case '/tasks/complete':
            $tasks = new \App\Controllers\TaskController($conn);
            $tasks->completeTask();
            break;

        case '/tasks/pending':
            $tasks = new \App\Controllers\TaskController($conn);
            $tasks->getPendingVerifications();
            break;

        case '/tasks/verify':
            $tasks = new \App\Controllers\TaskController($conn);
            $tasks->verifyTask();
            break;

        // Notification routes
        case '/notifications':
            $tasks = new \App\Controllers\TaskController($conn);
            $tasks->getNotifications();
            break;

        case '/notifications/read':
            $tasks = new \App\Controllers\TaskController($conn);
            $tasks->markNotificationRead();
            break;

        // Report routes
        case '/reports/earnings':
            $reports = new \App\Controllers\ReportController($conn);
            $reports->getEarnings();
            break;

        case '/reports/tasks':
            $reports = new \App\Controllers\ReportController($conn);
            $reports->getTaskStats();
            break;

        case '/reports/school-points':
            $reports = new \App\Controllers\ReportController($conn);
            $reports->getSchoolPoints();
            break;

        // Bible routes
        case '/bible/daily':
            $bible = new \App\Controllers\BibleController($conn);
            $bible->getDailyVerse();
            break;

        case '/bible/money':
            $bible = new \App\Controllers\BibleController($conn);
            $bible->getMoneyVerse();
            break;

        // Cash App routes
        case '/cashapp/auth':
            $cashapp = new \App\Controllers\CashAppController($conn);
            $cashapp->getAuthUrl();
            break;

        case '/cashapp/callback':
            $cashapp = new \App\Controllers\CashAppController($conn);
            $cashapp->handleCallback();
            break;

        case '/cashapp/refresh':
            $cashapp = new \App\Controllers\CashAppController($conn);
            $cashapp->refreshToken();
            break;

        case '/cashapp/unlink':
            $cashapp = new \App\Controllers\CashAppController($conn);
            $cashapp->unlinkAccount();
            break;

        // School Points routes
        case '/points/add':
            $points = new \App\Controllers\SchoolPointsController($conn);
            $points->addPoints();
            break;

        case '/points/remove':
            $points = new \App\Controllers\SchoolPointsController($conn);
            $points->removePoints();
            break;

        case '/points/weekly':
            $points = new \App\Controllers\SchoolPointsController($conn);
            $points->getWeeklyReport();
            break;

        case '/points/history':
            $points = new \App\Controllers\SchoolPointsController($conn);
            $points->getPointHistory();
            break;

        case '/points/categories':
            $points = new \App\Controllers\SchoolPointsController($conn);
            $points->getCategories();
            break;

        case '/points/reset':
            $points = new \App\Controllers\SchoolPointsController($conn);
            $points->processWeeklyReset();
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
            break;
    }
    exit();
}

// Development mode - proxy to Vite dev server
if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'development') {
    $viteDevServer = 'http://localhost:3000';
    header("Location: $viteDevServer" . $_SERVER['REQUEST_URI']);
    exit();
}

// Production mode - serve the built files
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allowance Tracker</title>
    <script type="module" src="http://localhost:3000/@vite/client"></script>
    <script type="module" src="http://localhost:3000/src/main.js"></script>
</head>
<body>
    <div id="app"></div>
</body>
</html>