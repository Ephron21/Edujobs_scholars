<?php
// Include database connection
require_once 'config/database.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to check if table exists
function tableExists($conn, $tableName) {
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    return $result->num_rows > 0;
}

// Function to check if index exists
function indexExists($conn, $tableName, $indexName) {
    $result = $conn->query("SHOW INDEX FROM `$tableName` WHERE Key_name = '$indexName'");
    return $result && $result->num_rows > 0;
}

// Create the consultation_requests table if it doesn't exist
function createConsultationRequestsTable($conn) {
    // SQL query to create the table
    $sql = "
    CREATE TABLE IF NOT EXISTS `consultation_requests` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `email` varchar(255) NOT NULL,
      `phone` varchar(50) NOT NULL,
      `service_type` varchar(100) NOT NULL,
      `message` text NOT NULL,
      `status` enum('pending','contacted','completed','cancelled') NOT NULL DEFAULT 'pending',
      `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    // Try to execute the SQL query
    if ($conn->query($sql) === TRUE) {
        echo "Table 'consultation_requests' created successfully or already exists.<br>";
        
        // Add indexes if they don't exist
        $indexes = [
            ['name' => 'idx_status', 'column' => 'status'],
            ['name' => 'idx_service_type', 'column' => 'service_type']
        ];
        
        foreach ($indexes as $index) {
            if (!indexExists($conn, 'consultation_requests', $index['name'])) {
                $indexQuery = "CREATE INDEX {$index['name']} ON consultation_requests({$index['column']})";
                if ($conn->query($indexQuery) === TRUE) {
                    echo "Index {$index['name']} created successfully.<br>";
                } else {
                    echo "Error creating index {$index['name']}: " . $conn->error . "<br>";
                }
            } else {
                echo "Index {$index['name']} already exists.<br>";
            }
        }
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Main execution
if (!tableExists($conn, 'consultation_requests')) {
    echo "Table 'consultation_requests' does not exist. Creating...<br>";
    createConsultationRequestsTable($conn);
} else {
    echo "Table 'consultation_requests' already exists.<br>";
    
    // Check and create indexes if they don't exist
    $indexes = [
        ['name' => 'idx_status', 'column' => 'status'],
        ['name' => 'idx_service_type', 'column' => 'service_type']
    ];
    
    foreach ($indexes as $index) {
        if (!indexExists($conn, 'consultation_requests', $index['name'])) {
            $indexQuery = "CREATE INDEX {$index['name']} ON consultation_requests({$index['column']})";
            if ($conn->query($indexQuery) === TRUE) {
                echo "Index {$index['name']} created successfully.<br>";
            } else {
                echo "Error creating index {$index['name']}: " . $conn->error . "<br>";
            }
        } else {
            echo "Index {$index['name']} already exists.<br>";
        }
    }
}

echo "<p>Database update completed!</p>";
echo "<p><a href='index.php'>Return to Homepage</a></p>";
?> 