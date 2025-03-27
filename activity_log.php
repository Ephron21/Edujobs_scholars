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
$pageTitle = "Activity Log";
require_once($includesPath . 'admin_header.php');

// Define filter values (mock data)
$users = [
    1 => 'Admin User',
    2 => 'John Doe',
    3 => 'Sarah Smith',
    4 => 'Michael Brown',
    5 => 'Emily Johnson'
];

$activityTypes = [
    'login' => 'Login',
    'logout' => 'Logout',
    'create' => 'Create',
    'update' => 'Update',
    'delete' => 'Delete',
    'upload' => 'File Upload',
    'download' => 'File Download',
    'system' => 'System'
];

// Get filter parameters
$user_filter = isset($_GET['user']) ? intval($_GET['user']) : 0;
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$search_term = isset($_GET['search']) ? $_GET['search'] : '';

// Mock activity log data (replace with actual database query)
$activities = [
    [
        'id' => 1,
        'user_id' => 1,
        'user_name' => 'Admin User',
        'activity_type' => 'login',
        'description' => 'Logged in to the system',
        'ip_address' => '192.168.1.1',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-5 minutes')),
        'details' => json_encode(['browser' => 'Chrome', 'os' => 'Windows'])
    ],
    [
        'id' => 2,
        'user_id' => 2,
        'user_name' => 'John Doe',
        'activity_type' => 'create',
        'description' => 'Created a new student record',
        'ip_address' => '192.168.1.2',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-15 minutes')),
        'details' => json_encode(['student_id' => 123, 'action' => 'create'])
    ],
    [
        'id' => 3,
        'user_id' => 3,
        'user_name' => 'Sarah Smith',
        'activity_type' => 'update',
        'description' => 'Updated applicant status',
        'ip_address' => '192.168.1.3',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
        'details' => json_encode(['applicant_id' => 456, 'status' => 'approved'])
    ],
    [
        'id' => 4,
        'user_id' => 1,
        'user_name' => 'Admin User',
        'activity_type' => 'system',
        'description' => 'System backup completed',
        'ip_address' => '192.168.1.1',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-1 hour')),
        'details' => json_encode(['backup_file' => 'backup_2023-03-27.sql', 'size' => '15MB'])
    ],
    [
        'id' => 5,
        'user_id' => 1,
        'user_name' => 'Admin User',
        'activity_type' => 'delete',
        'description' => 'Deleted user account',
        'ip_address' => '192.168.1.1',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-2 hours')),
        'details' => json_encode(['deleted_user_id' => 789, 'reason' => 'User request'])
    ],
    [
        'id' => 6,
        'user_id' => 4,
        'user_name' => 'Michael Brown',
        'activity_type' => 'upload',
        'description' => 'Uploaded document',
        'ip_address' => '192.168.1.4',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-3 hours')),
        'details' => json_encode(['file' => 'report.pdf', 'size' => '2.5MB'])
    ],
    [
        'id' => 7,
        'user_id' => 5,
        'user_name' => 'Emily Johnson',
        'activity_type' => 'download',
        'description' => 'Downloaded student report',
        'ip_address' => '192.168.1.5',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-4 hours')),
        'details' => json_encode(['report' => 'student_summary_2023.xlsx'])
    ],
    [
        'id' => 8,
        'user_id' => 2,
        'user_name' => 'John Doe',
        'activity_type' => 'logout',
        'description' => 'Logged out from the system',
        'ip_address' => '192.168.1.2',
        'timestamp' => date('Y-m-d H:i:s', strtotime('-5 hours')),
        'details' => json_encode(['session_duration' => '45 minutes'])
    ]
];

// Filter activities based on parameters (in a real app, these would be added to the SQL query)
$filtered_activities = [];
foreach ($activities as $activity) {
    // Apply user filter
    if ($user_filter > 0 && $activity['user_id'] != $user_filter) {
        continue;
    }
    
    // Apply type filter
    if ($type_filter && $activity['activity_type'] != $type_filter) {
        continue;
    }
    
    // Apply date range filter
    if ($date_from) {
        $activity_date = new DateTime($activity['timestamp']);
        $from_date = new DateTime($date_from);
        if ($activity_date < $from_date) {
            continue;
        }
    }
    
    if ($date_to) {
        $activity_date = new DateTime($activity['timestamp']);
        $to_date = new DateTime($date_to);
        $to_date->setTime(23, 59, 59); // End of the day
        if ($activity_date > $to_date) {
            continue;
        }
    }
    
    // Apply search term
    if ($search_term && strpos(strtolower($activity['description']), strtolower($search_term)) === false) {
        continue;
    }
    
    $filtered_activities[] = $activity;
}

// Pagination settings
$items_per_page = 5;
$total_items = count($filtered_activities);
$total_pages = ceil($total_items / $items_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get activities for current page
$paginated_activities = array_slice($filtered_activities, $offset, $items_per_page);

// Function to get activity type badge class
function getActivityTypeBadgeClass($type) {
    switch ($type) {
        case 'login':
            return 'bg-success';
        case 'logout':
            return 'bg-secondary';
        case 'create':
            return 'bg-primary';
        case 'update':
            return 'bg-info';
        case 'delete':
            return 'bg-danger';
        case 'upload':
            return 'bg-warning text-dark';
        case 'download':
            return 'bg-light text-dark';
        case 'system':
            return 'bg-dark';
        default:
            return 'bg-secondary';
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h2><i class="fas fa-history"></i> Activity Log</h2>
                    <p class="text-muted">View and filter system activities</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Filter sidebar -->
        <div class="col-md-3">
            <div class="card dashboard-card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Activities</h5>
                </div>
                <div class="card-body">
                    <form id="filterForm" method="get" action="activity_log.php">
                        <div class="mb-3">
                            <label for="user" class="form-label">User</label>
                            <select class="form-select" id="user" name="user">
                                <option value="0">All Users</option>
                                <?php foreach ($users as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo $user_filter == $id ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Activity Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">All Types</option>
                                <?php foreach ($activityTypes as $key => $label): ?>
                                    <option value="<?php echo $key; ?>" <?php echo $type_filter == $key ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="date_from" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="date_to" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="search" class="form-label">Search Term</label>
                            <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search_term); ?>" placeholder="Search in description...">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="activity_log.php" class="btn btn-outline-secondary">Clear Filters</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card dashboard-card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-download"></i> Export Options</h5>
                </div>
                <div class="card-body">
                    <p>Export the filtered activity log for reporting.</p>
                    <div class="d-grid gap-2">
                        <button id="exportCSV" class="btn btn-outline-primary">
                            <i class="fas fa-file-csv me-2"></i>Export to CSV
                        </button>
                        <button id="exportPDF" class="btn btn-outline-danger">
                            <i class="fas fa-file-pdf me-2"></i>Export to PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activity log content -->
        <div class="col-md-9">
            <div class="card dashboard-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Activity List</h5>
                    <span class="badge bg-primary"><?php echo $total_items; ?> activities found</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover activity-table mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>User</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Timestamp</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($paginated_activities)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">No activities found matching your criteria.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($paginated_activities as $activity): ?>
                                        <tr>
                                            <td>
                                                <span class="badge <?php echo getActivityTypeBadgeClass($activity['activity_type']); ?>">
                                                    <?php echo htmlspecialchars($activityTypes[$activity['activity_type']]); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($activity['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['ip_address']); ?></td>
                                            <td><?php echo htmlspecialchars($activity['timestamp']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info view-details" data-bs-toggle="modal" data-bs-target="#activityDetailsModal" data-id="<?php echo $activity['id']; ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="d-flex justify-content-center my-4">
                            <nav aria-label="Activity log pagination">
                                <ul class="pagination">
                                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&user=<?php echo $user_filter; ?>&type=<?php echo $type_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&search=<?php echo urlencode($search_term); ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&user=<?php echo $user_filter; ?>&type=<?php echo $type_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&search=<?php echo urlencode($search_term); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&user=<?php echo $user_filter; ?>&type=<?php echo $type_filter; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&search=<?php echo urlencode($search_term); ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Real-time Activity Monitor -->
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Real-time Activity Monitor</h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="realTimeSwitch" checked>
                        <label class="form-check-label" for="realTimeSwitch">Auto Refresh</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="realtime-chart-container">
                        <canvas id="realtimeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Details Modal -->
<div class="modal fade" id="activityDetailsModal" tabindex="-1" aria-labelledby="activityDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityDetailsModalLabel">Activity Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="activityDetails">
                    <div class="mb-3">
                        <strong>ID:</strong> <span id="detail-id"></span>
                    </div>
                    <div class="mb-3">
                        <strong>User:</strong> <span id="detail-user"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Type:</strong> <span id="detail-type"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong> <span id="detail-description"></span>
                    </div>
                    <div class="mb-3">
                        <strong>IP Address:</strong> <span id="detail-ip"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Timestamp:</strong> <span id="detail-timestamp"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Details:</strong>
                        <pre id="detail-json" class="bg-light p-2 mt-2 rounded"></pre>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// Handle View Details button click
document.querySelectorAll('.view-details').forEach(button => {
    button.addEventListener('click', function() {
        const activityId = this.getAttribute('data-id');
        
        // Find the activity in our array
        <?php echo 'const activities = ' . json_encode($activities) . ';'; ?>
        const activity = activities.find(a => a.id == activityId);
        
        if (activity) {
            // Populate modal with activity details
            document.getElementById('detail-id').textContent = activity.id;
            document.getElementById('detail-user').textContent = activity.user_name;
            document.getElementById('detail-type').textContent = '<?php echo json_encode($activityTypes); ?>'[activity.activity_type];
            document.getElementById('detail-description').textContent = activity.description;
            document.getElementById('detail-ip').textContent = activity.ip_address;
            document.getElementById('detail-timestamp').textContent = activity.timestamp;
            
            // Format and display JSON details
            try {
                const detailsObj = JSON.parse(activity.details);
                document.getElementById('detail-json').textContent = JSON.stringify(detailsObj, null, 2);
            } catch (e) {
                document.getElementById('detail-json').textContent = activity.details;
            }
        }
    });
});

// Export to CSV
document.getElementById('exportCSV').addEventListener('click', function() {
    // Create CSV content
    let csvContent = "data:text/csv;charset=utf-8,";
    csvContent += "ID,User,Type,Description,IP Address,Timestamp\n";
    
    <?php echo 'const filteredActivities = ' . json_encode($filtered_activities) . ';'; ?>
    
    filteredActivities.forEach(activity => {
        csvContent += `${activity.id},${activity.user_name},${activity.activity_type},${activity.description},${activity.ip_address},${activity.timestamp}\n`;
    });
    
    // Create download link
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "activity_log_export.csv");
    document.body.appendChild(link);
    
    // Trigger download
    link.click();
    document.body.removeChild(link);
});

// Handle real-time chart
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('realtimeChart').getContext('2d');
    
    // Prepare data
    const activityCounts = {
        login: 0,
        logout: 0,
        create: 0,
        update: 0,
        delete: 0,
        upload: 0,
        download: 0,
        system: 0
    };
    
    <?php echo 'const chartActivities = ' . json_encode($activities) . ';'; ?>
    
    chartActivities.forEach(activity => {
        activityCounts[activity.activity_type]++;
    });
    
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(activityCounts).map(key => {
                return '<?php echo json_encode($activityTypes); ?>'[key] || key;
            }),
            datasets: [{
                label: 'Activity Count',
                data: Object.values(activityCounts),
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',  // login
                    'rgba(108, 117, 125, 0.7)', // logout
                    'rgba(0, 123, 255, 0.7)',   // create
                    'rgba(23, 162, 184, 0.7)',  // update
                    'rgba(220, 53, 69, 0.7)',   // delete
                    'rgba(255, 193, 7, 0.7)',   // upload
                    'rgba(248, 249, 250, 0.7)', // download
                    'rgba(52, 58, 64, 0.7)'     // system
                ],
                borderColor: [
                    'rgb(40, 167, 69)',
                    'rgb(108, 117, 125)',
                    'rgb(0, 123, 255)',
                    'rgb(23, 162, 184)',
                    'rgb(220, 53, 69)',
                    'rgb(255, 193, 7)',
                    'rgb(248, 249, 250)',
                    'rgb(52, 58, 64)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Real-time updates
    let autoRefresh = true;
    document.getElementById('realTimeSwitch').addEventListener('change', function() {
        autoRefresh = this.checked;
    });
    
    // Simulate real-time updates every 10 seconds
    setInterval(() => {
        if (!autoRefresh) return;
        
        // In a real app, you would fetch new data from the server
        // For this demo, we'll just randomize some values
        const randomType = Object.keys(activityCounts)[Math.floor(Math.random() * Object.keys(activityCounts).length)];
        activityCounts[randomType]++;
        
        chart.data.datasets[0].data = Object.values(activityCounts);
        chart.update();
    }, 10000);
});
</script>

<?php require_once($includesPath . 'admin_footer.php'); ?> 