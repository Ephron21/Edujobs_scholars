<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then restrict access
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Access denied. Please log in.']);
    exit;
}

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

// Function to safely handle output for JSON
function safeJsonEncode($data) {
    return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An error occurred while fetching files.',
    'files' => []
];

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set up pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    // Prepare query to get files with pagination
    $query = "SELECT * FROM files ORDER BY uploaded_at DESC LIMIT :offset, :limit";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    
    // Fetch files data
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Count total files for pagination
    $countStmt = $conn->query("SELECT COUNT(*) FROM files");
    $totalFiles = (int)$countStmt->fetchColumn();
    
    // Calculate total pages
    $totalPages = ceil($totalFiles / $limit);
    
    // Format the response
    $formattedFiles = [];
    foreach ($files as $file) {
        $formattedFiles[] = [
            'id' => $file['id'],
            'name' => $file['filename'],
            'title' => $file['title'],
            'description' => $file['description'],
            'type' => $file['filetype'],
            'size' => $file['filesize'],
            'is_public' => (bool)$file['is_public'],
            'uploaded_at' => date('M j, Y g:i A', strtotime($file['uploaded_at'])),
            'uploaded_by' => $file['uploaded_by']
        ];
    }
    
    // Update response with success
    $response = [
        'status' => 'success',
        'message' => 'Files retrieved successfully.',
        'files' => $formattedFiles,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_files' => $totalFiles,
            'files_per_page' => $limit
        ]
    ];
    
} catch (PDOException $e) {
    // Update response with error
    $response = [
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'files' => []
    ];
} catch (Exception $e) {
    // Update response with error
    $response = [
        'status' => 'error',
        'message' => 'System error: ' . $e->getMessage(),
        'files' => []
    ];
}

// Output response as JSON
header('Content-Type: application/json');
echo safeJsonEncode($response);
exit;