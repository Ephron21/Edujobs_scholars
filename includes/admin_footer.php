    </main>
    <!-- End of Main Content Container -->

    <!-- Footer -->
    <footer class="bg-light py-3 mt-auto">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> EduJobs Scholars - Admin Dashboard</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">Version 2.5.3</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (for some plugins that require it) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Chart.js for dashboard charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    
    <!-- Custom Admin Dashboard JavaScript -->
    <script src="public/js/admin_dashboard.js"></script>
    
    <!-- Additional page-specific JavaScript -->
    <?php if (isset($extraJS)): ?>
        <?php foreach ($extraJS as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Inline page-specific JavaScript -->
    <?php if (isset($inlineJS)): ?>
        <script>
            <?php echo $inlineJS; ?>
        </script>
    <?php endif; ?>

    <!-- AJAX Files List Loader -->
    <script>
        // Function to load files list via AJAX
        function refreshFileList() {
            const filesList = document.getElementById('filesList');
            if (filesList) {
                fetch('list_files.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success' && data.files && data.files.length > 0) {
                            let html = '';
                            data.files.forEach(file => {
                                const fileIcon = getFileIconClass(file.type);
                                html += `
                                    <tr>
                                        <td>
                                            <i class="fas ${fileIcon} me-2"></i>
                                            ${file.title || file.name}
                                        </td>
                                        <td>${file.type}</td>
                                        <td>${formatFileSize(file.size)}</td>
                                        <td>${file.uploaded_at}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="download.php?id=${file.id}" class="btn btn-outline-primary">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" onclick="deleteFile(${file.id})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                            });
                            filesList.innerHTML = html;
                        } else {
                            filesList.innerHTML = `
                                <tr>
                                    <td colspan="5" class="text-center">No files found</td>
                                </tr>
                            `;
                        }
                    })
                    .catch(error => {
                        filesList.innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center text-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Error loading files: ${error.message}
                                </td>
                            </tr>
                        `;
                    });
            }
        }

        // Helper function to get appropriate icon based on file type
        function getFileIconClass(fileType) {
            if (!fileType) return 'fa-file';
            
            fileType = fileType.toLowerCase();
            
            if (fileType.includes('image')) return 'fa-file-image';
            if (fileType.includes('pdf')) return 'fa-file-pdf';
            if (fileType.includes('word') || fileType.includes('doc')) return 'fa-file-word';
            if (fileType.includes('excel') || fileType.includes('spreadsheet') || fileType.includes('csv')) return 'fa-file-excel';
            if (fileType.includes('powerpoint') || fileType.includes('presentation')) return 'fa-file-powerpoint';
            if (fileType.includes('video')) return 'fa-file-video';
            if (fileType.includes('audio')) return 'fa-file-audio';
            if (fileType.includes('zip') || fileType.includes('compressed')) return 'fa-file-archive';
            if (fileType.includes('text')) return 'fa-file-alt';
            
            return 'fa-file';
        }

        // Helper function to format file size
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Function to delete a file
        function deleteFile(fileId) {
            if (confirm('Are you sure you want to delete this file?')) {
                fetch('delete_file.php?id=' + fileId, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        refreshFileList();
                        alert('File deleted successfully!');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                });
            }
        }

        // Initial load of files list
        document.addEventListener('DOMContentLoaded', function() {
            refreshFileList();
        });
    </script>
</body>
</html> 