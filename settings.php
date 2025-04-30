<?php
// Ensure no whitespace or output before session_start
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

// Process maintenance mode toggle
if (isset($_POST['toggle_maintenance'])) {
    $maintenance_file = 'maintenance.flag';
    if (file_exists($maintenance_file)) {
        unlink($maintenance_file);
        $maintenance_message = "Maintenance mode disabled successfully!";
    } else {
        file_put_contents($maintenance_file, date('Y-m-d H:i:s'));
        $maintenance_message = "Maintenance mode enabled successfully!";
    }
}

// Process cache clearing
if (isset($_POST['clear_cache'])) {
    // Add your cache clearing logic here
    $cache_message = "System cache cleared successfully!";
}

// Define settings categories and their values (mock data - replace with database query)
$generalSettings = [
    'site_name' => 'EduJobs Scholars',
    'site_description' => 'Educational scholarship platform for students',
    'admin_email' => 'admin@edujobs.com',
    'items_per_page' => 25,
    'maintenance_mode' => false
];

$emailSettings = [
    'smtp_server' => 'smtp.example.com',
    'smtp_port' => 587,
    'smtp_username' => 'noreply@edujobs.com',
    'smtp_password' => '********',
    'from_email' => 'noreply@edujobs.com',
    'from_name' => 'EduJobs Scholars',
    'enable_smtp' => true
];

$securitySettings = [
    'login_attempts' => 5,
    'lockout_time' => 15, // minutes
    'password_expiry' => 90, // days
    'session_timeout' => 30, // minutes
    'enable_2fa' => false
];

// Process form submission
$saveSuccess = false;
$saveMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['save_general'])) {
        // In a real app, validate and save to database
        $saveSuccess = true;
        $saveMessage = "General settings saved successfully!";
    } elseif (isset($_POST['save_email'])) {
        // In a real app, validate and save to database
        $saveSuccess = true;
        $saveMessage = "Email settings saved successfully!";
    } elseif (isset($_POST['save_security'])) {
        // In a real app, validate and save to database
        $saveSuccess = true;
        $saveMessage = "Security settings saved successfully!";
    }
}

// Include header file - AFTER all processing
$pageTitle = "System Settings";
require_once "includes/admin_header.php";
?>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Left sidebar with settings navigation -->
        <div class="col-md-3">
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Settings</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#general-settings" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="fas fa-sliders-h me-2"></i> General Settings
                        </a>
                        <a href="#email-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-envelope me-2"></i> Email Settings
                        </a>
                        <a href="#security-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-shield-alt me-2"></i> Security Settings
                        </a>
                        <a href="#appearance-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-palette me-2"></i> Appearance
                        </a>
                        <a href="#maintenance-settings" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="fas fa-tools me-2"></i> Maintenance
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> System Info</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            PHP Version
                            <span class="badge bg-primary rounded-pill"><?php echo phpversion(); ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            MySQL Version
                            <span class="badge bg-primary rounded-pill">5.7.36</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Server
                            <span class="badge bg-primary rounded-pill"><?php echo $_SERVER['SERVER_SOFTWARE']; ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Main content area -->
        <div class="col-md-9">
            <?php if($saveMessage): ?>
                <div class="alert alert-<?php echo $saveSuccess ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo $saveMessage; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="tab-content">
                <!-- General Settings Tab -->
                <div class="tab-pane fade show active" id="general-settings">
                    <div class="card dashboard-card mb-4">
                        <div class="card-header">
                            <h5 class="page-title"><i class="fas fa-sliders-h"></i> General Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="settings.php">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Name</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name" value="<?php echo htmlspecialchars($generalSettings['site_name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Site Description</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="2"><?php echo htmlspecialchars($generalSettings['site_description']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="admin_email" class="form-label">Admin Email</label>
                                    <input type="email" class="form-control" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($generalSettings['admin_email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="items_per_page" class="form-label">Items Per Page</label>
                                    <select class="form-select" id="items_per_page" name="items_per_page">
                                        <option value="10" <?php echo $generalSettings['items_per_page'] == 10 ? 'selected' : ''; ?>>10</option>
                                        <option value="25" <?php echo $generalSettings['items_per_page'] == 25 ? 'selected' : ''; ?>>25</option>
                                        <option value="50" <?php echo $generalSettings['items_per_page'] == 50 ? 'selected' : ''; ?>>50</option>
                                        <option value="100" <?php echo $generalSettings['items_per_page'] == 100 ? 'selected' : ''; ?>>100</option>
                                    </select>
                                </div>
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php echo $generalSettings['maintenance_mode'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="maintenance_mode">Maintenance Mode</label>
                                    <div class="form-text">When enabled, only administrators can access the site.</div>
                                </div>
                                <input type="hidden" name="save_general" value="1">
                                <button type="submit" class="btn btn-primary">Save General Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Email Settings Tab -->
                <div class="tab-pane fade" id="email-settings">
                    <div class="card dashboard-card mb-4">
                        <div class="card-header">
                            <h5 class="page-title"><i class="fas fa-envelope"></i> Email Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="settings.php">
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_smtp" name="enable_smtp" <?php echo $emailSettings['enable_smtp'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_smtp">Enable SMTP</label>
                                    <div class="form-text">Use SMTP for sending emails instead of PHP mail function.</div>
                                </div>
                                <div id="smtp_settings" class="<?php echo $emailSettings['enable_smtp'] ? '' : 'd-none'; ?>">
                                    <div class="mb-3">
                                        <label for="smtp_server" class="form-label">SMTP Server</label>
                                        <input type="text" class="form-control" id="smtp_server" name="smtp_server" value="<?php echo htmlspecialchars($emailSettings['smtp_server']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="smtp_port" class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo $emailSettings['smtp_port']; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="smtp_username" class="form-label">SMTP Username</label>
                                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" value="<?php echo htmlspecialchars($emailSettings['smtp_username']); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="smtp_password" class="form-label">SMTP Password</label>
                                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" value="<?php echo htmlspecialchars($emailSettings['smtp_password']); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="from_email" class="form-label">From Email</label>
                                    <input type="email" class="form-control" id="from_email" name="from_email" value="<?php echo htmlspecialchars($emailSettings['from_email']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="from_name" class="form-label">From Name</label>
                                    <input type="text" class="form-control" id="from_name" name="from_name" value="<?php echo htmlspecialchars($emailSettings['from_name']); ?>" required>
                                </div>
                                <input type="hidden" name="save_email" value="1">
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-primary">Save Email Settings</button>
                                    <button type="button" id="testEmail" class="btn btn-outline-secondary">Send Test Email</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings Tab -->
                <div class="tab-pane fade" id="security-settings">
                    <div class="card dashboard-card mb-4">
                        <div class="card-header">
                            <h5 class="page-title"><i class="fas fa-shield-alt"></i> Security Settings</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="settings.php">
                                <div class="mb-3">
                                    <label for="login_attempts" class="form-label">Max Login Attempts</label>
                                    <input type="number" class="form-control" id="login_attempts" name="login_attempts" value="<?php echo $securitySettings['login_attempts']; ?>" min="1" max="10" required>
                                    <div class="form-text">Number of failed login attempts before account lockout.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="lockout_time" class="form-label">Lockout Time (minutes)</label>
                                    <input type="number" class="form-control" id="lockout_time" name="lockout_time" value="<?php echo $securitySettings['lockout_time']; ?>" min="5" max="60" required>
                                    <div class="form-text">Duration of account lockout after max failed login attempts.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="password_expiry" class="form-label">Password Expiry (days)</label>
                                    <input type="number" class="form-control" id="password_expiry" name="password_expiry" value="<?php echo $securitySettings['password_expiry']; ?>" min="30" max="365" required>
                                    <div class="form-text">Number of days before users are prompted to change password. Use 0 for no expiry.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                                    <input type="number" class="form-control" id="session_timeout" name="session_timeout" value="<?php echo $securitySettings['session_timeout']; ?>" min="5" max="120" required>
                                    <div class="form-text">Duration of inactivity before user is automatically logged out.</div>
                                </div>
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_2fa" name="enable_2fa" <?php echo $securitySettings['enable_2fa'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_2fa">Enable Two-Factor Authentication</label>
                                    <div class="form-text">Require two-factor authentication for all admin users.</div>
                                </div>
                                <input type="hidden" name="save_security" value="1">
                                <button type="submit" class="btn btn-primary">Save Security Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Appearance Settings Tab -->
                <div class="tab-pane fade" id="appearance-settings">
                    <div class="card dashboard-card mb-4">
                        <div class="card-header">
                            <h5 class="page-title"><i class="fas fa-palette"></i> Appearance Settings</h5>
                        </div>
                        <div class="card-body">
                            <form id="appearanceForm">
                                <div class="mb-3">
                                    <label for="theme_color" class="form-label">Primary Theme Color</label>
                                    <input type="color" class="form-control form-control-color" id="theme_color" value="#0d6efd">
                                </div>
                                <div class="mb-3">
                                    <label for="secondary_color" class="form-label">Secondary Theme Color</label>
                                    <input type="color" class="form-control form-control-color" id="secondary_color" value="#6c757d">
                                </div>
                                <div class="mb-3">
                                    <label for="logo_upload" class="form-label">Site Logo</label>
                                    <input class="form-control" type="file" id="logo_upload">
                                    <div class="form-text">Recommended size: 200x50 pixels. Formats: PNG, JPG, SVG</div>
                                </div>
                                <div class="mb-3">
                                    <label for="favicon_upload" class="form-label">Favicon</label>
                                    <input class="form-control" type="file" id="favicon_upload">
                                    <div class="form-text">Recommended size: 32x32 pixels. Format: ICO, PNG</div>
                                </div>
                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_dark_mode" checked>
                                    <label class="form-check-label" for="enable_dark_mode">Allow Dark Mode Toggle</label>
                                </div>
                                <button type="submit" class="btn btn-primary">Save Appearance Settings</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Maintenance Settings Tab -->
                <div class="tab-pane fade" id="maintenance-settings">
                    <div class="card dashboard-card mb-4">
                        <div class="card-header">
                            <h5 class="page-title"><i class="fas fa-tools"></i> Maintenance</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-primary">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-trash-alt me-2"></i>Clear Cache</h5>
                                            <p class="card-text">Clear the system cache to refresh data and potentially fix issues.</p>
                                            <form method="post" action="settings.php">
                                                <input type="hidden" name="clear_cache" value="1">
                                                <button type="submit" class="btn btn-primary">Clear Cache</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-database me-2"></i>Database Backup</h5>
                                            <p class="card-text">Create a backup of your database to prevent data loss.</p>
                                            <a href="backup_database.php" class="btn btn-warning">Backup Database</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-success">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-file-alt me-2"></i>System Logs</h5>
                                            <p class="card-text">View system logs to troubleshoot issues and monitor activity.</p>
                                            <a href="view_logs.php" class="btn btn-success">View Logs</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-danger">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Maintenance Mode</h5>
                                            <p class="card-text">Put the site in maintenance mode to perform updates.</p>
                                            <button type="button" id="toggleMaintenance" class="btn btn-danger">
                                                <?php echo $generalSettings['maintenance_mode'] ? 'Disable' : 'Enable'; ?> Maintenance Mode
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle SMTP settings visibility
document.getElementById('enable_smtp').addEventListener('change', function() {
    document.getElementById('smtp_settings').classList.toggle('d-none', !this.checked);
});

// Test email AJAX
document.getElementById('testEmail').addEventListener('click', function() {
    // Collect email settings
    const emailSettings = {
        enable_smtp: document.getElementById('enable_smtp').checked,
        smtp_server: document.getElementById('smtp_server').value,
        smtp_port: document.getElementById('smtp_port').value,
        smtp_username: document.getElementById('smtp_username').value,
        smtp_password: document.getElementById('smtp_password').value,
        from_email: document.getElementById('from_email').value,
        from_name: document.getElementById('from_name').value
    };
    
    // Send test email via AJAX
    fetch('send_test_email.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(emailSettings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test email sent successfully!');
        } else {
            alert('Failed to send test email: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending test email!');
    });
});

// Appearance settings AJAX
document.getElementById('appearanceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('theme_color', document.getElementById('theme_color').value);
    formData.append('secondary_color', document.getElementById('secondary_color').value);
    formData.append('enable_dark_mode', document.getElementById('enable_dark_mode').checked);
    
    if (document.getElementById('logo_upload').files.length > 0) {
        formData.append('logo', document.getElementById('logo_upload').files[0]);
    }
    
    if (document.getElementById('favicon_upload').files.length > 0) {
        formData.append('favicon', document.getElementById('favicon_upload').files[0]);
    }
    
    fetch('update_appearance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Appearance settings saved successfully!');
        } else {
            alert('Failed to save appearance settings!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving appearance settings!');
    });
});

// Toggle maintenance mode AJAX
document.getElementById('toggleMaintenance').addEventListener('click', function() {
    const currentState = <?php echo $generalSettings['maintenance_mode'] ? 'true' : 'false'; ?>;
    
    fetch('toggle_maintenance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ enable: !currentState })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Maintenance mode ' + (data.enabled ? 'enabled' : 'disabled') + ' successfully!');
            this.textContent = data.enabled ? 'Disable Maintenance Mode' : 'Enable Maintenance Mode';
        } else {
            alert('Failed to toggle maintenance mode!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while toggling maintenance mode!');
    });
});
</script>

<?php require_once "includes/admin_footer.php"; ?> 