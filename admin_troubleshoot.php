<?php
// Include database connection file
include('config/database.php');

// Check if database connection is established
if(!isset($conn) || $conn === null) {
    die("Error: Database connection failed. Please check your database configuration.");
}

echo "<h1>Admin Login Troubleshooter</h1>";

// Step 1: Check if admins table exists
echo "<h2>Step 1: Checking if admins table exists</h2>";
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'admins'");
if(mysqli_num_rows($table_check) == 0) {
    echo "<p style='color:red'>ERROR: The 'admins' table does not exist in the database.</p>";
    echo "<p>Please run the admin setup script first to create the table.</p>";
} else {
    echo "<p style='color:green'>SUCCESS: The 'admins' table exists.</p>";
    
    // Step 2: Check if admin records exist
    echo "<h2>Step 2: Checking admin records</h2>";
    $record_check = mysqli_query($conn, "SELECT * FROM admins");
    if(mysqli_num_rows($record_check) == 0) {
        echo "<p style='color:red'>ERROR: No admin records found in the 'admins' table.</p>";
        echo "<p>Please run the admin setup script to create admin accounts.</p>";
    } else {
        echo "<p style='color:green'>SUCCESS: Found " . mysqli_num_rows($record_check) . " admin records.</p>";
        
        // Display admin emails (not passwords)
        echo "<p>Admin emails in database:</p>";
        echo "<ul>";
        while($row = mysqli_fetch_assoc($record_check)) {
            echo "<li>" . $row['email'] . "</li>";
        }
        echo "</ul>";
        
        // Step 3: Test login for both admins
        echo "<h2>Step 3: Testing admin login</h2>";
        
        // Test credentials
        $test_credentials = [
            [
                'email' => 'Esron221@gmail.com',
                'password' => 'Manzi21%'
            ],
            [
                'email' => 'ephrontuyishime21@gmail.com',
                'password' => 'EduJobs21%'
            ]
        ];
        
        foreach($test_credentials as $cred) {
            echo "<h3>Testing login for: " . $cred['email'] . "</h3>";
            
            // Get stored hash for this email
            $sql = "SELECT id, password FROM admins WHERE email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $cred['email']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $id, $hashed_password);
                mysqli_stmt_fetch($stmt);
                
                echo "<p>Found user record with ID: " . $id . "</p>";
                echo "<p>Stored password hash: " . $hashed_password . "</p>";
                
                // Test password verification
                if(password_verify($cred['password'], $hashed_password)) {
                    echo "<p style='color:green'>SUCCESS: Password verification passed! Login should work.</p>";
                } else {
                    echo "<p style='color:red'>ERROR: Password verification failed.</p>";
                    
                    // Create a new hash for comparison
                    $new_hash = password_hash($cred['password'], PASSWORD_DEFAULT);
                    echo "<p>New generated hash for same password: " . $new_hash . "</p>";
                    echo "<p>Note: Hashes will be different each time but both should verify the same password.</p>";
                }
            } else {
                echo "<p style='color:red'>ERROR: No record found for email: " . $cred['email'] . "</p>";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

// Step 4: Check for case-sensitivity issues
echo "<h2>Step 4: Checking for case-sensitivity issues</h2>";
$emails = ['Esron221@gmail.com', 'esron221@gmail.com', 'ESRON221@GMAIL.COM', 'ephrontuyishime21@gmail.com', 'EPHRONTUYISHIME21@GMAIL.COM'];

foreach($emails as $email) {
    $sql = "SELECT id FROM admins WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    echo "<p>Email '" . $email . "': ";
    if(mysqli_stmt_num_rows($stmt) > 0) {
        echo "<span style='color:green'>Found in database</span>";
    } else {
        echo "<span style='color:red'>Not found in database</span>";
    }
    echo "</p>";
    
    mysqli_stmt_close($stmt);
}

// Step 5: Provide a fix
echo "<h2>Step 5: One-click Fix</h2>";
if(isset($_GET['fix']) && $_GET['fix'] == 'yes') {
    // Recreate admin accounts with known good configuration
    $clear = mysqli_query($conn, "TRUNCATE TABLE admins");
    
    // Define admin credentials
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
    
    $success = true;
    foreach($admins as $admin) {
        $hash = password_hash($admin['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO admins (firstname, lastname, email, password) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $admin['firstname'], $admin['lastname'], $admin['email'], $hash);
        
        if(!mysqli_stmt_execute($stmt)) {
            $success = false;
            echo "<p style='color:red'>Failed to create admin: " . $admin['email'] . "</p>";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    if($success) {
        echo "<p style='color:green'>Admin accounts have been reset successfully!</p>";
        echo "<p>You should now be able to log in with either:</p>";
        echo "<ul>";
        echo "<li>Email: Esron221@gmail.com, Password: Manzi21%</li>";
        echo "<li>Email: ephrontuyishime21@gmail.com, Password: EduJobs21%</li>";
        echo "</ul>";
    }
} else {
    echo "<p>Click the button below to reset admin accounts:</p>";
    echo "<a href='?fix=yes' style='background-color:#4CAF50;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;'>Reset Admin Accounts</a>";
}

// Close connection
mysqli_close($conn);
?>