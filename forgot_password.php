<?php
// Enable error reporting for debugging (Remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection file
require_once('config/database.php');

// Check if database connection is established
if (!isset($conn) || $conn === null) {
    die("Error: Database connection failed. Please check your database configuration.");
}

// Initialize variables
$email = "";
$email_err = "";
$success_msg = "";

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email address.";
    } else {
        $email = trim($_POST["email"]);

        // Check if email exists in the database
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Email exists, generate a token
                    $token = bin2hex(random_bytes(50));
                    $expires = date("Y-m-d H:i:s", strtotime("+1 hour")); // Token expires in 1 hour

                    // Delete existing reset requests (if any) for this email
                    $delete_sql = "DELETE FROM password_reset WHERE email = ?";
                    $delete_stmt = mysqli_prepare($conn, $delete_sql);
                    mysqli_stmt_bind_param($delete_stmt, "s", $email);
                    mysqli_stmt_execute($delete_stmt);
                    mysqli_stmt_close($delete_stmt);

                    // Insert new token into password_reset table
                    $insert_sql = "INSERT INTO password_reset (email, token, expires) VALUES (?, ?, ?)";
                    if ($insert_stmt = mysqli_prepare($conn, $insert_sql)) {
                        mysqli_stmt_bind_param($insert_stmt, "sss", $email, $token, $expires);

                        if (mysqli_stmt_execute($insert_stmt)) {
                            // Construct reset link
                            $reset_link = "http://localhost/registration_system/reset_password.php?token=" . $token;

                            // Simulate email sending (Replace this with actual email functionality)
                            $success_msg = "Password reset link has been sent to your email: <a href='$reset_link'>$reset_link</a>";
                        } else {
                            $email_err = "Failed to insert reset token. Please try again later.";
                        }

                        mysqli_stmt_close($insert_stmt);
                    }
                } else {
                    $email_err = "No account found with that email address.";
                }
            } else {
                $email_err = "Database error. Please try again later.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $email_err = "Database query failed.";
        }
    }

    mysqli_close($conn);
}
?>

<?php include('includes/header.php'); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Forgot Password</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($success_msg)): ?>
                        <div class="alert alert-success"><?php echo $success_msg; ?></div>
                    <?php else: ?>
                        <p class="text-center">Enter your email address to reset your password.</p>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="form-group mb-3">
                                <label>Email Address</label>
                                <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                                <span class="invalid-feedback"><?php echo $email_err; ?></span>
                            </div>
                            <div class="form-group d-grid mt-4">
                                <button type="submit" class="btn btn-primary">Send Reset Link</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>
