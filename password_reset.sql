-- Create password_reset table
CREATE TABLE password_reset (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ndahari</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .site-header{
            background-color: #2c3e50;
    color: white;
    padding: 15px 0;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            margin-right: 10px;
        }

        .logo h1 {
            margin: 0;
            font-size: 24px;
            color: #fff; /* White text color for logo */
        }

        .main-nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .main-nav ul li {
            margin-left: 20px;
            position: relative;
        }

        .main-nav ul li a {
            color: #fff; /* White text color for links */
            text-decoration: none;
            padding: 10px 15px;
            display: block;
        }

        .main-nav ul li a:hover {
            background-color: #555; /* Darker background on hover */
            border-radius: 5px;
        }

        /* Dropdown Styles */
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #333; /* Dark background for dropdown */
            min-width: 160px;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
            z-index: 1;
            border-radius: 5px;
        }

        .dropdown-content a {
            color: #fff; /* White text color for dropdown links */
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-content a:hover {
            background-color: #555; /* Darker background on hover */
        }

        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* Responsive Navbar */
        .nav-toggle {
            display: none;
        }

        .nav-toggle-label {
            display: none;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .nav-toggle-label {
                display: block;
            }

            .main-nav ul {
                display: none;
                flex-direction: column;
                position: absolute;
                top: 60px;
                right: 0;
                background-color: #333; /* Dark background for mobile menu */
                width: 100%;
            }

            .main-nav ul li {
                margin: 0;
            }

            .main-nav ul li a {
                padding: 15px;
            }

            .nav-toggle:checked ~ ul {
                display: flex;
            }
        }
    </style>
    <script>
        // JavaScript for additional interactivity (if needed)
        document.addEventListener("DOMContentLoaded", function () {
            // Add any JavaScript functionality here
        });
    </script>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <a href="index.php">
                <h1>Ndahari</h1>
                    <img src="public/images/logo.png" alt="Ndahari Logo">
                   
                </a>
            </div>
            <nav class="main-nav">
                <input type="checkbox" id="nav-toggle" class="nav-toggle">
                <label for="nav-toggle" class="nav-toggle-label">
                    <span></span>
                </label>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropbtn">Register</a>
                        <div class="dropdown-content">
                            <a href="job_seeker_signup.php">Job Seeker</a>
                            <a href="employer_signup.php">Employer</a>
                        </div>
                    </li>
                    <li><a href="Contact.php">Contact</a></li>
                    <li><a href="signin.php">Sign In</a></li>
                    <li><a href="admin_login.php">CSM</a></li>
                </ul>
            </nav>
        </div>
    </header>
</body>
</html>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ndahari Job Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
        }

        /* Footer Styling */
        .site-footer {
            background-color: #222;
            color: #fff;
            padding: 10px 0; /* Reduced padding to minimize height */
            text-align: center;
        }

        .footer-content {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            padding: 10px; /* Reduced padding */
        }

        .footer-section {
            width: 30%;
            margin: 10px 0;
        }

        .footer-section h3 {
            color: #f1c40f;
            font-size: 1.2rem; /* Smaller font size */
            margin-bottom: 10px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li a {
            color: #ddd;
            text-decoration: none;
            transition: 0.3s;
            font-size: 0.9rem; /* Smaller font size */
        }

        .footer-section ul li a:hover {
            color: #f1c40f;
            text-decoration: underline;
        }

        .social-links {
            margin-top: 10px;
        }

        .social-icon {
            margin: 0 8px;
            font-size: 20px; /* Smaller icons */
            color: white;
            transition: transform 0.3s, color 0.3s;
        }

        .social-icon:hover {
            transform: scale(1.2);
        }

        .facebook:hover { color: #3b5998; }
        .twitter:hover { color: #1da1f2; }
        .linkedin:hover { color: #0077b5; }
        .instagram:hover { color: #e1306c; }
        .whatsapp:hover { color: #25d366; }

        /* Footer Bottom */
        .footer-bottom {
            background: #111;
            padding: 8px; /* Reduced padding */
            font-size: 12px; /* Smaller font size */
        }

        /* Floating WhatsApp Button */
        .whatsapp-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #25d366;
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px; /* Smaller icon */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            animation: bounce 1.5s infinite;
            transition: transform 0.3s ease-in-out;
        }

        .whatsapp-float:hover {
            transform: scale(1.1);
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        /* Additional Interactivity */
        .footer-section.about p {
            font-size: 0.9rem; /* Smaller font size */
            line-height: 1.4;
        }

        .footer-section.links ul li {
            margin-bottom: 8px; /* Reduced spacing */
        }

        .footer-section.social h3 {
            margin-bottom: 8px; /* Reduced spacing */
        }
    </style>
</head>
<body>

    <!-- Footer Section -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <!-- About Section -->
                <div class="footer-section about">
                    <h3>Ndahari Job Portal</h3>
                    <p>Connecting job seekers with employers for a brighter future. Find your dream job or hire the best talent with Ndahari.</p>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="signin.php">Sign In</a></li>
                        <li><a href="signup.php">Sign Up</a></li>
                        <li><a href="dashboard.php">Dashboard</a></li>
                    </ul>
                </div>
               
                <!-- Social Media Links -->
                <div class="footer-section social">
                    <h3>Connect With Us</h3>
                    <div class="social-links">
                        <a href="https://wa.me/0787846344" target="_blank" class="social-icon whatsapp"><i class="fa fa-whatsapp"></i></a>
                        <a href="https://twitter.com" target="_blank" class="social-icon twitter"><i class="fa fa-twitter"></i></a>
                        <a href="https://linkedin.com" target="_blank" class="social-icon linkedin"><i class="fa fa-linkedin"></i></a>
                        <a href="https://facebook.com" target="_blank" class="social-icon facebook"><i class="fa fa-facebook"></i></a>
                        <a href="https://instagram.com" target="_blank" class="social-icon instagram"><i class="fa fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Ndahari Job Portal. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Icon -->
    <a href="https://wa.me/0787846344" target="_blank" class="whatsapp-float">
        <i class="fa fa-whatsapp"></i>
    </a>

    <!-- JavaScript for Interactivity -->
    <script>
        // Smooth Scroll for Footer Links
        document.querySelectorAll('.footer-section ul li a').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                if (this.hash !== "") {
                    e.preventDefault();
                    const hash = this.hash;
                    document.querySelector(hash).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add Hover Effect to Footer Sections
        document.querySelectorAll('.footer-section').forEach(section => {
            section.addEventListener('mouseover', () => {
                section.style.transform = 'translateY(-5px)';
                section.style.transition = 'transform 0.3s ease';
            });
            section.addEventListener('mouseout', () => {
                section.style.transform = 'translateY(0)';
            });
        });
    </script>

</body>
</html>