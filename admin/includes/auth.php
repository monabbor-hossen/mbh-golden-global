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

// ── CSRF: Generate a token once per session ───────────────────────
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ── REMEMBER ME COOKIE CHECK ──────────────────────────────────────
if (!isset($_SESSION['admin_id']) && isset($_COOKIE['remember_me'])) {
    $cookie_parts = explode(':', $_COOKIE['remember_me']);
    if (count($cookie_parts) === 2) {
        list($cookie_admin_id, $cookie_token) = $cookie_parts;
        
        // Connect to database to verify token
        require_once __DIR__ . '/../../includes/db.php';
        
        $stmt = $pdo->prepare("SELECT id, name, email, role, remember_token FROM admins WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $cookie_admin_id]);
        $admin = $stmt->fetch();
        
        if ($admin && !empty($admin['remember_token'])) {
            $token_hash = hash('sha256', $cookie_token);
            if (hash_equals($admin['remember_token'], $token_hash)) {
                // Token matches, log them in
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_role'] = $admin['role'];
                $_SESSION['last_activity'] = time();
            } else {
                // Tampered or expired token
                setcookie('remember_me', '', [
                    'expires' => time() - 3600,
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                    'httponly' => true,
                    'samesite' => 'Strict'
                ]);
            }
        } else {
            // Invalid user or no token set
            setcookie('remember_me', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
    }
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
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'])> $SESSION_TIMEOUT) {
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
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        header('Location: ' . $protocol . '://' . $host . '/mbh-golden-global/admin/index.php');
        exit;
    }
}

/**
 * Verify a submitted CSRF token matches the session token.
 * Calls die() immediately on mismatch — never continues execution.
 */
function verify_csrf_token(string $token): void {
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('403 Forbidden: CSRF token validation failed.');
    }
}

/**
 * Strip dangerous tags and event handlers from WYSIWYG HTML
 * before persisting to the database.
 *
 * Removes: <script>, <iframe>, <object>, <embed>, <applet>
 * and all inline event handlers (onclick=, onload=, etc.)
 */
function sanitize_wysiwyg_html(string $html): string {
    // Remove dangerous block-level script tags
    $html = preg_replace('#<script[^>]*>.*?</script>#is', '', $html);
    $html = preg_replace('#<iframe[^>]*>.*?</iframe>#is', '', $html);
    $html = preg_replace('#<object[^>]*>.*?</object>#is', '', $html);
    $html = preg_replace('#<embed[^>]*>.*?</embed>#is', '', $html);
    $html = preg_replace('#<applet[^>]*>.*?</applet>#is', '', $html);
    // Remove inline JS event handlers: onclick="...", onload='...', etc.
    $html = preg_replace('#\s+on[a-z]+\s*=\s*(["\']).*?\1#is', '', $html);
    // Remove javascript: URI schemes from any attribute
    $html = preg_replace('#(href|src|action)\s*=\s*(["\'])\s*javascript:[^"\'>]*\2#is', '', $html);
    return trim($html);
}
?>
