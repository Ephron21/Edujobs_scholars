<?php
session_start();
require_once 'config.php';
include('includes/functions.php');

// Ensure only admin can access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['admin_id'];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $isPublic = isset($_POST['is_public']) ? 1 : 0;
    
    if (uploadFile($conn, $_FILES['file'], $title, $description, $isPublic, $userId)) {
        $successMessage = "File uploaded successfully!";
    } else {
        $errorMessage = "File upload failed.";
    }
}

// Handle file deletion
if (isset($_GET['delete'])) {
    $fileId = $_GET['delete'];
    if (deleteFile($conn, $fileId, $userId, true)) {
        $successMessage = "File deleted successfully!";
    } else {
        $errorMessage = "File deletion failed.";
    }
}

// Fetch all uploaded files
$query = "SELECT * FROM uploaded_files ORDER BY upload_date DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin - File Management</title>
    <!-- Bootstrap and other CSS -->
</head>
<body>
    <div class="container">
        <h1>File Management</h1>
        
        <!-- File Upload Form -->
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <input type="text" name="title" placeholder="File Title" required>
            <textarea name="description" placeholder="File Description"></textarea>
            <label>
                <input type="checkbox" name="is_public"> Public File
            </label>
            <button type="submit">Upload File</button>
        </form>
        
        <!-- Files Table -->
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Public</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($files as $file): ?>
                <tr>
                    <td><?php echo htmlspecialchars($file['title']); ?></td>
                    <td><?php echo htmlspecialchars($file['file_type']); ?></td>
                    <td><?php echo $file['is_public'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <a href="edit_file.php?id=<?php echo $file['id']; ?>">Edit</a>
                        <a href="?delete=<?php echo $file['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>