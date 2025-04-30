<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'edujobs_scholars');
define('DB_USER', 'root');
define('DB_PASS', 'Diano21@Esron21%'); // XAMPP default empty password

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Timezone
date_default_timezone_set('UTC');

// Database Connection
try {
    // First try to connect without selecting database
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Create the edujobs_admin user and grant privileges
    $pdo->exec("CREATE USER IF NOT EXISTS 'edujobs_admin'@'localhost' IDENTIFIED BY 'Edujobs@2024'");
    $pdo->exec("GRANT ALL PRIVILEGES ON " . DB_NAME . ".* TO 'edujobs_admin'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    
    // Now connect to the specific database
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Application Constants
define('SITE_URL', 'http://localhost/Edujobs_scholars');
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']); 