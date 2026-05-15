<?php
/**
 * Authentication Check - Include at the top of all protected admin pages
 * 
 * This file verifies the user has an active admin session.
 * If not authenticated, redirects to login page.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict',
    ]);
}

// Check if user is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    // Destroy any partial session data
    session_destroy();
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    header('Location: ' . $protocol . '://' . $host . '/mbh-golden-global/admin/login.php');
    exit;
}

// Optional: Verify session hasn't expired (30 minutes of inactivity)
$SESSION_TIMEOUT = 1800; // 30 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $SESSION_TIMEOUT) {
    session_destroy();
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    header('Location: ' . $protocol . '://' . $host . '/mbh-golden-global/admin/login.php?timeout=1');
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();

// Get admin info from session (already validated at login)
$adminId = $_SESSION['admin_id'];
$adminEmail = $_SESSION['admin_email'];
$adminName = $_SESSION['admin_name'];

function requireAdmin() {
    if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'admin') {
        // Redirect staff back to dashboard if they try to access restricted pages
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        header('Location: ' . $protocol . '://' . $host . '/mbh-golden-global/admin/index.php');
        exit;
    }
}
?>
