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

// Include database connection file
include('config/database.php');

// Check if database connection is established
if (!isset($conn) || $conn === null) {
    die("Error: Database connection failed. Please check your database configuration.");
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Sanitize input
    
    if ($delete_id > 0) {
        // Don't allow deletion of the current user
        if ($_SESSION['id'] != $delete_id) {
            // Prepare a statement to prevent SQL injection
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $delete_id);
            
            if ($stmt->execute()) {
                $success_message = "User deleted successfully.";
            } else {
                $error_message = "Error deleting user: " . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $error_message = "You cannot delete your own account while logged in.";
        }
    } else {
        $error_message = "Invalid user ID.";
    }
}

// Get users data
$sql = "SELECT id, firstname, lastname, email, user_type, created_at FROM users";
$result = mysqli_query($conn, $sql);

// Include header
include('includes/header.php');
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <h3>User Management</h3>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="add_user.php" class="btn btn-primary">Add New User</a>
                            <a href="admin_dashboard.php" class="btn btn-secondary ms-2">Back to Dashboard</a>
                            <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>User Type</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . $row["id"] . "</td>";
                                        echo "<td>" . htmlspecialchars($row["firstname"]) . " " . htmlspecialchars($row["lastname"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["user_type"]) . "</td>";
                                        echo "<td>" . htmlspecialchars($row["created_at"]) . "</td>";
                                        echo "<td>
                                                <a href='edit_user.php?id=" . $row["id"] . "' class='btn btn-sm btn-primary'>Edit</a>
                                                <a href='manage_users.php?delete_id=" . $row["id"] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>
                                            </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>No users found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Close connection
mysqli_close($conn);

// Include footer
include('includes/footer.php');
?>