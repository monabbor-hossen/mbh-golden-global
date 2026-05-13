<?php
/**
 * WYSIWYG Editor Image Upload Handler
 * 
 * Handles async image uploads from TinyMCE and other editors
 * Returns JSON with image URL for embedded use
 */

require_once '../../includes/db.php';
require_once '../includes/upload.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded.']);
    exit;
}

// Determine uploaded file key for editors
$file = $_FILES['file'] ?? $_FILES['upload'] ?? $_FILES['image'] ?? null;
if (!$file || !isset($file['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded.']);
    exit;
}

$upload_dir = __DIR__ . '/../../assets/uploads';
$result = handle_image_upload($file, $upload_dir);

if (!$result['success']) {
    http_response_code(400);
    echo json_encode(['error' => $result['error']]);
    exit;
}

// Return success response for TinyMCE
echo json_encode([
    'location' => $result['url'],
]);
