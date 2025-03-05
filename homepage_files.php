<?php
// homepage_files.php
require_once 'config.php';

function getPublicFiles($limit = 10) {
    global $conn;
    
    $query = "SELECT id, title, description, original_name, file_type, file_path, created_at 
              FROM file_uploads 
              WHERE is_public = 1 
              ORDER BY created_at DESC 
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $files = [];
    
    while ($row = $result->fetch_assoc()) {
        // Determine file icon based on file type
        $fileIcon = 'fa-file';
        if (strpos($row['file_type'], 'image/') === 0) {
            $fileIcon = 'fa-file-image';
        } elseif ($row['file_type'] === 'application/pdf') {
            $fileIcon = 'fa-file-pdf';
        } elseif (strpos($row['file_type'], 'video/') === 0) {
            $fileIcon = 'fa-file-video';
        } elseif (strpos($row['file_type'], 'application/msword') === 0 || 
                   strpos($row['file_type'], 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') === 0) {
            $fileIcon = 'fa-file-word';
        }
        
        $row['file_icon'] = $fileIcon;
        $files[] = $row;
    }
    
    $stmt->close();
    return $files;
}

// Function to display public files on homepage
function displayPublicFiles() {
    $publicFiles = getPublicFiles();
    
    if (empty($publicFiles)) {
        echo '<div class="container mt-4">';
        echo '<div class="alert alert-info">No public files available.</div>';
        echo '</div>';
        return;
    }
    
    echo '<section class="container mt-4">';
    echo '<h2 class="mb-4">Public Files</h2>';
    echo '<div class="row">';
    
    foreach ($publicFiles as $file) {
        echo '<div class="col-md-4 mb-4">';
        echo '<div class="card h-100">';
        echo '<div class="card-body">';
        echo '<h5 class="card-title">';
        echo '<i class="fas ' . htmlspecialchars($file['file_icon']) . ' me-2"></i>';
        echo htmlspecialchars($file['title']);
        echo '</h5>';
        echo '<p class="card-text">' . htmlspecialchars($file['description']) . '</p>';
        echo '</div>';
        echo '<div class="card-footer d-flex justify-content-between align-items-center">';
        echo '<small class="text-muted">' . date('F j, Y', strtotime($file['created_at'])) . '</small>';
        
        // Download button (only for logged-in users)
        if (isset($_SESSION['loggedin'])) {
            echo '<a href="download.php?file_id=' . $file['id'] . '" class="btn btn-sm btn-primary">';
            echo '<i class="fas fa-download me-1"></i>Download</a>';
        }
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
    echo '</section>';
}

// Add this to your homepage template (e.g., index.php)
// <?php displayPublicFiles(); ?>
?>