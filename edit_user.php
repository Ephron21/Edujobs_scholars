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

// Check if user ID is provided
if (!isset($_GET["id"]) || empty(trim($_GET["id"]))) {
    header("location: manage_users.php");
    exit;
}

$id = trim($_GET["id"]);

// Fetch user data
$sql = "SELECT id, firstname, lastname, email, user_type FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            $firstname = $row["firstname"];
            $lastname = $row["lastname"];
            $email = $row["email"];
            $user_type = $row["user_type"];
        } else {
            header("location: manage_users.php");
            exit;
        }
    } else {
        $error_message = "Error fetching user data.";
    }
    $stmt->close();
} else {
    $error_message = "Error preparing statement.";
}

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

    // Validate user type
    if (empty(trim($_POST["user_type"]))) {
        $error_message = "Please select a user type.";
    } else {
        $user_type = trim($_POST["user_type"]);
    }

    // If no errors, update the database
    if (empty($error_message)) {
        $sql = "UPDATE users SET firstname = ?, lastname = ?, email = ?, user_type = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssi", $firstname, $lastname, $email, $user_type, $id);
            if ($stmt->execute()) {
                $success_message = "User updated successfully!";
            } else {
                $error_message = "Error updating user: " . $stmt->error;
            }
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
                    <h3>Edit User</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <form action="edit_user.php?id=<?php echo $id; ?>" method="post">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" name="firstname" class="form-control" value="<?php echo $firstname; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" name="lastname" class="form-control" value="<?php echo $lastname; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo $email; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="user_type">User Type</label>
                            <select name="user_type" class="form-control" required>
                                <option value="admin" <?php echo ($user_type == "admin") ? "selected" : ""; ?>>Admin</option>
                                <option value="user" <?php echo ($user_type == "user") ? "selected" : ""; ?>>User</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Update User</button>
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