<?php
// db.php - use this include in all files
// Database credentials (you provided)
$DB_HOST = 'localhost';
$DB_NAME = 'dbjpxlupgh2qgi';
$DB_USER = 'u4xcmflvc2cc9';
$DB_PASS = 'frkh4gf8mwve';

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    // If connection fails, show friendly error
    http_response_code(500);
    echo "Database connection failed: " . htmlspecialchars($e->getMessage());
    exit;
}
