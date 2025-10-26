<?php
require __DIR__ . '/../vendor/autoload.php';

// Load configuration
$config = require __DIR__ . '/../config.php';

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

// Initialize scheduler
$scheduler = new App\Services\PayoutScheduler($db);

try {
    // Schedule new payouts (runs on Friday)
    if (date('l') === 'Friday') {
        echo "Scheduling payouts for tomorrow...\n";
        $scheduler->schedulePayouts();
        echo "Payouts scheduled successfully!\n";
    }

    // Process pending payouts (runs daily)
    echo "\nProcessing pending payouts...\n";
    $results = $scheduler->processScheduledPayouts();

    // Output results
    foreach ($results as $result) {
        if ($result['success']) {
            echo "✓ Processed payment of \${$result['amount']} to {$result['user']}\n";
        } else {
            echo "✗ Failed to process payment of \${$result['amount']} to {$result['user']}: {$result['error']}\n";
        }
    }

    echo "\nPayout processing completed!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
