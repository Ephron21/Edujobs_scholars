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

// Include database connection
include('config/database.php');

// Define variables and initialize with empty values
$firstname = $lastname = $email = $password = $user_type = $phone = "";
$error_message = $success_message = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate first name
    if (empty(trim($_POST["firstname"]))) {
        $error_message = "Please enter a first name.";
    } else {
        $firstname = trim($_POST["firstname"]);
    }

    // Validate last name
    if (empty(trim($_POST["lastname"]))) {
        $error_message = "Please enter a last name.";
    } else {
        $lastname = trim($_POST["lastname"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $error_message = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $error_message = "Please enter a password.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $error_message = "Please enter a phone number.";
    } else {
        $phone = trim($_POST["phone"]);
    }

    // Validate user type
    if (empty(trim($_POST["user_type"]))) {
        $error_message = "Please select a user type.";
    } else {
        $user_type = trim($_POST["user_type"]);
    }

    // If no errors, insert into database
    if (empty($error_message)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Prepare an insert statement
        $sql = "INSERT INTO users (firstname, lastname, email, password, user_type, phone) VALUES (?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("ssssss", $firstname, $lastname, $email, $hashed_password, $user_type, $phone);

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                $success_message = "User added successfully!";
            } else {
                $error_message = "Error adding user: " . $stmt->error;
            }

            // Close statement
            $stmt->close();
        } else {
            $error_message = "Error preparing statement: " . $conn->error;
        }
    }
}

// Include header
include('includes/header.php');
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Add New User</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <form action="add_user.php" method="post">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" name="firstname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" name="lastname" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="user_type">User Type</label>
                            <select name="user_type" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Add User</button>
                            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include('includes/footer.php');
?>