<?php
// Set content type to HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Consultation Form</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        form { background: #f4f4f4; padding: 20px; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        .test-section { margin-top: 30px; padding: 20px; background: #e9f7ef; border-radius: 5px; }
        .test-button { background: #2196F3; }
        .test-button:hover { background: #0b7dda; }
    </style>
</head>
<body>
    <h1>Test Consultation Form</h1>
    <p>This page is for testing the consultation form submission to diagnose any issues with data storage.</p>
    
    <form action="process_consultation.php" method="post">
        <div class="form-group">
            <label for="name">Full Name:</label>
            <input type="text" id="name" name="name" value="Test User" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" value="test@example.com" required>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" value="123456789" required>
        </div>
        
        <div class="form-group">
            <label for="service">Service Interested In:</label>
            <select id="service" name="service" required>
                <option value="">Select a Service</option>
                <option value="cv_writing" selected>CV & Cover Letter Writing</option>
                <option value="university_application">University Application Guidance</option>
                <option value="mifotra_setup">MIFOTRA Account Setup</option>
                <option value="job_application">Job Application Assistance</option>
                <option value="other">Other Services</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="message">Your Message:</label>
            <textarea id="message" name="message" rows="4" required>This is a test message for debugging purposes.</textarea>
        </div>
        
        <button type="submit">Submit Test Request</button>
    </form>
    
    <div class="test-section">
        <h2>Direct Database Test</h2>
        <p>Click the button below to test the database insertion directly without form submission:</p>
        <a href="process_consultation.php?test=true"><button class="test-button">Run Database Test</button></a>
    </div>
    
    <div class="test-section">
        <h2>Check Current Database Records</h2>
        <p>View existing records in the consultation_requests table:</p>
        <?php
        // Include database connection
        require_once 'config/database.php';
        
        if ($conn->connect_error) {
            echo "<p style='color:red'>Database connection failed: " . $conn->connect_error . "</p>";
        } else {
            // Check if table exists
            $result = $conn->query("SHOW TABLES LIKE 'consultation_requests'");
            if ($result->num_rows == 0) {
                echo "<p style='color:red'>Table 'consultation_requests' does not exist!</p>";
            } else {
                // Get all records
                $records = $conn->query("SELECT * FROM consultation_requests ORDER BY created_at DESC");
                
                if ($records->num_rows > 0) {
                    echo "<h3>Found " . $records->num_rows . " records in the database:</h3>";
                    echo "<table border='1' cellpadding='5' style='width:100%; border-collapse: collapse;'>";
                    echo "<tr style='background-color:#f2f2f2;'><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Service</th><th>Status</th><th>Created</th></tr>";
                    
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
                    echo "<p>No records found in the consultation_requests table.</p>";
                }
            }
        }
        ?>
    </div>
    
    <p style="margin-top: 20px;"><a href="index.php">Return to Homepage</a></p>
</body>
</html> 