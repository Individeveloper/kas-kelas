<?php
/**
 * Database Helper
 * Koneksi dan query database
 */

namespace App\Helpers;

class Database {
    private static $db;

    /**
     * Initialize Database Connection
     */
    public static function init() {
        if (!self::$db) {
            self::$db = new \mysqli(
                $_ENV['DB_HOST'] ?? 'localhost',
                $_ENV['DB_USER'] ?? 'root',
                $_ENV['DB_PASS'] ?? '',
                $_ENV['DB_NAME'] ?? 'kasKelas'
            );

            if (self::$db->connect_errno) {
                die("Failed to connect to MySQL: " . self::$db->connect_error);
            }

            self::$db->set_charset("utf8");
        }
        return self::$db;
    }

    /**
     * Get Database Instance
     */
    public static function getConnection() {
        return self::$db ?? self::init();
    }

    /**
     * Execute Query
     */
    public static function query($sql) {
        $db = self::getConnection();
        return $db->query($sql);
    }

    /**
     * Escape String
     */
    public static function escape($string) {
        $db = self::getConnection();
        return $db->real_escape_string($string);
    }

    /**
     * Get Last Insert ID
     */
    public static function lastId() {
        $db = self::getConnection();
        return $db->insert_id;
    }

    /**
     * Get Error
     */
    public static function error() {
        $db = self::getConnection();
        return $db->error;
    }
}
