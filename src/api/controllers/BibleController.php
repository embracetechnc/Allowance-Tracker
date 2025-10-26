<?php
namespace App\Controllers;

use App\Services\BibleService;

class BibleController {
    private $bibleService;

    public function __construct($db) {
        $this->bibleService = new BibleService($db);
    }

    public function getDailyVerse() {
        try {
            $verse = $this->bibleService->getDailyVerse();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $verse
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch daily verse',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function getMoneyVerse() {
        try {
            $verse = $this->bibleService->getRandomVerseAboutMoney();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $verse
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch money verse',
                'error' => $e->getMessage()
            ]);
        }
    }
}