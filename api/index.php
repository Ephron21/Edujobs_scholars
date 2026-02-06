<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for proper PHP execution
header('Content-Type: text/html; charset=utf-8');

// Include your main application file
if (file_exists('../index.php')) {
    require_once '../index.php';
} else {
    echo "Welcome to EduJobs Scholars!";
} 