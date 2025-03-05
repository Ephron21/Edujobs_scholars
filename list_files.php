<?php
// list_files.php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'registration_system';
$username = 'root';
$password = 'Diano21@Esron21%';

// Function to check if user is logged in
function isUserLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// Function to check if current user is an admin
function isAdminLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true &&
           isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] === true;
}

// Function to get current user ID
function getCurrentUserId() {
    return isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : null;
}

// Enhanced PDO connection with security settings
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Pagination and Search
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$itemsPerPage = 10;
$offset = ($page - 1) * $itemsPerPage;

// Build query for files
$query = "SELECT uf.*, a.email AS uploaded_by_email 
          FROM uploaded_files uf 
          LEFT JOIN admins a ON uf.uploaded_by = a.id 
          WHERE 1=1";

$params = [];

// If not admin, only show public files or user's own files
if (!isAdminLoggedIn()) {
    $query .= " AND (uf.is_public = 1 OR uf.uploaded_by = :user_id)";
    $params[':user_id'] = getCurrentUserId();
}

// Add search condition
if (!empty($search)) {
    $query .= " AND (uf.title LIKE :search OR uf.description LIKE :search OR uf.original_name LIKE :search)";
    $params[':search'] = "%{$search}%";
}

// Count total files for pagination
$countQuery = preg_replace('/SELECT .*? FROM/i', 'SELECT COUNT(*) FROM', $query);
$countStmt = $conn->prepare($countQuery);
$countStmt->execute($params);
$totalFiles = $countStmt->fetchColumn();

// Total pages calculation
$totalPages = ceil($totalFiles / $itemsPerPage);

// Add pagination to query
$query .= " ORDER BY uf.upload_date DESC LIMIT :limit OFFSET :offset";
$params[':limit'] = $itemsPerPage;
$params[':offset'] = $offset;

// Prepare and execute query
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    if (is_int($value)) {
        $stmt->bindValue($key, $value, PDO::PARAM_INT);
    } else {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
}
$stmt->execute();
$files = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Library</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        .file-card {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        .file-icon {
            max-width: 100px;
            margin: 0 auto 10px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <h1>File Library</h1>

    <div class="search-container">
        <form method="GET">
            <input type="text" name="search" placeholder="Search files..." 
                   value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <?php if (empty($files)): ?>
        <p>No files available.</p>
    <?php else: ?>
        <div class="file-grid">
            <?php foreach ($files as $file): ?>
                <div class="file-card">
                    <h3><?php echo htmlspecialchars($file['title']); ?></h3>
                    <p><?php echo htmlspecialchars($file['description'] ?? 'No description'); ?></p>
                    <p>Type: <?php echo htmlspecialchars($file['file_type']); ?></p>
                    <p>Uploaded: <?php echo date('Y-m-d', strtotime($file['upload_date'])); ?></p>
                    <?php if (isAdminLoggedIn() || $file['is_public']): ?>
                        <a href="download.php?id=<?php echo $file['id']; ?>">Download</a>
                    <?php else: ?>
                        <p>Not authorized to download</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; 
                    echo !empty($search) ? '&search=' . urlencode($search) : ''; 
                    ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</body>
</html>