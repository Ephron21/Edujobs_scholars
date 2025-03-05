<?php
// Include database connection file
include('config/database.php');

// Check if database connection is established
if(!isset($conn) || $conn === null) {
    die("Error: Database connection failed. Please check your database configuration.");
}

// Define variables and initialize with empty values
$email = $password = "";
$email_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // For debugging - remove in production
    // error_log("Login attempt: " . print_r($_POST, true));
 
    // Check if email is empty
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your email.";
    } else{
        $email = trim($_POST["email"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($email_err) && empty($password_err)){
        // Check admin table only - we're focusing on admin login
        $sql = "SELECT id, firstname, lastname, email, password FROM admins WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Set parameters
            $param_email = $email;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store result
                mysqli_stmt_store_result($stmt);
                
                // Check if email exists in admin table
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Bind result variables
                    mysqli_stmt_bind_result($stmt, $id, $firstname, $lastname, $email, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        // For debugging - remove in production
                        // error_log("Password: $password, Hash: $hashed_password");
                        
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["email"] = $email;
                            $_SESSION["firstname"] = $firstname;
                            $_SESSION["lastname"] = $lastname;
                            $_SESSION["is_admin"] = true;  // Set admin flag

                            // Redirect to admin dashboard
                            header("location: admin_dashboard.php");
                            exit();
                        } else{
                            // Password is not valid
                            $login_err = "Invalid email or password.";
                            // For debugging - remove in production
                            // error_log("Password verification failed");
                        }
                    }
                } else {
                    // No admin found with that email
                    $login_err = "Invalid email or password.";
                    // For debugging - remove in production
                    // error_log("No admin found with email: $email");
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            } else{
                echo "Oops! Something went wrong. Please try again later.";
                // For debugging - remove in production
                // error_log("Query execution failed: " . mysqli_error($conn));
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
                exit();
            }
        }
    }
    
    // Close connection
    mysqli_close($conn);
}
?>

<?php include('includes/header.php'); ?>

<style>
    /* Smooth fade-in animation */
    .login-container {
        opacity: 0;
        animation: fadeIn 1.5s ease-in-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Moving text banner */
    .moving-text {
        white-space: nowrap;
        overflow: hidden;
        display: block;
        font-size: 18px;
        font-weight: bold;
        background-color: #007bff;
        color: white;
        padding: 10px;
        animation: moveText 10s linear infinite;
    }
    @keyframes moveText {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }

    /* Image slider */
    .slider {
        width: 100%;
        overflow: hidden;
        position: relative;
        max-width: 500px;
        margin: auto;
    }
    .slides {
        display: flex;
        transition: transform 1.5s ease-in-out;
    }
    .slides img {
        width: 100%;
        border-radius: 6px;
        height: 160px;
    }

     /* Back to Home Button */
     .back-to-home {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-home a {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease-in-out;
        }

        .back-to-home a:hover {
            background-color: #0056b3;
        }

</style>

<!-- Moving text -->
<div class="moving-text">Welcome to our system! This is Login Admin for EduJobs Scholar.</div>

<!-- Image Slider -->
<div class="slider">
    <div class="slides">
        <img src="images/te.jpg" alt="Slide 1">
        <img src="images/bene.jpg" alt="Slide 2">
    </div>
</div>

<script>
    // Slider animation
    let index = 0;
    function moveSlides() {
        const slides = document.querySelector('.slides');
        index++;
        if (index >= slides.children.length) {
            index = 0;
        }
        slides.style.transform = `translateX(${-index * 100}%)`;
    }
    setInterval(moveSlides, 3000);
</script>

<div class="container mt-5 login-container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h2 class="text-center">Admin Login</h2>
                </div>
                <div class="card-body">
                    <?php if(!empty($login_err)){ ?>
                        <div class="alert alert-danger"><?php echo $login_err; ?></div>
                    <?php } ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>    
                        <div class="form-group mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        </div>
                        <div class="form-group d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>

                        <!-- Back to Home Button -->
                        <div class="back-to-home">
                            <a href="index.php">Back to Home</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>