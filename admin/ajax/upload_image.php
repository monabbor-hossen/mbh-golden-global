<?php
/**
 * WYSIWYG Editor Image Upload Handler
 *
 * Handles async image uploads from TinyMCE and other rich-text editors.
 * Returns a JSON response with the web-accessible image URL on success.
 *
 * Expected $_FILES key: 'file'
 * Response format (TinyMCE compatible): { "location": "/mbh-golden-global/assets/uploads/img_xxx.jpg" }
 */

// ── 1. Force JSON output for ALL responses (including errors) ──────────────────
header('Content-Type: application/json; charset=utf-8');

// ── 2. Bootstrap ──────────────────────────────────────────────────────────────
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../includes/upload.php';

// ── 3. Session / Auth guard ───────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure'   => isset($_SERVER['HTTPS']),
        'cookie_samesite' => 'Strict',
    ]);
}

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_email'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden: authentication required.']);
    exit;
}

// ── 4. Method guard ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// ── 5. Presence check ─────────────────────────────────────────────────────────
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file field received. Expected $_FILES["file"].']);
    exit;
}

$file = $_FILES['file'];

// ── 6. PHP upload error codes ─────────────────────────────────────────────────
if ($file['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload_max_filesize limit (php.ini).',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds the MAX_FILE_SIZE directive in the HTML form.',
        UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded. Please try again.',
        UPLOAD_ERR_NO_FILE    => 'No file was sent. Please select a file before uploading.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server error: missing temporary upload folder.',
        UPLOAD_ERR_CANT_WRITE => 'Server error: failed to write to disk. Check directory permissions.',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by a PHP extension on the server.',
    ];
    $message = $uploadErrors[$file['error']] ?? 'Unknown upload error (code: ' . $file['error'] . ').';
    http_response_code(400);
    echo json_encode(['error' => $message]);
    exit;
}

// ── 7. Delegate to the shared upload handler ──────────────────────────────────
//
//   Absolute server path:  /opt/lampp/htdocs/mbh-golden-global/assets/uploads
//   __DIR__ here           /opt/lampp/htdocs/mbh-golden-global/admin/ajax
//   Two levels up (../..)  /opt/lampp/htdocs/mbh-golden-global
//
$uploadDir = realpath(__DIR__ . '/../../assets/uploads');

// realpath() returns false if the directory doesn't exist yet — handle that.
if ($uploadDir === false) {
    $uploadDir = __DIR__ . '/../../assets/uploads';
    if (!mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: could not create upload directory.']);
        exit;
    }
    // Re-resolve now that it exists
    $uploadDir = realpath($uploadDir);
}

$result = handle_image_upload($file, $uploadDir);

if (!$result['success']) {
    http_response_code(400);
    echo json_encode(['error' => $result['error']]);
    exit;
}

// ── 8. Return the browser-accessible URL ─────────────────────────────────────
//   The app lives at http://localhost/mbh-golden-global/, so the public path is:
//   /mbh-golden-global/assets/uploads/<filename>
echo json_encode([
    'location' => $result['url'], // already built correctly inside handle_image_upload()
]);
