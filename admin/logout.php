<?php
/**
 * Admin Logout
 * 
 * Destroys session and redirects to login page
 */

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy all session data
session_destroy();

// Clear session cookie if it exists
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Redirect to login page
header('Location: login.php?logged_out=1');
exit;
?>
