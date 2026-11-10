<?php
/**
 * CAMPUS EVENTS - Sign Out Handler
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Unset all session variables
$_SESSION = [];

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Start a fresh session just to store the flash message
session_start();
set_flash('info', 'You have been signed out safely. See you soon!');
header('Location: ' . BASE_URL . 'login.php');
exit;

