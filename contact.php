<?php
// Include header (using relative path for portability)
include('includes/header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - EduJobs Scholars</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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

        /* Contact Section Header */
        .contact-header {
            background: url('images') no-repeat center center/cover;
            padding: 100px 0;
            color: blue;
            text-align: center;
            animation: fadeIn 2s ease-in-out;
        }

        .contact-header h1 {
            font-size: 3rem;
            animation: slideInLeft 1.5s ease-out;
        }

        .contact-header p {
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

        /* Flexbox Container for Contact Form and Contact Info */
        .contact-sections {
            display: flex;
            justify-content: space-between;
            gap: 20px; /* Adds space between the two sections */
            margin-top: 50px;
        }

        /* Contact Form Styles */
        .contact-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 1; /* Takes up equal space */
        }

        .contact-form input,
        .contact-form textarea {
            width: 95%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease-in-out;
        }

        .contact-form input:focus,
        .contact-form textarea:focus {
            border-color: #007bff;
            outline: none;
        }

        .contact-form button {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s ease-in-out;
            width: 50%;
        }

        .contact-form button:hover {
            background-color: #0056b3;
        }

        /* Contact Information Section */
        .contact-info {
            width: 49%;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 1; /* Takes up equal space */
        }

        .contact-info h3 {
            font-size: 1.5rem;
            color: #0056b3;
            margin-bottom: 20px;
        }

        .contact-info ul {
            list-style: none;
            padding: 0;
        }

        .contact-info ul li {
            margin-bottom: 10px;
            font-size: 1rem;
            color: #555;
        }

        .contact-info ul li a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease-in-out;
        }

        .contact-info ul li a:hover {
            color: #0056b3;
            text-decoration: underline;
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
            font-size: 24px;
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .contact-sections {
                flex-direction: column; /* Stack sections vertically on smaller screens */
            }

            .contact-form,
            .contact-info {
                width: 100%; /* Full width on smaller screens */
            }
        }
    </style>
</head>
<body>

    <!-- Main Content Section -->
    <div class="container">
        <!-- Contact Section Header -->
        <div class="contact-header">
            <h1>Contact Us</h1>
            <p>We're here to help! Reach out to us for any inquiries or assistance.</p>
        </div>

        <!-- Contact Form and Contact Information Sections -->
        <div class="contact-sections">
            <!-- Contact Form Section -->
            <section class="contact-form">
                <h2>Send Us a Message</h2>
                <form action="submit_contact_form.php" method="POST">
                    <input type="text" name="name" placeholder="Your Name" required>
                    <input type="email" name="email" placeholder="Your Email" required>
                    <input type="tel" name="phone" placeholder="Your Phone Number" required>
                    <textarea name="message" placeholder="Your Message" required></textarea>
                    <button type="submit">Submit</button>
                </form>
            </section>

            <!-- Contact Information Section -->
            <section class="contact-info">
                <h3>Other Ways to Reach Us</h3>
                <ul>
                    <li><strong>Email:</strong> <a href="mailto:info@edujobsscholars.com">info@edujobsscholars.com</a></li>
                    <li><strong>Phone:</strong> <a href="tel:+250787846344">+250 787846344</a></li>
                    <li><strong>Phone:</strong> <a href="tel:+250734733221">+250 734733221</a></li>
                    <li><strong>Address:</strong> Kigali, Rwanda</li>
                </ul>
            </section>
        </div>

        <!-- Back to Home Button -->
        <div class="back-to-home">
            <a href="index.php">Back to Home</a>
        </div>
    </div>

    <!-- Floating WhatsApp Icon -->
    <a href="https://wa.me/0787846344" target="_blank" class="whatsapp-float">
        <i class="fa fa-whatsapp"></i>
    </a>

    <!-- JavaScript for Interactivity -->
    <script>
        // Smooth Scroll for Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
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

        // Add Hover Effect to Form Button
        const submitButton = document.querySelector('.contact-form button');
        submitButton.addEventListener('mouseover', () => {
            submitButton.style.transform = 'translateY(-2px)';
        });
        submitButton.addEventListener('mouseout', () => {
            submitButton.style.transform = 'translateY(0)';
        });

        // Add Hover Effect to Contact Info Links
        const contactLinks = document.querySelectorAll('.contact-info ul li a');
        contactLinks.forEach(link => {
            link.addEventListener('mouseover', () => {
                link.style.color = '#0056b3';
            });
            link.addEventListener('mouseout', () => {
                link.style.color = '#007bff';
            });
        });
    </script>

    <?php
    // Include footer (using relative path for portability)
    include('includes/footer.php');
    ?>
</body>
</html>