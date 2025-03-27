<?php
// This is a test file to help debug file access issues
session_start();

// Make this accessible only to admin users
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access denied.";
    exit;
}

echo "<h1>File System Test</h1>";

// Check uploads directory
$uploadDir = "uploads/";
echo "<h2>Upload Directory</h2>";
echo "Path: " . realpath($uploadDir) . "<br>";
echo "Exists: " . (is_dir($uploadDir) ? "Yes" : "No") . "<br>";
echo "Readable: " . (is_readable($uploadDir) ? "Yes" : "No") . "<br>";
echo "Writable: " . (is_writable($uploadDir) ? "Yes" : "No") . "<br>";
echo "Permissions: " . substr(sprintf("%o", fileperms($uploadDir)), -4) . "<br>";

// List files in uploads directory
echo "<h2>Files in Uploads Directory</h2>";
$files = glob($uploadDir . "*");
if (empty($files)) {
    echo "No files found.<br>";
} else {
    echo "<table border='1'>";
    echo "<tr><th>Filename</th><th>Path</th><th>Size</th><th>Permissions</th><th>Readable</th><th>Test Link</th></tr>";
    foreach ($files as $file) {
        if (is_file($file)) {
            echo "<tr>";
            echo "<td>" . basename($file) . "</td>";
            echo "<td>" . $file . "</td>";
            echo "<td>" . filesize($file) . " bytes</td>";
            echo "<td>" . substr(sprintf("%o", fileperms($file)), -4) . "</td>";
            echo "<td>" . (is_readable($file) ? "Yes" : "No") . "</td>";
            echo "<td><a href='view_file.php?file=" . urlencode($file) . "' target='_blank'>View File</a></td>";
            echo "</tr>";
        }
    }
    echo "</table>";
}

// PHP Info
echo "<h2>Server Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Web Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "Script Owner: " . get_current_user() . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Current Script: " . __FILE__ . "<br>";
?> 