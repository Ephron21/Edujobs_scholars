<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduJobs Scholars</title>
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
                    <h3>EduJobs Scholars</h3>
                    <p>A comprehensive solution for managing user information and registrations with security and ease.</p>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-section links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="home.php">Home</a></li>
                        <li><a href="admin_login.php">CSM</a></li>
                        <li><a href="login.php">Sign In</a></li>
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
                <p>&copy; <?php echo date('Y'); ?> EduJobs Scholars. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Icon -->
    <a href="https://wa.me/0787846344" target="_blank" class="whatsapp-float">
        <i class="fa fa-phone"></i>
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