<?php
// Include database connection file
include('config/database.php');

// Check if database connection is established
if (!isset($conn) || $conn === null) {
    die("Error: Database connection failed. Please check your database configuration.");
}

// Define variables and initialize with empty values
$token = $email = "";
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = $token_err = "";
$success = false;

// Check if token is in URL
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token exists and is valid
    $sql = "SELECT email, expires FROM password_reset WHERE token = ? AND expires > NOW()";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $email, $expires);
                mysqli_stmt_fetch($stmt);
            } else {
                $token_err = "This password reset link is invalid or has expired.";
            }
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }

        mysqli_stmt_close($stmt); // Close the select statement
    } else {
        echo "Oops! Something went wrong. Please try again later.";
    }
} else {
    // No token provided, redirect to forgot password page
    header("location: forgot_password.php");
    exit;
}

// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($token_err)) {

    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter the new password.";
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input errors before updating the database
    if (empty($new_password_err) && empty($confirm_password_err)) {
        // Update password
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_email);

            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_email = $email;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Delete the token
                $delete_sql = "DELETE FROM password_reset WHERE email = ?";
                if ($delete_stmt = mysqli_prepare($conn, $delete_sql)) {
                    mysqli_stmt_bind_param($delete_stmt, "s", $email);
                    mysqli_stmt_execute($delete_stmt);
                    mysqli_stmt_close($delete_stmt); // Close the delete statement
                }

                // Password updated successfully
                $success = true;
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }

            mysqli_stmt_close($stmt); // Close the update statement
        }
    }

    mysqli_close($conn); // Close the database connection
}
?>

<?php include('includes/header.php'); ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Reset Password</h2>
                </div>
                <div class="card-body">
                    <?php if (!empty($token_err)): ?>
                        <div class="alert alert-danger"><?php echo $token_err; ?></div>
                        <div class="text-center">
                            <a href="forgot_password.php" class="btn btn-primary">Request New Link</a>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="alert alert-success">Your password has been updated successfully!</div>
                        <div class="text-center">
                            <a href="login.php" class="btn btn-primary">Login</a>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Please enter your new password.</p>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?token=" . $token); ?>" method="post">
                            <div class="form-group mb-3">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control <?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>">
                                <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                            </div>
                            <div class="form-group mb-3">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                            </div>
                            <div class="form-group d-grid mt-4">
                                <button type="submit" class="btn btn-primary">Reset Password</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>