<?php
require __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require __DIR__ . '/config.php';

// Initialize database connection
try {
    $db = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['name']}",
        $config['database']['user'],
        $config['database']['pass']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database successfully!\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Initialize notification service
require_once __DIR__ . '/src/api/services/NotificationService.php';
$notificationService = new App\Services\NotificationService($db);

echo "\n=== Testing Task Completion Notification ===\n";

// Simulate Hannah completing a room cleaning task
$taskData = [
    'user_id' => 3, // Hannah's ID
    'task_type' => 'room_cleaning',
    'completed_at' => date('Y-m-d H:i:s'),
    'status' => 'completed'
];

// Insert task into database
try {
    $stmt = $db->prepare("
        INSERT INTO tasks (user_id, task_type, status, completed_at, created_at)
        VALUES (:user_id, :task_type, :status, :completed_at, NOW())
    ");
    $stmt->execute($taskData);
    $taskId = $db->lastInsertId();
    $taskData['id'] = $taskId;
    
    echo "Task created successfully (ID: {$taskId})\n";
    
    // Send notification to parents
    if ($notificationService->notifyTaskCompletion($taskData)) {
        echo "Task completion notification sent successfully!\n";
    } else {
        echo "Failed to send task completion notification.\n";
    }
} catch (Exception $e) {
    echo "Error creating task: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Task Verification Notification ===\n";

// Simulate William (parent) verifying the task
$verificationData = [
    'id' => $taskId,
    'user_id' => 3, // Hannah's ID
    'task_type' => 'room_cleaning',
    'verified_by' => 1, // William's ID
    'verified_at' => date('Y-m-d H:i:s'),
    'approved' => true
];

// Update task in database
try {
    $stmt = $db->prepare("
        UPDATE tasks 
        SET status = :status,
            verified_by = :verified_by,
            verified_at = :verified_at
        WHERE id = :id
    ");
    
    $stmt->execute([
        'status' => 'verified',
        'verified_by' => $verificationData['verified_by'],
        'verified_at' => $verificationData['verified_at'],
        'id' => $verificationData['id']
    ]);
    
    echo "Task verification updated successfully\n";
    
    // Send notification to child
    if ($notificationService->notifyTaskVerification($verificationData)) {
        echo "Task verification notification sent successfully!\n";
    } else {
        echo "Failed to send task verification notification.\n";
    }
} catch (Exception $e) {
    echo "Error updating task: " . $e->getMessage() . "\n";
}

echo "\nNotification workflow test completed!\n";
