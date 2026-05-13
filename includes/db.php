<?php
/**
 * Database Connection - PDO MySQL
 * 
 * This file establishes a secure PDO database connection with error handling.
 * Configuration can be updated later for production environments.
 */

$host = 'localhost';
$dbname = 'mbh_golden_global';
$user = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // Log error securely (avoid exposing to user)
    error_log('Database Connection Failed: ' . $e->getMessage());
    
    // User-friendly error message
    die('Database connection error. Please contact support.');
}

?>
