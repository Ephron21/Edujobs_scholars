<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session securely
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Database configuration (Move credentials to an environment file for security)
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

// Check if it's an AJAX request
$isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Enhanced PDO connection with security settings
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ]);
} catch (PDOException $e) {
    if ($isAjaxRequest) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }
    die('Database connection failed');
}

// Function to send a secure JSON response
function sendJsonResponse($status, $message, $httpCode = 200) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    http_response_code($httpCode);
    echo json_encode(['status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Function to check if the admin is logged in and get user ID
function getCurrentUserId() {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        return null;
    }
    
    // Check if user_id exists in session
    if (!isset($_SESSION["admin_id"])) {
        // If not, try to fetch it from the database
        global $conn;
        try {
            $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
            $stmt->execute([$_SESSION["email"]]);
            $user = $stmt->fetch();
            
            if ($user) {
                $_SESSION["admin_id"] = $user['id'];
                return $user['id'];
            }
        } catch (PDOException $e) {
            error_log('Error fetching user ID: ' . $e->getMessage());
        }
        
        return null;
    }
    
    return $_SESSION["admin_id"];
}

// Function to check if the admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true &&
           isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
}

// Create files table if not exists
function createFilesTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS uploaded_files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_type VARCHAR(100) NOT NULL,
        file_size INT NOT NULL,
        uploaded_by INT,
        is_public BOOLEAN DEFAULT FALSE,
        title VARCHAR(255),
        description TEXT,
        upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (uploaded_by) REFERENCES admins(id)
    )";
    $conn->exec($sql);
}

// Call the function to ensure table exists
createFilesTable($conn);

// File upload handler
function uploadFile($conn) {
    // Check if user is admin
    if (!isAdminLoggedIn()) {
        sendJsonResponse('error', 'Unauthorized access', 403);
    }

    // Get current user ID
    $userId = getCurrentUserId();
    if (!$userId) {
        sendJsonResponse('error', 'Admin not found', 403);
    }

    // Check if file was uploaded
    if (!isset($_FILES['file'])) {
        sendJsonResponse('error', 'No file uploaded', 400);
    }

    // Validate additional fields
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($title)) {
        sendJsonResponse('error', 'File title is required', 400);
    }

    $file = $_FILES['file'];
    $uploadDir = 'uploads/';

    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Validate file
    $maxFileSize = 50 * 1024 * 1024; // 50MB
    $allowedTypes = [
        'image/jpeg', 
        'image/png', 
        'image/gif',
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'video/mp4',
        'video/mpeg',
        'text/plain'
    ];

    // Check file size
    if ($file['size'] > $maxFileSize) {
        sendJsonResponse('error', 'File is too large. Maximum size is 50MB', 400);
    }

    // Check file type
    if (!in_array($file['type'], $allowedTypes)) {
        sendJsonResponse('error', 'Invalid file type', 400);
    }

    // Generate a unique filename
    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueFilename = uniqid('upload_') . '.' . $fileExtension;
    $uploadPath = $uploadDir . $uniqueFilename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        sendJsonResponse('error', 'File upload failed', 500);
    }

    // Save file info to database
    try {
        $stmt = $conn->prepare("INSERT INTO uploaded_files 
            (filename, original_name, file_path, file_type, file_size, uploaded_by, is_public, title, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $uniqueFilename, 
            $file['name'], 
            $uploadPath, 
            $file['type'], 
            $file['size'], 
            $userId, // Use the retrieved user ID
            isset($_POST['is_public']) ? 1 : 0,
            $title,
            $description
        ]);

        sendJsonResponse('success', 'File uploaded successfully', 200);
    } catch (PDOException $e) {
        // Remove the file if database insertion fails
        unlink($uploadPath);
        sendJsonResponse('error', 'Database error: ' . $e->getMessage(), 500);
    }
}

// Check if it's an AJAX upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    uploadFile($conn);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], input[type="file"], textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php
    // Check if user is logged in and is an admin
    if (!isAdminLoggedIn()) {
        echo '<p>You must be logged in as an admin to access this page.</p>';
        exit;
    }
    ?>
    <form id="fileUploadForm" enctype="multipart/form-data">
        <div id="errorMessage" class="error"></div>
        
        <div class="form-group">
            <label for="file">Choose File:</label>
            <input type="file" id="file" name="file" required 
                   accept="image/jpeg,image/png,image/gif,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,video/mp4,video/mpeg,text/plain">
            <small>Max file size: 50MB. Allowed types: images, PDFs, Word docs, videos</small>
        </div>

        <div class="form-group">
            <label for="title">File Title:</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_public" value="1"> Make file publicly visible
            </label>
        </div>

        <button type="submit">Upload File</button>
    </form>

    <script>
    document.getElementById('fileUploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous error messages
        const errorMessage = document.getElementById('errorMessage');
        errorMessage.textContent = '';

        // Create form data
        const formData = new FormData(this);

        // Send AJAX request
        fetch('', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('File uploaded successfully!');
                // Optional: Redirect or reset form
                this.reset();
            } else {
                errorMessage.textContent = data.message;
            }
        })
        .catch(error => {
            errorMessage.textContent = 'An error occurred during upload.';
            console.error('Error:', error);
        });
    });
    </script>
</body>
</html>