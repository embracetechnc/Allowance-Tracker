<?php
namespace App\Config;

class Config {
    private static $config = null;

    private static function load() {
        if (self::$config === null) {
            $configPath = $_SERVER['DOCUMENT_ROOT'] . '/../config.php';
            if (!file_exists($configPath)) {
                error_log("Config file not found at: {$configPath}");
                self::$config = [];
                return;
            }
            self::$config = require $configPath;
        }
    }

    public static function get($key, $default = null) {
        self::load();
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    public static function has($key) {
        self::load();
        
        $keys = explode('.', $key);
        $value = self::$config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return false;
            }
            $value = $value[$k];
        }
        
        return true;
    }
}