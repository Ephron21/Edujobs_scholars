<?php
session_start();

// Simulate admin login for testing
$_SESSION["loggedin"] = true;
$_SESSION["is_admin"] = true;
$_SESSION["admin_id"] = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test File Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Test File Upload</h2>
        <div id="alert" class="alert" style="display: none;"></div>
        
        <form id="uploadForm" class="mt-4">
            <div class="mb-3">
                <label for="file" class="form-label">Choose File</label>
                <input type="file" class="form-control" id="file" name="file" required>
                <div class="form-text">Max file size: 50MB. Allowed types: images, PDFs, Word docs, videos</div>
            </div>
            
            <div class="mb-3">
                <label for="title" class="form-label">File Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1">
                <label class="form-check-label" for="is_public">Make file publicly visible</label>
            </div>
            
            <button type="submit" class="btn btn-primary">Upload File</button>
        </form>
        
        <div class="progress mt-3" style="display: none;">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
    </div>

    <script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const progressBar = document.querySelector('.progress-bar');
        const progress = document.querySelector('.progress');
        const alert = document.getElementById('alert');
        
        // Show progress bar
        progress.style.display = 'block';
        
        // Reset alert
        alert.style.display = 'none';
        alert.className = 'alert';
        
        fetch('upload.php', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Show result
            alert.style.display = 'block';
            alert.textContent = data.message;
            alert.className = `alert alert-${data.status === 'success' ? 'success' : 'danger'}`;
            
            if (data.status === 'success') {
                // Reset form on success
                this.reset();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert.style.display = 'block';
            alert.textContent = 'An error occurred during upload';
            alert.className = 'alert alert-danger';
        })
        .finally(() => {
            // Hide progress bar
            progress.style.display = 'none';
            progressBar.style.width = '0%';
        });
    });
    </script>
</body>
</html> 