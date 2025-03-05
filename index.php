<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session securely
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

// Include header (using relative path for portability)
include('includes/header.php');

// Database configuration
require_once 'config.php'; // Ensure this file contains your database connection details

// Function to check if user is logged in
function isUserLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// Function to get file icon based on file type
function getFileIcon($fileType) {
    $iconMap = [
        'image/jpeg' => '📷',
        'image/png' => '🖼️',
        'image/gif' => '🎬',
        'application/pdf' => '📄',
        'application/msword' => '📝',
        'video/mp4' => '🎥',
        'text/plain' => '📋',
        'default' => '📁'
    ];
    
    return $iconMap[$fileType] ?? $iconMap['default'];
}

// Fetch public files
$query = "SELECT uf.*, a.email AS uploaded_by_email 
          FROM uploaded_files uf 
          LEFT JOIN admins a ON uf.uploaded_by = a.id 
          WHERE uf.is_public = 1 
          ORDER BY uf.upload_date DESC 
          LIMIT 6"; // Limit to 6 most recent public files

$stmt = $conn->prepare($query);
$stmt->execute();
$publicFiles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduJobs Scholars</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Background Image for the Header */
        header {
            background: url('images/homepage.pn') no-repeat center center/cover;
            padding: 100px 0;
            color: blue;
            text-align: center;
            animation: fadeIn 2s ease-in-out;
        }

        header h1 {
            font-size: 3rem;
            animation: slideInLeft 1.5s ease-out;
        }

        header p {
            font-size: 1.2rem;
            margin-top: 20px;
            animation: slideInRight 1.5s ease-out;
        }

        /* Container for Content */
        .container {
            max-width: 1100px;
            margin: auto;
            padding: 0 15px;
        }

        /* Section Styling */
        section {
            margin-top: 50px;
        }

        h2 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
        }

        /* Features Section */
        .features div {
            margin-bottom: 30px;
            transition: transform 0.3s ease-in-out;
        }

        .features div:hover {
            transform: scale(1.05);
        }

        .features h3 {
            color: #0056b3;
            font-size: 1.5rem;
        }

        /* Image Slider */
        #slider {
            position: relative;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .slider-images {
            display: flex;
            transition: transform 1s ease-in-out;
        }

        .slider-images img {
            width: 100%;
            height: auto;
        }

        /* Animating moving text */
        .marquee {
            overflow: hidden;
            white-space: nowrap;
            box-sizing: border-box;
            font-size: 1.2rem;
            color: #007bff;
            background-color: #f4f7fc;
            padding: 10px 0;
        }

        .marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 10s linear infinite;
        }

        /* Popup Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow: auto;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 10px;
            animation: fadeIn 0.5s ease-in-out;
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            float: right;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* File Display Styles */
        .public-files-section {
            background-color: #f9f9f9;
            padding: 50px 0;
            margin-top: 30px;
        }

        .public-files-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .file-card {
            background-color: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .file-card:hover {
            transform: scale(1.05);
        }

        .file-icon {
            font-size: 3em;
            margin-bottom: 15px;
        }

        .file-details {
            margin-top: 15px;
        }

        .file-download {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .file-download:hover {
            background-color: #0056b3;
        }

        .login-prompt {
            text-align: center;
            color: #666;
            margin-top: 15px;
        }

        /* Keyframes for Animations */
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        @keyframes slideInLeft {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(0); }
        }

        @keyframes slideInRight {
            0% { transform: translateX(100%); }
            100% { transform: translateX(0); }
        }

        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body>
    <!-- Main Content Section -->
    <div class="container">
        <header class="mt-5">
            <h1>Welcome to EduJobs Scholars</h1>
            <p>Your go-to platform for the latest updates on university scholarships and job opportunities in Rwanda and internationally. We also provide application consulting services, MIFOTRA account setup, CV writing, and more.</p>
        </header>

        <!-- Introduction Section -->
        <section class="intro mt-5">
            <div class="row">
                <div class="col-md-6">
                    <h2>What We Do</h2>
                    <p>EduJobs Scholars provides students who have completed secondary school with up-to-date information on open university applications in Rwanda and around the world. We also assist job seekers in finding job vacancies, as well as providing support for MIFOTRA-related processes and application consulting services.</p>
                    <p>In addition, we offer services such as web development, graphic design, video production, and other valuable services for both students and job seekers.</p>
                </div>
                <div class="col-md-6">
                    <div id="slider">
                        <div class="slider-images">
                            <img src="images/ur.png" alt="Scholarship Image 1">
                            <img src="images/rp.png" alt="Scholarship Image 2">
                            <img src="images/Cur.png" alt="Scholarship Image 3">
                            <img src="images/mifotra.png" alt="Scholarship Image 1">
                            <img src="images/rp.png" alt="Scholarship Image 2">
                            <img src="images/Cur.png" alt="Scholarship Image 3">
                            <img src="images/ines.png" alt="Scholarship Image 2">
                            <img src="images/kib.png" alt="Scholarship Image 3">
                            <img src="images/card1.png" alt="Scholarship Image 2">
                            <img src="images/Cur.png" alt="Scholarship Image 3">
                            <img src="images/ur.png" alt="Scholarship Image 2">
                            <img src="images/Cur.png" alt="Scholarship Image 3">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features mt-5">
            <h2>Our Features</h2>
            <div class="row">
                <div class="col-md-3 text-center">
                    <h3>📝 Application Consulting</h3>
                    <p>We offer personalized consulting services to help students navigate university applications, CV writing, and job application processes.</p>
                </div>
                <div class="col-md-3 text-center">
                    <h3>🔒 Secure Data Storage</h3>
                    <p>All your documents and personal information are securely stored to ensure privacy and protection during the application process.</p>
                </div>
                <div class="col-md-3 text-center">
                    <h3>👥 Job and University Listings</h3>
                    <p>Stay updated with the latest job vacancies from MIFOTRA and international universities offering scholarships and admissions.</p>
                </div>
                <div class="col-md-3 text-center">
                    <h3>📱 Mobile-Friendly</h3>
                    <p>Access the platform from any device, whether mobile or desktop, with our responsive and user-friendly design.</p>
                </div>
            </div>
        </section>

        <!-- About Us Section -->
        <section class="about-us mt-5">
            <h2>About EduJobs Scholars</h2>
            <p>EduJobs Scholars was created to empower students and job seekers by providing them with valuable resources and up-to-date information on scholarships, job opportunities, and application consulting services. Our goal is to streamline the application process for students and job seekers, helping them easily access opportunities and build successful futures.</p>
        </section>

        <!-- Public Files Section -->
        <section class="public-files-section">
            <div class="container">
                <h2>Public Files</h2>
                <p class="text-center mb-4">Browse publicly available resources. Login to download.</p>
                
                <?php if (empty($publicFiles)): ?>
                    <div class="text-center">
                        <p>No public files available at the moment.</p>
                    </div>
                <?php else: ?>
                    <div class="public-files-grid">
                        <?php foreach ($publicFiles as $file): ?>
                            <div class="file-card">
                                <div class="file-icon">
                                    <?php echo getFileIcon($file['file_type']); ?>
                                </div>
                                <h3><?php echo htmlspecialchars($file['title']); ?></h3>
                                <div class="file-details">
                                    <p><?php echo htmlspecialchars($file['description'] ?? 'No description'); ?></p>
                                    <p>Type: <?php echo htmlspecialchars($file['file_type']); ?></p>
                                    <p>Uploaded: <?php echo date('Y-m-d', strtotime($file['upload_date'])); ?></p>
                                </div>
                                
                                <?php if (isUserLoggedIn()): ?>
                                    <a href="download.php?id=<?php echo $file['id']; ?>" class="file-download">
                                        Download
                                    </a>
                                <?php else: ?>
                                    <div class="login-prompt">
                                        <p>Please <a href="login.php">login</a> to download</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($publicFiles) >= 6): ?>
                        <div class="text-center mt-4">
                            <a href="list_files.php" class="btn btn-primary">View All Files</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Include footer (using relative path for portability) -->
    <?php include('includes/footer.php'); ?>

    <script>
        // Image Slider JavaScript
        let sliderImages = document.querySelector('.slider-images');
        let sliderWidth = document.querySelector('#slider').offsetWidth;
        let currentSlide = 0;
        let totalSlides = sliderImages.children.length;

        function showNextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            sliderImages.style.transform = `translateX(-${currentSlide * sliderWidth}px)`;
        }
        setInterval(showNextSlide, 3000); // Change every 3 seconds

        // Modal Popup JavaScript
        let modal = document.getElementById('myModal');
        let span = document.getElementsByClassName('close')[0];

        // Show the modal after 2 seconds
        setTimeout(function() {
            modal.style.display = 'block';
        }, 2000);

        // Close the modal
        span.onclick = function() {
            modal.style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Smooth Scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>