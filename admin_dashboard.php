<?php
// Initialize the session
session_start();
// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
// Check if the user is an admin
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    // Not an admin, redirect to access denied page
    header("location: access_denied.php");
    exit;
}
// Include header file
$includesPath = __DIR__ . '/includes/';
if (file_exists($includesPath . 'header.php')) {
    include $includesPath . 'header.php';
}

// Get admin information (mock data - replace with actual database query)
$adminInfo = [
    'name' => isset($_SESSION["username"]) ? $_SESSION["username"] : 'Admin User',
    'email' => isset($_SESSION["email"]) ? $_SESSION["email"] : 'admin@example.com',
    'avatar' => 'assets/img/admin-avatar.jpg', // Default path - replace with actual path
    'lastLogin' => date('Y-m-d H:i:s', isset($_SESSION["last_login"]) ? $_SESSION["last_login"] : time()),
    'role' => 'System Administrator'
];

// Mock statistics (replace with actual database queries)
$totalUsers = 245;
$newUsersToday = 12;
$pendingApplicants = 38;
$systemAlerts = 5;
?>

<!-- Embedded CSS -->
<style>
    /* Dashboard specific styles */
    .dashboard-card {
        transition: transform 0.3s, box-shadow 0.3s;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 20px;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
    }
    
    .dashboard-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color: #3498db;
    }
    
    .stats-card {
        border-left: 4px solid;
        transition: all 0.3s;
    }
    
    .stats-card:hover {
        transform: scale(1.03);
    }
    
    .stats-card.primary {
        border-left-color: #3498db;
    }
    
    .stats-card.success {
        border-left-color: #2ecc71;
    }
    
    .stats-card.warning {
        border-left-color: #f39c12;
    }
    
    .stats-card.danger {
        border-left-color: #e74c3c;
    }
    
    .admin-profile {
        padding: 20px;
        background: linear-gradient(135deg, #3498db, #1abc9c);
        color: white;
        border-radius: 8px;
        position: relative;
        overflow: hidden;
    }
    
    .admin-profile::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: url('assets/img/pattern.svg');
        opacity: 0.1;
    }
    
    .profile-img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 3px solid white;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .profile-img:hover {
        transform: scale(1.1);
    }
    
    .welcome-text {
        animation: fadeInUp 1s ease-out;
    }
    
    .stat-number {
        font-size: 1.8rem;
        font-weight: bold;
        color: #34495e;
    }
    
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
    }
    
    .activity-item {
        padding: 12px 0;
        border-bottom: 1px solid #eee;
        transition: background-color 0.3s;
    }
    
    .activity-item:hover {
        background-color: #f8f9fa;
    }
    
    .activity-item:last-child {
        border-bottom: none;
    }
    
    .activity-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }
    
    .dot-success {
        background-color: #2ecc71;
    }
    
    .dot-warning {
        background-color: #f39c12;
    }
    
    .dot-danger {
        background-color: #e74c3c;
    }
    
    .dot-info {
        background-color: #3498db;
    }
    
    .typed-text {
        color: #3498db;
        font-weight: bold;
    }
    
    .chart-container {
        position: relative;
        height: 250px;
        margin-bottom: 20px;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }
    
    .menu-card {
        cursor: pointer;
        animation: fadeIn 0.8s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .system-alert {
        animation: pulse 2s infinite;
    }
    
    .page-title {
        position: relative;
        display: inline-block;
        margin-bottom: 20px;
    }
    
    .page-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 0;
        width: 50px;
        height: 3px;
        background-color: #3498db;
        transition: width 0.3s;
    }
    
    .page-title:hover::after {
        width: 100%;
    }
</style>

<div class="container-fluid mt-4">
    <!-- Welcome Banner with Typing Effect -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h2 class="welcome-text">Welcome back, <span class="typed-text" id="adminName"></span></h2>
                    <p class="text-muted">Today is <?php echo date('l, F j, Y'); ?> | Last login: <?php echo $adminInfo['lastLogin']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Left sidebar with admin profile -->
        <div class="col-md-3">
            <div class="admin-profile mb-4">
                <div class="text-center mb-3">
                    <img src="<?php echo $adminInfo['avatar']; ?>" alt="Admin Profile" class="profile-img mb-3" onerror="this.src='https://via.placeholder.com/100x100'">
                    <h4><?php echo $adminInfo['name']; ?></h4>
                    <p><?php echo $adminInfo['role']; ?></p>
                </div>
                <div class="d-grid gap-2">
                    <a href="profile.php" class="btn btn-light btn-sm"><i class="fas fa-user-cog"></i> Edit Profile</a>
                    <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <!-- Quick Navigation Menu -->
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-compass"></i> Quick Navigation</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="dashboard.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a href="manage_users.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i> User Management
                        </a>
                        <a href="view_applicants.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-graduate me-2"></i> Applicants
                        </a>
                        <a href="reports.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar me-2"></i> Reports
                        </a>
                        <a href="settings.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-cogs me-2"></i> System Settings
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- System Alerts -->
            <div class="card dashboard-card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="fas fa-bell"></i> System Alerts</h5>
                </div>
                <div class="card-body">
                    <div class="system-alert">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong><?php echo $systemAlerts; ?> alerts</strong> require your attention
                        </div>
                    </div>
                    <div class="d-grid">
                        <a href="alerts.php" class="btn btn-outline-danger">View All Alerts</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-md-9">
            <!-- Statistics Row -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stats-card primary dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Total Users</h6>
                                    <div class="stat-number"><?php echo $totalUsers; ?></div>
                                </div>
                                <div class="dashboard-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card success dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">New Today</h6>
                                    <div class="stat-number"><?php echo $newUsersToday; ?></div>
                                </div>
                                <div class="dashboard-icon">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 40%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card warning dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Pending</h6>
                                    <div class="stat-number"><?php echo $pendingApplicants; ?></div>
                                </div>
                                <div class="dashboard-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 60%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stats-card danger dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted">Alerts</h6>
                                    <div class="stat-number"><?php echo $systemAlerts; ?></div>
                                </div>
                                <div class="dashboard-icon">
                                    <i class="fas fa-bell"></i>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 25%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="page-title">User Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="activityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="page-title">User Types</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="userTypesChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Cards Row -->
            <div class="row">
                <!-- User Management Card -->
                <div class="col-md-6">
                    <div class="card dashboard-card menu-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white p-3 rounded me-3">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                                <div>
                                    <h4 class="card-title">User Management</h4>
                                    <p class="card-text text-muted">Manage system users with admin privileges</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="manage_users.php" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i> View Users
                                </a>
                                <a href="add_user.php" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i> Add User
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Applicant Management Card -->
                <div class="col-md-6">
                    <div class="card dashboard-card menu-card">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success text-white p-3 rounded me-3">
                                    <i class="fas fa-user-graduate fa-2x"></i>
                                </div>
                                <div>
                                    <h4 class="card-title">Applicant Management</h4>
                                    <p class="card-text text-muted">Manage applicant records and their documents</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="view_applicants.php" class="btn btn-success">
                                    <i class="fas fa-eye me-1"></i> View Applicants
                                </a>
                                <a href="export_applicants.php" class="btn btn-outline-success">
                                    <i class="fas fa-file-export me-1"></i> Export Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <?php
// Add this to your existing admin_dashboard.php
// Ensure you have proper session and admin authentication at the top of the file

// Add this modal to your HTML
?>
<!-- File Upload Modal -->
<div class="modal fade" id="fileUploadModal" tabindex="-1" aria-labelledby="fileUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileUploadModalLabel">Upload New File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fileUploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label">Choose File</label>
                        <input class="form-control" type="file" id="fileUpload" name="file" required>
                        <div class="form-text">Max file size: 50MB. Allowed types: images, PDFs, Word docs, videos</div>
                    </div>
                    <div class="mb-3">
                        <label for="fileTitle" class="form-label">File Title</label>
                        <input type="text" class="form-control" id="fileTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="fileDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="fileDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="isPublic" name="is_public">
                        <label class="form-check-label" for="isPublic">Make file publicly visible</label>
                    </div>
                    <div id="uploadFeedback" class="mt-3"></div>
                    <button type="submit" class="btn btn-primary">Upload File</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for File Upload -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileUploadForm = document.getElementById('fileUploadForm');
    const uploadFeedback = document.getElementById('uploadFeedback');

    fileUploadForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(fileUploadForm);

        fetch('file_upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                uploadFeedback.innerHTML = `
                    <div class="alert alert-success">
                        ${data.message}
                    </div>
                `;
                // Reset form and close modal
                fileUploadForm.reset();
                
                // Optional: Refresh file list or show uploaded file
                // You might want to add a function to update file list dynamically
            } else {
                uploadFeedback.innerHTML = `
                    <div class="alert alert-danger">
                        ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            uploadFeedback.innerHTML = `
                <div class="alert alert-danger">
                    An error occurred: ${error.message}
                </div>
            `;
        });
    });
});
</script>

<!-- Add this button to your dashboard layout -->
<div class="container">
    <div class="row mb-3">
        <div class="col-12">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#fileUploadModal">
                <i class="fas fa-upload me-2"></i>Upload New File
            </button>
        </div>
    </div>
</div>

            
            <!-- Recent Activity and System Information Row -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="page-title">Recent Activity</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="activity-item p-3">
                                <span class="activity-dot dot-success"></span>
                                <strong>John Doe</strong> created a new account
                                <small class="text-muted float-end">2 mins ago</small>
                            </div>
                            <div class="activity-item p-3">
                                <span class="activity-dot dot-info"></span>
                                <strong>Sarah Smith</strong> updated applicant status
                                <small class="text-muted float-end">45 mins ago</small>
                            </div>
                            <div class="activity-item p-3">
                                <span class="activity-dot dot-warning"></span>
                                <strong>System</strong> backup completed
                                <small class="text-muted float-end">1 hour ago</small>
                            </div>
                            <div class="activity-item p-3">
                                <span class="activity-dot dot-danger"></span>
                                <strong>Admin</strong> deleted user account
                                <small class="text-muted float-end">2 hours ago</small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="activity_log.php" class="btn btn-sm btn-outline-primary w-100">View All Activity</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card dashboard-card">
                        <div class="card-header">
                            <h5 class="page-title">System Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Server Status:</span>
                                <span class="badge bg-success">Online</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Database:</span>
                                <span class="badge bg-info">Healthy</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Storage:</span>
                                <div class="progress w-50" style="height: 8px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span>75%</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Last Backup:</span>
                                <span>March 2, 2025 05:30 AM</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>System Version:</span>
                                <span>v2.5.3</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript Section -->
<script>
// Typing effect for admin name
document.addEventListener('DOMContentLoaded', function() {
    const adminName = "<?php echo $adminInfo['name']; ?>";
    let i = 0;
    const speed = 100; // typing speed
    const nameElement = document.getElementById('adminName');
    
    function typeWriter() {
        if (i < adminName.length) {
            nameElement.innerHTML += adminName.charAt(i);
            i++;
            setTimeout(typeWriter, speed);
        }
    }
    
    typeWriter();
    
    // Initialize charts
    initCharts();
    
    // Add hover animations to menu cards
    const menuCards = document.querySelectorAll('.menu-card');
    menuCards.forEach(card => {
        card.addEventListener('mouseover', function() {
            this.classList.add('shadow-lg');
        });
        
        card.addEventListener('mouseout', function() {
            this.classList.remove('shadow-lg');
        });
    });
});

// Function to initialize charts
function initCharts() {
    // Load Chart.js from CDN if not already loaded
    if (typeof Chart === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js';
        script.onload = createCharts;
        document.head.appendChild(script);
    } else {
        createCharts();
    }
}

function createCharts() {
    // User activity chart (line chart)
    const activityCtx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Users',
                data: [65, 59, 80, 81, 56, 55],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Active Users',
                data: [28, 48, 40, 19, 86, 27],
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            }
        }
    });

    // User types chart (doughnut chart)
    const userTypesCtx = document.getElementById('userTypesChart').getContext('2d');
    const userTypesChart = new Chart(userTypesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Admin', 'Staff', 'Applicants', 'Guests'],
            datasets: [{
                data: [15, 30, 45, 10],
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#f39c12',
                    '#e74c3c'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            animation: {
                animateRotate: true,
                animateScale: true,
                duration: 2000,
                easing: 'easeOutBounce'
            }
        }
    });
}
</script>

<?php
// Include footer file
if (file_exists($includesPath . 'footer.php')) {
    include $includesPath . 'footer.php';
}
?>