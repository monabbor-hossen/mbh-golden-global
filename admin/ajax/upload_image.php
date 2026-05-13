<?php
<<<<<<< HEAD
<<<<<<< HEAD
header('Content-Type: application/json');

// Only POST requests are allowed
=======
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
>>>>>>> 27f9f30 (make php)
=======
header('Content-Type: application/json');

// Only POST requests are allowed
>>>>>>> 980afd584d3b1ad6b4ee493300c2d3a649f7b13e
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

<<<<<<< HEAD
<<<<<<< HEAD
// Validate the uploaded file
=======
// Check if file was uploaded
>>>>>>> 27f9f30 (make php)
=======
// Validate the uploaded file
>>>>>>> 980afd584d3b1ad6b4ee493300c2d3a649f7b13e
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded.']);
    exit;
}

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 980afd584d3b1ad6b4ee493300c2d3a649f7b13e
$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the server limit.',
        UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the form limit.',
        UPLOAD_ERR_PARTIAL    => 'The file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'File upload stopped by a PHP extension.',
    ];

    $message = $uploadErrors[$file['error']] ?? 'Unknown upload error.';
    http_response_code(400);
    echo json_encode(['error' => $message]);
    exit;
}

if (!is_uploaded_file($file['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid uploaded file.']);
    exit;
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

$originalName = basename($file['name']);
$fileExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($fileExt, $allowedExtensions, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file extension. Allowed: jpg, jpeg, png, webp, gif.']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : false;
finfo_close($finfo);

if ($mimeType === false || !in_array($mimeType, $allowedMimeTypes, true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid MIME type. Only image files are allowed.']);
    exit;
}

$targetDir = __DIR__ . '/../../assets/uploads/';
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        http_response_code(500);
        echo json_encode(['error' => 'Unable to create upload directory.']);
        exit;
    }
}

if (!is_writable($targetDir)) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload directory is not writable.']);
    exit;
}

$safeName = uniqid('img_', true) . '.' . $fileExt;
$destination = $targetDir . $safeName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to move uploaded file.']);
    exit;
}

chmod($destination, 0644);

// Use the public path for the browser. Adjust this if the project is deployed in a subfolder.
$publicPath = '/assets/uploads/' . $safeName;

echo json_encode(['location' => $publicPath]);
<<<<<<< HEAD
=======
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
>>>>>>> 27f9f30 (make php)
=======
>>>>>>> 980afd584d3b1ad6b4ee493300c2d3a649f7b13e
