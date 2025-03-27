<?php
// Start the session to maintain login state
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION['admin_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit();
}

// Include database connection
require_once 'config/database.php';

// Handle status updates if provided
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Valid statuses
    $validStatuses = ['pending', 'contacted', 'completed', 'cancelled'];
    
    if (in_array($action, $validStatuses)) {
        // Update the status in the database
        $stmt = $conn->prepare("UPDATE consultation_requests SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $action, $id);
        
        if ($stmt->execute()) {
            $statusMessage = "Status updated successfully!";
            $statusType = "success";
        } else {
            $statusMessage = "Error updating status: " . $stmt->error;
            $statusType = "danger";
        }
    } elseif ($action === 'delete') {
        // Delete the consultation request
        $stmt = $conn->prepare("DELETE FROM consultation_requests WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $statusMessage = "Consultation request deleted successfully!";
            $statusType = "success";
        } else {
            $statusMessage = "Error deleting request: " . $stmt->error;
            $statusType = "danger";
        }
    }
}

// Get all consultation requests from the database
$query = "SELECT * FROM consultation_requests ORDER BY created_at DESC";
$result = $conn->query($query);

// Include admin header
include_once 'includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h1 class="mt-4">Consultation Requests</h1>
            <p>View and manage all consultation requests from users.</p>
            
            <?php if (isset($statusMessage)): ?>
            <div class="alert alert-<?php echo $statusType; ?> alert-dismissible fade show" role="alert">
                <?php echo $statusMessage; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table mr-1"></i>
                    Consultation Requests
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="consultationsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Service</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                                            <td>
                                                <?php 
                                                    // Show a truncated message with a "Show more" button for long messages
                                                    $messageText = htmlspecialchars($row['message']);
                                                    if (strlen($messageText) > 50) {
                                                        echo '<div class="message-container">';
                                                        echo '<div class="message-short">' . substr($messageText, 0, 50) . '... </div>';
                                                        echo '<div class="message-full" style="display:none;">' . $messageText . '</div>';
                                                        echo '<button class="btn btn-sm btn-link toggle-message">Show more</button>';
                                                        echo '</div>';
                                                    } else {
                                                        echo $messageText;
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php
                                                    switch ($row['status']) {
                                                        case 'pending': echo 'warning'; break;
                                                        case 'contacted': echo 'info'; break;
                                                        case 'completed': echo 'success'; break;
                                                        case 'cancelled': echo 'danger'; break;
                                                    }
                                                ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $row['id']; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $row['id']; ?>">
                                                        <a class="dropdown-item" href="mailto:<?php echo htmlspecialchars($row['email']); ?>">Email</a>
                                                        <a class="dropdown-item view-details" href="#" data-toggle="modal" data-target="#viewModal" 
                                                           data-id="<?php echo $row['id']; ?>"
                                                           data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                           data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                           data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                                                           data-service="<?php echo htmlspecialchars($row['service_type']); ?>"
                                                           data-message="<?php echo htmlspecialchars($row['message']); ?>"
                                                           data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                                           data-created="<?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?>">
                                                            View Details
                                                        </a>
                                                        <a class="dropdown-item edit-item" href="#" data-toggle="modal" data-target="#editModal" 
                                                           data-id="<?php echo $row['id']; ?>"
                                                           data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                           data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                           data-phone="<?php echo htmlspecialchars($row['phone']); ?>"
                                                           data-service="<?php echo htmlspecialchars($row['service_type']); ?>"
                                                           data-message="<?php echo htmlspecialchars($row['message']); ?>"
                                                           data-status="<?php echo htmlspecialchars($row['status']); ?>">
                                                            Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item" href="?action=pending&id=<?php echo $row['id']; ?>">Mark as Pending</a>
                                                        <a class="dropdown-item" href="?action=contacted&id=<?php echo $row['id']; ?>">Mark as Contacted</a>
                                                        <a class="dropdown-item" href="?action=completed&id=<?php echo $row['id']; ?>">Mark as Completed</a>
                                                        <a class="dropdown-item" href="?action=cancelled&id=<?php echo $row['id']; ?>">Mark as Cancelled</a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger delete-btn" href="#" data-toggle="modal" data-target="#deleteModal" data-id="<?php echo $row['id']; ?>" data-name="<?php echo htmlspecialchars($row['name']); ?>">Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No consultation requests found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel">Consultation Request Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <span id="modal-name"></span></p>
                        <p><strong>Email:</strong> <span id="modal-email"></span></p>
                        <p><strong>Phone:</strong> <span id="modal-phone"></span></p>
                        <p><strong>Service:</strong> <span id="modal-service"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span id="modal-status"></span></p>
                        <p><strong>Created:</strong> <span id="modal-created"></span></p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <p><strong>Message:</strong></p>
                        <div class="border p-3 bg-light" id="modal-message"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="modal-email-link">Send Email</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete the consultation request from <span id="delete-name"></span>? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirm-delete">Delete</a>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" id="edit-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Consultation Request</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-name">Name</label>
                        <input type="text" class="form-control" id="edit-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-email">Email</label>
                        <input type="email" class="form-control" id="edit-email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-phone">Phone</label>
                        <input type="text" class="form-control" id="edit-phone" name="phone">
                    </div>
                    <div class="form-group">
                        <label for="edit-service">Service</label>
                        <input type="text" class="form-control" id="edit-service" name="service" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-status">Status</label>
                        <select class="form-control" id="edit-status" name="status">
                            <option value="pending">Pending</option>
                            <option value="contacted">Contacted</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-message">Message</label>
                        <textarea class="form-control" id="edit-message" name="message" rows="5"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// JavaScript to toggle between short and full message views
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-message');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const container = this.closest('.message-container');
            const shortMsg = container.querySelector('.message-short');
            const fullMsg = container.querySelector('.message-full');
            
            if (fullMsg.style.display === 'none') {
                shortMsg.style.display = 'none';
                fullMsg.style.display = 'block';
                this.textContent = 'Show less';
            } else {
                shortMsg.style.display = 'block';
                fullMsg.style.display = 'none';
                this.textContent = 'Show more';
            }
        });
    });
    
    // Handle View Details modal
    $('.view-details').on('click', function() {
        $('#modal-name').text($(this).data('name'));
        $('#modal-email').text($(this).data('email'));
        $('#modal-phone').text($(this).data('phone'));
        $('#modal-service').text($(this).data('service'));
        $('#modal-message').text($(this).data('message'));
        $('#modal-status').text($(this).data('status'));
        $('#modal-created').text($(this).data('created'));
        $('#modal-email-link').attr('href', 'mailto:' + $(this).data('email'));
    });
    
    // Handle Delete modal
    $('#deleteModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const name = button.data('name');
        
        $('#delete-name').text(name);
        $('#confirm-delete').attr('href', '?action=delete&id=' + id);
    });
    
    // Handle Edit modal
    $('.edit-item').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const email = $(this).data('email');
        const phone = $(this).data('phone');
        const service = $(this).data('service');
        const message = $(this).data('message');
        const status = $(this).data('status');
        
        // Set form values
        $('#edit-name').val(name);
        $('#edit-email').val(email);
        $('#edit-phone').val(phone);
        $('#edit-service').val(service);
        $('#edit-message').val(message);
        $('#edit-status').val(status);
        
        // Set form action
        $('#edit-form').attr('action', '?action=update&id=' + id);
    });
    
    // Initialize DataTables for better table functionality
    $('#consultationsTable').DataTable({
        responsive: true,
        order: [[7, 'desc']], // Sort by created date descending
        columnDefs: [
            { orderable: false, targets: 8 } // Disable ordering on the Actions column
        ]
    });
});
</script>

<?php
// Include admin footer
include_once 'includes/admin_footer.php';
?> 