<?php
// Initialize the session
session_start();

// Include header file
$includesPath = __DIR__ . '/includes/';
if (file_exists($includesPath . 'header.php')) {
    include $includesPath . 'header.php';
} else {
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Access Denied</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>";
}
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h3 class="mb-0">Access Denied</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Permission Error!</h4>
                        <p>You do not have permission to access this area. This section requires administrator privileges.</p>
                    </div>
                    <p>If you believe this is an error, please contact the system administrator for assistance.</p>
                    
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <a href="index.php" class="btn btn-primary">Return to Home</a>
                        <a href="logout.php" class="btn btn-outline-secondary ms-2">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer file
if (file_exists($includesPath . 'footer.php')) {
    include $includesPath . 'footer.php';
} else {
    echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js'></script>
    </body>
    </html>";
}
?>