<?php
namespace App\Services;

use Exception;
use PDO;

class BibleService {
    private $db;
    private $cache_duration = 86400; // 24 hours in seconds

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->initializeCache();
    }

    private function initializeCache() {
        try {
            // Create cache table if it doesn't exist
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS bible_verse_cache (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    verse_text TEXT NOT NULL,
                    reference VARCHAR(255) NOT NULL,
                    fetched_at DATETIME NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } catch (Exception $e) {
            error_log("Failed to initialize bible verse cache: " . $e->getMessage());
        }
    }

    public function getDailyVerse() {
        try {
            // Check cache first
            $cachedVerse = $this->getCachedVerse();
            if ($cachedVerse) {
                return $cachedVerse;
            }

            // Fetch new verse if cache is empty or expired
            $verse = $this->fetchVerseFromAPI();
            if ($verse) {
                $this->cacheVerse($verse);
                return $verse;
            }

            // Return a default verse if API fails
            return [
                'verse' => 'Train up a child in the way he should go: and when he is old, he will not depart from it.',
                'reference' => 'Proverbs 22:6',
                'fetched_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("Error getting daily verse: " . $e->getMessage());
            throw $e;
        }
    }

    private function getCachedVerse() {
        try {
            $stmt = $this->db->prepare("
                SELECT verse_text, reference, fetched_at 
                FROM bible_verse_cache 
                WHERE fetched_at > DATE_SUB(NOW(), INTERVAL ? SECOND)
                ORDER BY id DESC 
                LIMIT 1
            ");
            $stmt->execute([$this->cache_duration]);
            
            if ($verse = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return [
                    'verse' => $verse['verse_text'],
                    'reference' => $verse['reference'],
                    'fetched_at' => $verse['fetched_at']
                ];
            }
            
            return null;
        } catch (Exception $e) {
            error_log("Error checking verse cache: " . $e->getMessage());
            return null;
        }
    }

    private function cacheVerse($verse) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO bible_verse_cache (verse_text, reference, fetched_at)
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$verse['verse'], $verse['reference']]);
        } catch (Exception $e) {
            error_log("Error caching verse: " . $e->getMessage());
        }
    }

    private function fetchVerseFromAPI() {
        try {
            // Initialize cURL session
            $ch = curl_init();
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, "https://bible-api.com/proverbs22:6?translation=kjv");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'AllowanceTracker/1.0');
            
            // Execute cURL request
            $response = curl_exec($ch);
            
            // Check for errors
            if (curl_errno($ch)) {
                error_log("cURL Error: " . curl_error($ch));
                curl_close($ch);
                return null;
            }
            
            // Close cURL session
            curl_close($ch);
            
            // Parse response
            $data = json_decode($response, true);
            if (!$data || !isset($data['text']) || !isset($data['reference'])) {
                error_log("Invalid API response: " . $response);
                return null;
            }
            
            return [
                'verse' => trim($data['text']),
                'reference' => $data['reference'],
                'fetched_at' => date('Y-m-d H:i:s')
            ];
        } catch (Exception $e) {
            error_log("Error fetching verse from API: " . $e->getMessage());
            return null;
        }
    }

    public function getRandomVerseAboutMoney() {
        $verses = [
            [
                'verse' => 'For the love of money is the root of all evil: which while some coveted after, they have erred from the faith, and pierced themselves through with many sorrows.',
                'reference' => '1 Timothy 6:10'
            ],
            [
                'verse' => 'But thou shalt remember the LORD thy God: for it is he that giveth thee power to get wealth, that he may establish his covenant which he sware unto thy fathers, as it is this day.',
                'reference' => 'Deuteronomy 8:18'
            ],
            [
                'verse' => 'The rich ruleth over the poor, and the borrower is servant to the lender.',
                'reference' => 'Proverbs 22:7'
            ],
            [
                'verse' => 'A good man leaveth an inheritance to his children\'s children: and the wealth of the sinner is laid up for the just.',
                'reference' => 'Proverbs 13:22'
            ],
            [
                'verse' => 'Honour the LORD with thy substance, and with the firstfruits of all thine increase.',
                'reference' => 'Proverbs 3:9'
            ]
        ];

        $randomVerse = $verses[array_rand($verses)];
        $randomVerse['fetched_at'] = date('Y-m-d H:i:s');
        return $randomVerse;
    }
}