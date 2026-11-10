<?php
/**
 * CAMPUS EVENTS - Database Connection Helper
 * Uses PDO for secure prepared statements and SQL injection prevention.
 */

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;
    private static ?string $connectionError = null;

    /**
     * Get active PDO database connection
     */
    public static function getConnection(): ?PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 5,
                ];

                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                self::$connectionError = $e->getMessage();
                return null;
            }
        }
        return self::$instance;
    }

    /**
     * Retrieve connection error if any
     */
    public static function getError(): ?string {
        return self::$connectionError;
    }
}

/**
 * Global helper function to get database instance
 */
function getDB(): ?PDO {
    return Database::getConnection();
}

