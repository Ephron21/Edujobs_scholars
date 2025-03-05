<?php
// Include database connection file
include('config/database.php');

// Check if database connection is established
if(!isset($conn) || $conn === null) {
    die("Error: Database connection failed. Please check your database configuration.");
}

// Define variables and initialize with empty values
$firstname = $lastname = $email = $phone = $password = $confirm_password = "";
$address = $city = $country = $postal_code = $date_of_birth = $gender = $occupation = $bio = "";
$firstname_err = $lastname_err = $email_err = $phone_err = $password_err = $confirm_password_err = "";
$address_err = $city_err = $country_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate firstname
    if(empty(trim($_POST["firstname"]))){
        $firstname_err = "Please enter your first name.";
    } else{
        $firstname = trim($_POST["firstname"]);
    }
    
    // Validate lastname
    if(empty(trim($_POST["lastname"]))){
        $lastname_err = "Please enter your last name.";
    } else{
        $lastname = trim($_POST["lastname"]);
    }
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter an email.";
    } elseif(!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM users WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Set parameters
            $param_email = trim($_POST["email"]);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "This email is already taken.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Validate phone
    if(empty(trim($_POST["phone"]))){
        $phone_err = "Please enter your phone number.";
    } else{
        $phone = trim($_POST["phone"]);
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Get profile information (optional fields)
    $address = !empty($_POST["address"]) ? trim($_POST["address"]) : "";
    $city = !empty($_POST["city"]) ? trim($_POST["city"]) : "";
    $country = !empty($_POST["country"]) ? trim($_POST["country"]) : "";
    $postal_code = !empty($_POST["postal_code"]) ? trim($_POST["postal_code"]) : "";
    $date_of_birth = !empty($_POST["date_of_birth"]) ? trim($_POST["date_of_birth"]) : NULL;
    $gender = !empty($_POST["gender"]) ? trim($_POST["gender"]) : "";
    $occupation = !empty($_POST["occupation"]) ? trim($_POST["occupation"]) : "";
    $bio = !empty($_POST["bio"]) ? trim($_POST["bio"]) : "";
    
    // Check input errors before inserting in database
    if(empty($firstname_err) && empty($lastname_err) && empty($email_err) && empty($phone_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Prepare an insert statement for users table
            $sql = "INSERT INTO users (firstname, lastname, email, phone, password, user_type) VALUES (?, ?, ?, ?, ?, ?)";
            
            if($stmt = mysqli_prepare($conn, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ssssss", $param_firstname, $param_lastname, $param_email, $param_phone, $param_password, $param_user_type);
                
                // Set parameters
                $param_firstname = $firstname;
                $param_lastname = $lastname;
                $param_email = $email;
                $param_phone = $phone;
                $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
                $param_user_type = "user"; // By default, set as regular user
                
                // Execute the statement
                mysqli_stmt_execute($stmt);
                
                // Get the user ID for the profile table
                $user_id = mysqli_insert_id($conn);
                
                // Close statement
                mysqli_stmt_close($stmt);
                
                // Insert into profile table if we have a user_id
                if($user_id) {
                    $profile_sql = "INSERT INTO user_profiles (user_id, address, city, country, postal_code, date_of_birth, gender, occupation, bio) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    if($profile_stmt = mysqli_prepare($conn, $profile_sql)){
                        // Bind variables
                        mysqli_stmt_bind_param(
                            $profile_stmt, 
                            "issssssss", 
                            $user_id,
                            $address,
                            $city,
                            $country,
                            $postal_code,
                            $date_of_birth,
                            $gender,
                            $occupation,
                            $bio
                        );
                        
                        // Execute statement
                        mysqli_stmt_execute($profile_stmt);
                        
                        // Close statement
                        mysqli_stmt_close($profile_stmt);
                    }
                }
                
                // Commit transaction
                mysqli_commit($conn);
                
                // Redirect to login page
                header("location: login.php");
                exit();
            }
        } catch (Exception $e) {
            // Roll back transaction on error
            mysqli_rollback($conn);
            echo "Error: " . $e->getMessage();
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<?php include('includes/header.php'); ?>

<style>
    /* General Styles */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f7f7f7;
    color: #333;
}

/* Container Styles */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Card Styles */
.card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    background-color: #fff;
    padding: 20px;
}

.card-header {
    background-color: #007bff;
    color: #fff;
    padding: 15px;
    border-radius: 10px 10px 0 0;
    text-align: center;
}

.card-header h2 {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.card-header p {
    font-size: 1rem;
}

/* Form Styles */
form {
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    margin-top: 20px;
}

/* Input Fields Styles */
.form-group input, .form-group select, .form-group textarea {
    width: 100%;
    padding: 12px;
    border-radius: 5px;
    border: 1px solid #ccc;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    border-color: #007bff;
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    outline: none;
}

.form-group .invalid-feedback {
    color: red;
    font-size: 0.9rem;
}

/* Button Styles */
button {
    padding: 12px;
    font-size: 1rem;
    border-radius: 5px;
    transition: all 0.3s ease;
    cursor: pointer;
}

button:hover {
    background-color: #0056b3;
    border-color: #004085;
}

.btn-primary {
    background-color: #007bff;
    border: 1px solid #007bff;
    color: #fff;
}

.btn-secondary {
    background-color: #6c757d;
    border: 1px solid #6c757d;
    color: #fff;
}

.d-grid {
    display: grid;
    gap: 10px;
}

/* Section Headers */
h4 {
    font-size: 1.2rem;
    margin-bottom: 20px;
    color: #007bff;
}

/* Form Layout */
.row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.col-md-6, .col-md-4, .col-md-12 {
    flex: 1;
    min-width: 250px;
}

/* Footer and Links */
.text-center {
    text-align: center;
}

.text-center a {
    color: #007bff;
    text-decoration: none;
    transition: color 0.3s ease;
}

.text-center a:hover {
    color: #0056b3;
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .row {
        flex-direction: column;
        gap: 10px;
    }
    
    .col-md-6 {
        flex: 1;
        min-width: 100%;
    }

    .col-md-4 {
        flex: 1;
        min-width: 100%;
    }
}

/* Hover Effects */
.form-group input:hover, .form-group select:hover, .form-group textarea:hover {
    border-color: #0056b3;
}

/* Focus Effects */
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    border-color: #007bff;
    outline: none;
}
</style>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">User Registration</h2>
                    <p class="text-center">Please fill this form to create an account.</p>
                </div>
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <!-- Account Information Section -->
                        <h4 class="mb-3">Account Information</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="firstname" class="form-control <?php echo (!empty($firstname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $firstname; ?>">
                                    <span class="invalid-feedback"><?php echo $firstname_err; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="lastname" class="form-control <?php echo (!empty($lastname_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $lastname; ?>">
                                    <span class="invalid-feedback"><?php echo $lastname_err; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
                                    <span class="invalid-feedback"><?php echo $phone_err; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Profile Information Section (Optional) -->
                        <h4 class="mt-4 mb-3">Profile Information (Optional)</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label>Address</label>
                                    <input type="text" name="address" class="form-control" value="<?php echo $address; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>City</label>
                                    <input type="text" name="city" class="form-control" value="<?php echo $city; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Country</label>
                                    <input type="text" name="country" class="form-control" value="<?php echo $country; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Postal Code</label>
                                    <input type="text" name="postal_code" class="form-control" value="<?php echo $postal_code; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Date of Birth</label>
                                    <input type="date" name="date_of_birth" class="form-control" value="<?php echo $date_of_birth; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?php if($gender == "Male") echo "selected"; ?>>Male</option>
                                        <option value="Female" <?php if($gender == "Female") echo "selected"; ?>>Female</option>
                                        <option value="Other" <?php if($gender == "Other") echo "selected"; ?>>Other</option>
                                        <option value="Prefer not to say" <?php if($gender == "Prefer not to say") echo "selected"; ?>>Prefer not to say</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label>Occupation</label>
                                    <input type="text" name="occupation" class="form-control" value="<?php echo $occupation; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label>Bio</label>
                                    <textarea name="bio" class="form-control" rows="3"><?php echo $bio; ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Sign Up</button>
                            <button type="reset" class="btn btn-secondary mt-2">Reset</button>
                        </div>
                        <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a>.</p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>