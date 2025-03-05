<?php
// Include database connection file
include('config/database.php');

// Check if database connection is established
if(!isset($conn) || $conn === null) {
    die("Error: Database connection failed. Please check your database configuration.");
}

// Function to hash password securely
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Admin credentials
$admins = [
    [
        'firstname' => 'Esron',
        'lastname' => 'Admin',
        'email' => 'Esron221@gmail.com',
        'password' => 'Manzi21%'
    ],
    [
        'firstname' => 'Ephron',
        'lastname' => 'Tuyishime',
        'email' => 'ephrontuyishime21@gmail.com',
        'password' => 'EduJobs21%'
    ]
];

echo "<h2>Admin Setup Script</h2>";

// Create admins table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if (mysqli_query($conn, $sql)) {
    echo "Admins table created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
    exit();
}

// Clear existing admin records for clean setup
$clear_sql = "TRUNCATE TABLE admins";
if (mysqli_query($conn, $clear_sql)) {
    echo "Cleared existing admin records for fresh setup<br>";
} else {
    echo "Note: Could not clear existing records: " . mysqli_error($conn) . "<br>";
}

// Insert admin accounts
foreach ($admins as $admin) {
    // Add the new admin
    $insert_sql = "INSERT INTO admins (firstname, lastname, email, password) VALUES (?, ?, ?, ?)";
    $insert_stmt = mysqli_prepare($conn, $insert_sql);
    
    $hashed_password = hashPassword($admin['password']);
    
    mysqli_stmt_bind_param(
        $insert_stmt, 
        "ssss", 
        $admin['firstname'], 
        $admin['lastname'], 
        $admin['email'], 
        $hashed_password
    );
    
    if (mysqli_stmt_execute($insert_stmt)) {
        echo "Admin " . $admin['email'] . " created successfully<br>";
        echo "Password hash for verification: " . $hashed_password . "<br><br>";
    } else {
        echo "Error creating admin: " . mysqli_error($conn) . "<br>";
    }
    
    mysqli_stmt_close($insert_stmt);
}

echo "<br><strong>Setup complete!</strong><br>";
echo "You can now log in with either:<br>";
echo "Email: Esron221@gmail.com, Password: Manzi21%<br>";
echo "Email: ephrontuyishime21@gmail.com, Password: EduJobs21%<br>";

// Close connection
mysqli_close($conn);
?>