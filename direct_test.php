<?php
// This script tests direct database insertion without form submission
// Show all errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Direct Database Test</h1>";

// Include database connection
require_once 'config/database.php';

// Check database connection
if ($conn->connect_error) {
    die("<p style='color:red'>Database connection failed: " . $conn->connect_error . "</p>");
}

echo "<p style='color:green'>Database connection successful!</p>";

// Check if table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'consultation_requests'");
if ($tableCheck->num_rows == 0) {
    die("<p style='color:red'>Table 'consultation_requests' does not exist!</p>");
}

echo "<p style='color:green'>Table 'consultation_requests' exists!</p>";

// Test data
$name = "Direct Test User";
$email = "direct_test@example.com";
$phone = "987654321";
$service = "direct_test_service";
$message = "This is a direct test message to debug database insertion.";

echo "<p>Attempting to insert test data...</p>";

try {
    // Using prepared statements
    $stmt = $conn->prepare("INSERT INTO consultation_requests (name, email, phone, service_type, message) VALUES (?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        die("<p style='color:red'>Prepare statement failed: " . $conn->error . "</p>");
    }
    
    echo "<p>Statement prepared successfully!</p>";
    
    // Bind parameters
    $stmt->bind_param("sssss", $name, $email, $phone, $service, $message);
    
    // Execute the statement
    if ($stmt->execute()) {
        $insertId = $conn->insert_id;
        echo "<p style='color:green'>Test data inserted successfully with ID: $insertId</p>";
        
        // Display inserted data
        $result = $conn->query("SELECT * FROM consultation_requests WHERE id = $insertId");
        if ($result && $row = $result->fetch_assoc()) {
            echo "<p>Inserted Data:</p>";
            echo "<pre>";
            print_r($row);
            echo "</pre>";
        }
    } else {
        die("<p style='color:red'>Execute failed: " . $stmt->error . "</p>");
    }
} catch (Exception $e) {
    die("<p style='color:red'>Exception: " . $e->getMessage() . "</p>");
}

// Display all existing records
$records = $conn->query("SELECT * FROM consultation_requests ORDER BY created_at DESC");
if ($records && $records->num_rows > 0) {
    echo "<h2>All Records in Database</h2>";
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background-color:#f2f2f2;'><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Service</th><th>Status</th><th>Created At</th></tr>";
    
    while ($row = $records->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
        echo "<td>" . htmlspecialchars($row['service_type']) . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>No records found in database.</p>";
}

echo "<p><a href='index.php'>Return to Homepage</a></p>";
?> 