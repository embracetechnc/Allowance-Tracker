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

// Initialize BibleService
require_once __DIR__ . '/src/api/services/BibleService.php';
$bibleService = new App\Services\BibleService($db);

echo "\n=== Testing Daily Verse ===\n";
try {
    $verse = $bibleService->getDailyVerse();
    echo "Verse: {$verse['verse']}\n";
    echo "Reference: {$verse['reference']}\n";
    echo "Fetched at: {$verse['fetched_at']}\n";
} catch (Exception $e) {
    echo "Error getting daily verse: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Random Money Verse ===\n";
try {
    $verse = $bibleService->getRandomVerseAboutMoney();
    echo "Verse: {$verse['verse']}\n";
    echo "Reference: {$verse['reference']}\n";
    echo "Fetched at: {$verse['fetched_at']}\n";
} catch (Exception $e) {
    echo "Error getting money verse: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Cache ===\n";
try {
    echo "Fetching verse again (should use cache)...\n";
    $verse = $bibleService->getDailyVerse();
    echo "Verse: {$verse['verse']}\n";
    echo "Reference: {$verse['reference']}\n";
    echo "Fetched at: {$verse['fetched_at']}\n";
} catch (Exception $e) {
    echo "Error testing cache: " . $e->getMessage() . "\n";
}
