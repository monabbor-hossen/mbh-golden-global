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

// Check if user is logged in to clear remember token
if (isset($_SESSION['admin_id'])) {
    require_once '../includes/db.php';
    $stmt = $pdo->prepare("UPDATE admins SET remember_token = NULL WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['admin_id']]);
}

// Clear remember_me cookie
if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
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
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
header('Location: ' . $protocol . '://' . $host . '/mbh-golden-global/admin/login.php?logged_out=1');
exit;
?>
