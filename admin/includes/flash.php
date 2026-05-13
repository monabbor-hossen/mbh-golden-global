<?php
/**
 * Flash Message Helper
 *
 * Provides a single-use session-based message store so admin pages can
 * redirect after a POST (PRG pattern) and still surface a result message.
 *
 * Usage:
 *   flash_set('Record saved!');            // success (default)
 *   flash_set('Something failed.', 'error');
 *   header('Location: page.php'); exit;
 *
 *   // On the next GET request, at the top of the page:
 *   [$message, $message_type] = flash_get();
 */

/**
 * Store a flash message in the session.
 *
 * @param string $message The human-readable message.
 * @param string $type    'success' or 'error'
 */
function flash_set(string $message, string $type = 'success'): void
{
    $_SESSION['_flash_message'] = $message;
    $_SESSION['_flash_type']    = $type;
}

/**
 * Read and immediately clear the flash message from the session.
 *
 * @return array [$message, $type]  — both empty strings if no flash was set.
 */
function flash_get(): array
{
    $message = $_SESSION['_flash_message'] ?? '';
    $type    = $_SESSION['_flash_type']    ?? 'success';
    unset($_SESSION['_flash_message'], $_SESSION['_flash_type']);
    return [$message, $type];
}
