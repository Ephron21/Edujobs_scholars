<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin Dashboard'; ?> - EduJobs Scholars</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom Admin Dashboard CSS -->
    <link rel="stylesheet" href="public/css/admin_dashboard.css">
    
    <!-- Additional page-specific CSS -->
    <?php if (isset($extraCSS)): ?>
        <?php foreach ($extraCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="admin_dashboard.php">
                <i class="fas fa-graduation-cap me-2"></i>
                EduJobs Scholars Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarAdmin">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_users.php"><i class="fas fa-users me-1"></i> Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_students.php"><i class="fas fa-user-graduate me-1"></i> Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_applicants.php"><i class="fas fa-user-check me-1"></i> Applicants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="file_upload.php"><i class="fas fa-file-upload me-1"></i> Files</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="view_consultations.php"><i class="fas fa-comment-dots me-1"></i> Consultations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php"><i class="fas fa-cogs me-1"></i> Settings</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav ms-auto">
                    <!-- Notifications Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <?php if (isset($systemAlerts) && $systemAlerts > 0): ?>
                                <span class="notification-badge"><?php echo $systemAlerts; ?></span>
                            <?php endif; ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-dropdown" id="notificationDropdown" aria-labelledby="notificationsDropdown">
                            <div class="notification-header">
                                <h6>Notifications</h6>
                                <a href="#" class="mark-all-read">Mark all as read</a>
                            </div>
                            <div class="notification-list">
                                <a href="#" class="dropdown-item notification-item unread">
                                    <div class="notification-icon bg-primary">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h6 class="notification-title">New User Registration</h6>
                                        <p class="notification-text">John Doe registered a new account</p>
                                        <span class="notification-time">2 mins ago</span>
                                    </div>
                                </a>
                                <a href="#" class="dropdown-item notification-item">
                                    <div class="notification-icon bg-success">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h6 class="notification-title">System Update</h6>
                                        <p class="notification-text">System successfully updated to v2.5.3</p>
                                        <span class="notification-time">1 hour ago</span>
                                    </div>
                                </a>
                                <a href="#" class="dropdown-item notification-item unread">
                                    <div class="notification-icon bg-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h6 class="notification-title">Storage Warning</h6>
                                        <p class="notification-text">Server storage is almost full (85%)</p>
                                        <span class="notification-time">3 hours ago</span>
                                    </div>
                                </a>
                                <a href="#" class="dropdown-item notification-item">
                                    <div class="notification-icon bg-danger">
                                        <i class="fas fa-times-circle"></i>
                                    </div>
                                    <div class="notification-content">
                                        <h6 class="notification-title">Login Failed</h6>
                                        <p class="notification-text">Multiple failed login attempts from IP 192.168.1.1</p>
                                        <span class="notification-time">5 hours ago</span>
                                    </div>
                                </a>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="notifications.php" class="dropdown-item text-center">
                                View all notifications
                            </a>
                        </div>
                    </li>
                    
                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar">
                                <img src="<?php echo isset($adminInfo['avatar']) ? $adminInfo['avatar'] : 'assets/img/admin-avatar.jpg'; ?>" alt="Admin" class="rounded-circle" width="32" height="32" onerror="this.src='https://via.placeholder.com/32x32'">
                                <span class="status-indicator status-online"></span>
                            </div>
                            <span class="d-none d-md-inline-block ms-1"><?php echo isset($adminInfo['name']) ? $adminInfo['name'] : 'Admin User'; ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><a class="dropdown-item" href="activity_log.php"><i class="fas fa-list me-2"></i> Activity Log</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb navigation -->
    <div class="bg-light py-2">
        <div class="container-fluid">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="admin_dashboard.php">Home</a></li>
                    <?php if (isset($breadcrumbs)): ?>
                        <?php foreach ($breadcrumbs as $link => $title): ?>
                            <?php if ($link === '#'): ?>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo $title; ?></li>
                            <?php else: ?>
                                <li class="breadcrumb-item"><a href="<?php echo $link; ?>"><?php echo $title; ?></a></li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></li>
                    <?php endif; ?>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Content Container -->
    <main class="py-3">
        <!-- Each page's content will be inserted here -->
    </main>
</body>
</html> 