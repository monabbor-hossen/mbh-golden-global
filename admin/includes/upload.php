<?php
/**
 * Secure File Upload Handler
 * 
 * Validates and safely uploads files to the assets/uploads directory
 * Prevents malicious uploads and ensures file security
 */

/**
 * Upload and validate an image file
 * 
 * @param array $file The $_FILES array element (e.g., $_FILES['image'])
 * @param string $upload_dir Directory where files will be saved (absolute path)
 * @return array ['success' => bool, 'url' => string|null, 'error' => string|null]
 */
function handle_image_upload($file, $upload_dir = null)
{
    // Use default upload directory if not specified
    if ($upload_dir === null) {
        $upload_dir = __DIR__ . '/../assets/uploads';
    }

    // Ensure upload directory exists
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0755, true);
    }

    // File validation
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
    $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
    $max_file_size = 5 * 1024 * 1024; // 5MB

    // Check file existence
    if (!isset($file) || !isset($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded.'];
    }

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds maximum upload size.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form maximum size.',
            UPLOAD_ERR_PARTIAL => 'File was partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file.',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
        ];
        $message = $error_messages[$file['error']] ?? 'Unknown upload error.';
        return ['success' => false, 'error' => $message];
    }

    // Validate file size
    if ($file['size'] > $max_file_size) {
        return ['success' => false, 'error' => 'File size exceeds 5MB limit.'];
    }

    // Get file extension and MIME type
    $original_name = basename($file['name']);
    $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    // Validate extension
    if (!in_array($file_ext, $allowed_extensions)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, WEBP.'];
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowed_mimes)) {
        return ['success' => false, 'error' => 'Invalid MIME type. Only image files allowed.'];
    }

    // Security check: Prevent PHP/executable uploads
    $dangerous_mimes = [
        'application/x-php',
        'application/x-httpd-php',
        'application/x-httpd-php3',
        'application/x-httpd-php4',
        'application/x-httpd-php5',
        'application/x-executable',
        'application/x-elf',
    ];

    if (in_array($mime_type, $dangerous_mimes)) {
        return ['success' => false, 'error' => 'Executable files are not allowed.'];
    }

    // Additional security: Check for PHP code in file
    $file_contents = file_get_contents($file['tmp_name'], false, null, 0, 1024);
    if (stripos($file_contents, '<?php') !== false || stripos($file_contents, '<?') !== false) {
        return ['success' => false, 'error' => 'File contains PHP code. Not allowed.'];
    }

    // Generate unique filename with timestamp and random string
    $unique_name = uniqid('img_', true) . '.' . $file_ext;
    $destination = $upload_dir . DIRECTORY_SEPARATOR . $unique_name;

    // Move uploaded file to destination
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'Failed to save file.'];
    }

    // Set proper permissions
    chmod($destination, 0644);

    // Return the relative URL for web access
    $relative_url = '/assets/uploads/' . $unique_name;

    return [
        'success' => true,
        'url' => $relative_url,
        'filename' => $unique_name,
    ];
}

/**
 * Delete an uploaded image file
 * 
 * @param string $filename The filename to delete
 * @param string $upload_dir Directory where files are stored (absolute path)
 * @return array ['success' => bool, 'message' => string]
 */
function delete_image_file($filename, $upload_dir = null)
{
    if ($upload_dir === null) {
        $upload_dir = __DIR__ . '/../assets/uploads';
    }

    // Security: Prevent directory traversal
    if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
        return ['success' => false, 'message' => 'Invalid filename.'];
    }

    $file_path = $upload_dir . DIRECTORY_SEPARATOR . $filename;

    if (!file_exists($file_path)) {
        return ['success' => false, 'message' => 'File not found.'];
    }

    if (!is_file($file_path) || !is_writable($file_path)) {
        return ['success' => false, 'message' => 'Cannot delete file.'];
    }

    if (unlink($file_path)) {
        return ['success' => true, 'message' => 'File deleted successfully.'];
    }

    return ['success' => false, 'message' => 'Failed to delete file.'];
}
