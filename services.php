<?php
// Include header (using relative path for portability)
include('includes/header.php');
?>

<style>
/* General Styles */
body {
    font-family: Arial, sans-serif;
    background-color: #f4f7fc;
    color: #333;
    margin: 0;
    padding: 0;
}

/* Section Styling */
section {
    padding: 50px 0;
}

/* Service Section */
.services-section {
    text-align: center;
    margin-bottom: 50px;
}

.services-section h2 {
    font-size: 2.5rem;
    color: #0056b3;
    margin-bottom: 30px;
}

.service-card {
    display: inline-block;
    background-size: cover;
    background-position: center;
    padding: 20px;
    margin: 10px;
    width: 250px;
    height: 300px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    overflow: hidden;
}

.service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5); /* Dark overlay for better text visibility */
    z-index: 1;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.service-card h3 {
    font-size: 1.6rem;
    color: white;
    margin-bottom: 15px;
    position: relative;
    z-index: 2;
}

.service-card p {
    font-size: 1rem;
    color: white;
    position: relative;
    z-index: 2;
}

.service-card .btn {
    display: inline-block;
    margin-top: 10px;
    padding: 10px 20px;
    background-color: #0056b3;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
    position: relative;
    z-index: 2;
}

.service-card .btn:hover {
    background-color: #003366;
}

/* Modal for Service Details */
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 30px;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
}

.close {
    font-size: 28px;
    color: #aaa;
    float: right;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}

@keyframes fadeIn {
    0% { opacity: 0; }
    100% { opacity: 1; }
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

<!-- Main Content Section -->
<div class="container">
    <section class="services-section">
        <h2>Our Services</h2>

        <!-- Online Service Integration -->
        <div class="service-card" style="background-image: url('images/online-services.jpg');">
            <h3>Online Service Integration</h3>
            <p>We help clients integrate services like Irembo to streamline their processes for a smoother user experience.</p>
            <a href="#" class="btn" onclick="openModal('online-services')">Learn More</a>
        </div>

        <!-- Application Consultancy -->
        <div class="service-card" style="background-image: url('images/application-consultancy.jpg');">
            <h3>Application Consultancy</h3>
            <p>We offer expert advice for students looking to apply to universities in Rwanda and abroad.</p>
            <a href="#" class="btn" onclick="openModal('application-consultancy')">Learn More</a>
        </div>

        <!-- CV Writing -->
        <div class="service-card" style="background-image: url('images/cv-writing.jpg');">
            <h3>CV Writing</h3>
            <p>We help job seekers create professional CVs that stand out, increasing your chances of securing an interview.</p>
            <a href="#" class="btn" onclick="openModal('cv-writing')">Learn More</a>
        </div>

        <!-- MIFOTRA Registration -->
        <div class="service-card" style="background-image: url('images/mifotra-registration.jpg');">
            <h3>MIFOTRA Registration</h3>
            <p>Our team assists you in creating your MIFOTRA account and guides you through the registration process.</p>
            <a href="#" class="btn" onclick="openModal('mifotra-registration')">Learn More</a>
        </div>

        <!-- Job Application Assistance -->
        <div class="service-card" style="background-image: url('images/job-application.jpg');">
            <h3>Job Application Assistance</h3>
            <p>We support job seekers in preparing and submitting applications for job vacancies from MIFOTRA and other job portals.</p>
            <a href="#" class="btn" onclick="openModal('job-application')">Learn More</a>
        </div>

        <!-- Web Development -->
        <div class="service-card" style="background-image: url('images/web-development.jpg');">
            <h3>Web Development</h3>
            <p>We offer custom web development services tailored to your business or personal needs.</p>
            <a href="#" class="btn" onclick="openModal('web-development')">Learn More</a>
        </div>

        <!-- Graphic Design -->
        <div class="service-card" style="background-image: url('images/graphic-design.jpg');">
            <h3>Graphic Design</h3>
            <p>Our graphic design team creates stunning visuals for your brand, from logos to social media content.</p>
            <a href="#" class="btn" onclick="openModal('graphic-design')">Learn More</a>
        </div>

        <!-- Video Production -->
        <div class="service-card" style="background-image: url('images/video-production.jpg');">
            <h3>Video Production</h3>
            <p>We offer high-quality video production services, including promotional videos, interviews, and educational content.</p>
            <a href="#" class="btn" onclick="openModal('video-production')">Learn More</a>
        </div>
    </section>
</div>

<!-- Modal for Service Details -->
<div id="application-consultancy-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('application-consultancy')">&times;</span>
        <h2>Application Consultancy</h2>
        <p>Our application consultancy service provides expert advice and guidance throughout the university application process. Whether you're applying for a scholarship or need help selecting the right program, we are here to assist you every step of the way.</p>
    </div>
</div>

<div id="cv-writing-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('cv-writing')">&times;</span>
        <h2>CV Writing</h2>
        <p>Our professional CV writing service ensures your CV is tailored to highlight your skills, experience, and qualifications, increasing your chances of landing the job you want.</p>
    </div>
</div>

<div id="mifotra-registration-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('mifotra-registration')">&times;</span>
        <h2>MIFOTRA Registration</h2>
        <p>We assist you in creating and managing your MIFOTRA account to ensure you're registered and ready to apply for job opportunities through the MIFOTRA platform.</p>
    </div>
</div>

<div id="job-application-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('job-application')">&times;</span>
        <h2>Job Application Assistance</h2>
        <p>We guide you through the process of submitting job applications through MIFOTRA and other online platforms, ensuring that your applications stand out to potential employers.</p>
    </div>
</div>

<div id="web-development-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('web-development')">&times;</span>
        <h2>Web Development</h2>
        <p>Our expert web development team creates websites that are fast, responsive, and designed to meet the specific needs of your business or project.</p>
    </div>
</div>

<div id="graphic-design-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('graphic-design')">&times;</span>
        <h2>Graphic Design</h2>
        <p>Our graphic design services range from logo design to full branding packages, ensuring your brand stands out with creative and impactful designs.</p>
    </div>
</div>

<div id="video-production-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('video-production')">&times;</span>
        <h2>Video Production</h2>
        <p>We offer professional video production services to help you create engaging content for your brand, business, or personal use.</p>
    </div>
</div>

<div id="online-services-modal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('online-services')">&times;</span>
        <h2>Online Service Integration</h2>
        <p>Our team helps you integrate online services like Irembo to make your business operations more efficient and user-friendly.</p>
    </div>
</div>

 <!-- Back to Home Button -->
 <div class="back-to-home">
            <a href="index.php">Back to Home</a>
        </div>

<script>
// Open Modal
function openModal(service) {
    var modal = document.getElementById(service + '-modal');
    modal.style.display = 'block';
}

// Close Modal
function closeModal(service) {
    var modal = document.getElementById(service + '-modal');
    modal.style.display = 'none';
}

// Close Modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php
// Include footer (using relative path for portability)
include('includes/footer.php');
?>