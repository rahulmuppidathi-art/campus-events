<?php
/**
 * CAMPUS EVENTS - College Event Management System
 * Academic Web Technology Implementation Project
 * Team Leader: Patrick
 * 
 * Application Configuration
 */

// Configure session directory if default is not writable
$sessionDir = __DIR__ . '/../assets/sessions';
if (!is_dir($sessionDir)) {
    @mkdir($sessionDir, 0777, true);
}
if (is_dir($sessionDir) && is_writable($sessionDir)) {
    session_save_path($sessionDir);
}

// Start session securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    @session_start();
}

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Application Branding
define('APP_NAME', 'CAMPUS EVENTS');
define('APP_TAGLINE', 'Discover. Participate. Celebrate.');
define('TEAM_LEADER', 'Patrick');
define('APP_VERSION', '1.0.0');

// Database Configuration (Standard XAMPP MySQL defaults)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'campus_events_db');
define('DB_PORT', 3306);

// Base Path and Dynamic URL detection
define('ROOT_PATH', dirname(__DIR__));
define('UPLOADS_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads');

// Calculate base URL automatically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Determine web root path cleanly
if (php_sapi_name() === 'cli') {
    define('BASE_URL', './');
} else {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
    // If running in admin subfolder, move one level up
    if (str_ends_with($scriptDir, '/admin') || str_ends_with($scriptDir, '\\admin')) {
        $baseFolder = dirname($scriptDir);
    } else {
        $baseFolder = $scriptDir;
    }
    $baseFolder = str_replace('\\', '/', $baseFolder);
    $baseFolder = rtrim($baseFolder, '/');

    if (!empty($baseFolder) && $baseFolder !== '/') {
        define('BASE_URL', $protocol . $host . $baseFolder . '/');
    } else {
        define('BASE_URL', $protocol . $host . '/');
    }
}

// Error reporting (set for clean academic demonstration)
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', '1');
