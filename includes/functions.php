<!-- <?php
// Useful functions for the application

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to handle file uploads
function handle_file_upload($file, $admin_id, $file_type) {
    $upload_dir = "../uploads/";
    $target_dir = "";
    
    // Determine target directory based on file type
    switch($file_type) {
        case 'id':
            $target_dir = $upload_dir . "id_documents/";
            break;
        case 'diploma':
            $target_dir = $upload_dir . "diplomas/";
            break;
        case 'image':
            $target_dir = $upload_dir . "profile_images/";
            break;
        default:
            return false;
    }
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = $file_type . '_' . $user_id . '_' . time() . '.' . $file_extension;
    $destination = $target_dir . $new_filename;
    
    // Move file to destination
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $destination;
    }
    
    return false;
}
?>

 -->

 <?php
// functions.php

function getPublicFiles($conn, $limit = 6) {
    $query = "SELECT * FROM uploaded_files WHERE is_public = 1 ORDER BY upload_date DESC LIMIT :limit";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function uploadFile($conn, $file, $title, $description, $isPublic, $userId) {
    $uploadDir = 'uploads/';
    $filename = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $query = "INSERT INTO uploaded_files 
                  (filename, title, description, file_type, uploaded_by, is_public, upload_date) 
                  VALUES (:filename, :title, :description, :file_type, :uploaded_by, :is_public, NOW())";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':file_type', $file['type']);
        $stmt->bindParam(':uploaded_by', $userId);
        $stmt->bindParam(':is_public', $isPublic, PDO::PARAM_BOOL);
        
        return $stmt->execute();
    }
    return false;
}

function deleteFile($conn, $fileId, $userId, $isAdmin = false) {
    // Check file ownership or admin status
    $checkQuery = $isAdmin 
        ? "SELECT filename FROM uploaded_files WHERE id = :file_id" 
        : "SELECT filename FROM uploaded_files WHERE id = :file_id AND uploaded_by = :user_id";
    
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bindParam(':file_id', $fileId);
    if (!$isAdmin) {
        $checkStmt->bindParam(':user_id', $userId);
    }
    $checkStmt->execute();
    $file = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($file) {
        // Delete file from filesystem
        unlink('uploads/' . $file['filename']);
        
        // Delete file record from database
        $deleteQuery = "DELETE FROM uploaded_files WHERE id = :file_id";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':file_id', $fileId);
        return $deleteStmt->execute();
    }
    
    return false;
}

function updateFileDetails($conn, $fileId, $userId, $title, $description, $isPublic) {
    $query = "UPDATE uploaded_files 
              SET title = :title, 
                  description = :description, 
                  is_public = :is_public 
              WHERE id = :file_id AND uploaded_by = :user_id";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':is_public', $isPublic, PDO::PARAM_BOOL);
    $stmt->bindParam(':file_id', $fileId);
    $stmt->bindParam(':user_id', $userId);
    
    return $stmt->execute();
}
