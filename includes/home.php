<?php
// Start session to access session variables if one doesn't already exist
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include header file
include 'header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <div class="home-banner">
        <h1>Welcome to EduJobs Scholars</h1>
        <p>Your go-to platform for university application updates, job vacancies, and expert consulting services for students and job seekers.</p>
        <div class="home-buttons">
            <a href="#university-section" class="btn btn-primary">Explore University Applications</a>
            <a href="#jobs-section" class="btn btn-secondary">Find Job Vacancies</a>
            <a href="#consulting-section" class="btn btn-tertiary">Get Application Consulting</a>
        </div>
    </div>

    <!-- Featured Sections with Enhanced Functionality -->
    
    <!-- University Applications Section -->
    <div id="university-section" class="featured-section">
        <h2>University Applications</h2>
        <div class="section-intro">
            <p>Stay updated with the latest application opportunities for universities in Rwanda and abroad. We provide comprehensive information and assistance with application processes.</p>
        </div>
        
        <div class="tab-container">
            <div class="tabs">
                <button class="tab-btn active" data-tab="local-universities">Rwanda Universities</button>
                <button class="tab-btn" data-tab="international-universities">International Universities</button>
                <button class="tab-btn" data-tab="scholarships">Scholarships</button>
            </div>
            
            <div class="tab-content active" id="local-universities">
                <h3>Rwanda Universities Accepting Applications</h3>
                <div class="university-cards">
                    <div class="university-card">
                        <div class="university-logo">🏛️</div>
                        <h4>University of Rwanda</h4>
                        <p class="deadline">Application Deadline: July 30, 2023</p>
                        <p>The University of Rwanda is now accepting applications for the 2023/2024 academic year.</p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                    <div class="university-card">
                        <div class="university-logo">🏛️</div>
                        <h4>Rwanda Polytechnic</h4>
                        <p class="deadline">Application Deadline: August 15, 2023</p>
                        <p>Rwanda Polytechnic has opened applications for various technical programs.</p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                    <div class="university-card">
                        <div class="university-logo">🏛️</div>
                        <h4>Mount Kenya University Rwanda</h4>
                        <p class="deadline">Application Deadline: Ongoing</p>
                        <p>MKU Rwanda offers a variety of undergraduate and postgraduate programs.</p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                </div>
                <div class="view-more-container">
                    <a href="#" class="btn btn-outline">View All Rwanda Universities</a>
                </div>
            </div>
            
            <div class="tab-content" id="international-universities">
                <h3>International Universities Accepting Applications</h3>
                <div class="university-cards">
                    <div class="university-card">
                        <div class="university-logo">🌍</div>
                        <h4>Harvard University</h4>
                        <p class="deadline">Application Deadline: January 1, 2024</p>
                        <p>Harvard University is accepting international student applications for various programs.</p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                    <div class="university-card">
                        <div class="university-logo">🌍</div>
                        <h4>University of Cape Town</h4>
                        <p class="deadline">Application Deadline: September 30, 2023</p>
                        <p>UCT offers a wide range of undergraduate and postgraduate programs for international students.</p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                    <div class="university-card">
                        <div class="university-logo">🌍</div>
                        <h4>University of Toronto</h4>
                        <p class="deadline">Application Deadline: December 15, 2023</p>
                        <p>UofT welcomes international applications for their diverse academic programs.</p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                </div>
                <div class="view-more-container">
                    <a href="#" class="btn btn-outline">View All International Universities</a>
                </div>
            </div>
            
            <div class="tab-content" id="scholarships">
                <h3>Available Scholarships</h3>
                <div class="scholarship-cards">
                    <div class="scholarship-card">
                        <div class="scholarship-icon">🎓</div>
                        <h4>Mastercard Foundation Scholars Program</h4>
                        <p class="deadline">Application Deadline: May 15, 2023</p>
                        <p>Full scholarships for African students to study at partner universities worldwide.</p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                    <div class="scholarship-card">
                        <div class="scholarship-icon">🎓</div>
                        <h4>Chevening Scholarships</h4>
                        <p class="deadline">Application Deadline: November 2, 2023</p>
                        <p>Fully-funded scholarships to study in the UK for individuals with leadership potential.</p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                    <div class="scholarship-card">
                        <div class="scholarship-icon">🎓</div>
                        <h4>DAAD Scholarships</h4>
                        <p class="deadline">Application Deadline: Varies by program</p>
                        <p>German Academic Exchange Service scholarships for study in Germany.</p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                </div>
                <div class="view-more-container">
                    <a href="#" class="btn btn-outline">View All Scholarships</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Job Vacancies Section -->
    <div id="jobs-section" class="featured-section">
        <h2>Job Vacancies</h2>
        <div class="section-intro">
            <p>Get real-time updates on job openings, including those from MIFOTRA and other online services. We compile opportunities from various sources to help you find your next career move.</p>
        </div>
        
        <div class="job-search-container">
            <form class="job-search-form" action="#" method="post">
                <div class="search-row">
                    <div class="search-group">
                        <input type="text" class="form-control" placeholder="Keywords (e.g., Teacher, Engineer)">
                    </div>
                    <div class="search-group">
                        <select class="form-control">
                            <option value="">All Locations</option>
                            <option value="kigali">Kigali</option>
                            <option value="eastern">Eastern Province</option>
                            <option value="western">Western Province</option>
                            <option value="northern">Northern Province</option>
                            <option value="southern">Southern Province</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <select class="form-control">
                            <option value="">All Categories</option>
                            <option value="education">Education</option>
                            <option value="healthcare">Healthcare</option>
                            <option value="it">IT & Technology</option>
                            <option value="admin">Administrative</option>
                            <option value="engineering">Engineering</option>
                        </select>
                    </div>
                    <div class="search-button">
                        <button type="submit" class="btn btn-primary">Search Jobs</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="featured-jobs">
            <h3>Featured Job Opportunities</h3>
            <div class="job-cards">
                <div class="job-card" data-job-id="1">
                    <div class="job-header">
                        <h4>Secondary School Teacher - Mathematics</h4>
                        <span class="job-type">Full-time</span>
                    </div>
                    <div class="job-details">
                        <p class="job-company"><i class="fas fa-building"></i> Ministry of Education</p>
                        <p class="job-location"><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</p>
                        <p class="job-deadline"><i class="fas fa-calendar-alt"></i> Apply by: June 30, 2023</p>
                    </div>
                    <div class="job-description">
                        <p>The Ministry of Education is looking for qualified Mathematics teachers for secondary schools in Kigali.</p>
                    </div>
                    <div class="job-footer">
                        <button class="btn btn-sm btn-primary view-details-btn">View Details</button>
                        <button class="btn btn-sm btn-secondary quick-apply-btn">Quick Apply</button>
                    </div>
                </div>
                
                <div class="job-card" data-job-id="2">
                    <div class="job-header">
                        <h4>Software Developer</h4>
                        <span class="job-type">Full-time</span>
                    </div>
                    <div class="job-details">
                        <p class="job-company"><i class="fas fa-building"></i> Rwanda Information Society Authority</p>
                        <p class="job-location"><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</p>
                        <p class="job-deadline"><i class="fas fa-calendar-alt"></i> Apply by: July 15, 2023</p>
                    </div>
                    <div class="job-description">
                        <p>RISA is seeking skilled Software Developers to join their team and contribute to Rwanda's digital transformation.</p>
                    </div>
                    <div class="job-footer">
                        <button class="btn btn-sm btn-primary view-details-btn">View Details</button>
                        <button class="btn btn-sm btn-secondary quick-apply-btn">Quick Apply</button>
                    </div>
                </div>
                
                <div class="job-card" data-job-id="3">
                    <div class="job-header">
                        <h4>Project Manager</h4>
                        <span class="job-type">Contract</span>
                    </div>
                    <div class="job-details">
                        <p class="job-company"><i class="fas fa-building"></i> UNDP Rwanda</p>
                        <p class="job-location"><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</p>
                        <p class="job-deadline"><i class="fas fa-calendar-alt"></i> Apply by: July 5, 2023</p>
                    </div>
                    <div class="job-description">
                        <p>UNDP is looking for an experienced Project Manager to oversee development projects in Rwanda.</p>
                    </div>
                    <div class="job-footer">
                        <button class="btn btn-sm btn-primary view-details-btn">View Details</button>
                        <button class="btn btn-sm btn-secondary quick-apply-btn">Quick Apply</button>
                    </div>
                </div>
            </div>
            <div class="view-more-container">
                <a href="#" class="btn btn-outline">View All Job Vacancies</a>
            </div>
        </div>
    </div>
    
    <!-- Consulting Services Section -->
    <div id="consulting-section" class="featured-section">
        <h2>Application Consulting Services</h2>
        <div class="section-intro">
            <p>Receive expert consulting for university applications, CV writing, and MIFOTRA account creation. Our team of professionals is ready to help you succeed.</p>
        </div>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">📝</div>
                <h3>CV & Cover Letter Writing</h3>
                <p>Our professional writers will help you create a standout CV and cover letter tailored to your target positions.</p>
                <ul class="service-features">
                    <li>Professional CV formatting</li>
                    <li>Keyword optimization</li>
                    <li>Cover letter customization</li>
                    <li>ATS-friendly documents</li>
                </ul>
                <div class="service-pricing">
                    <p class="price">From 15,000 RWF</p>
                </div>
                <a href="form.php?service=cv_writing" class="btn btn-primary">Book Consultation</a>
            </div>
            
            <div class="service-card">
                <div class="service-icon">🎓</div>
                <h3>University Application Guidance</h3>
                <p>Get expert help with your university applications, from choosing the right program to preparing your documents.</p>
                <ul class="service-features">
                    <li>Program selection advice</li>
                    <li>Application document review</li>
                    <li>Personal statement assistance</li>
                    <li>Application submission support</li>
                </ul>
                <div class="service-pricing">
                    <p class="price">From 25,000 RWF</p>
                </div>
                <a href="form.php?service=university_application" class="btn btn-primary">Book Consultation</a>
            </div>
            
            <div class="service-card">
                <div class="service-icon">💼</div>
                <h3>MIFOTRA Account Setup</h3>
                <p>We'll help you create and optimize your MIFOTRA account to increase your chances of landing a government job.</p>
                <ul class="service-features">
                    <li>Account creation assistance</li>
                    <li>Profile optimization</li>
                    <li>Document upload support</li>
                    <li>Application tracking guidance</li>
                </ul>
                <div class="service-pricing">
                    <p class="price">From 10,000 RWF</p>
                </div>
                <a href="form.php?service=mifotra_setup" class="btn btn-primary">Book Consultation</a>
            </div>
        </div>
        
        <div class="consultation-form-container">
            <h3>Request Quick Consultation</h3>
            <p>Fill out our consultation form to get expert advice and assistance with your applications.</p>
            <div class="text-center">
                <a href="form.php" class="btn btn-primary btn-lg">Go to Consultation Form</a>
            </div>
            
            <?php if (isset($_GET['consultation']) && $_GET['consultation'] == 'success'): ?>
                <div class="alert alert-success mt-3">
                    Thank you for your consultation request! We will contact you shortly.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Services Section -->
    <div class="features-section">
        <h2>Our Services</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🏫</div>
                <h3>University Applications</h3>
                <p>Stay updated with the latest application opportunities for universities in Rwanda and abroad.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💼</div>
                <h3>Job Vacancies</h3>
                <p>Get real-time updates on job openings, including those from MIFOTRA and other online services.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✍️</div>
                <h3>Application Consulting</h3>
                <p>Receive expert consulting for university applications, CV writing, and MIFOTRA account creation.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌐</div>
                <h3>Online Services</h3>
                <p>Access online services like Irembo for application processing and other government services.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💻</div>
                <h3>Web Development</h3>
                <p>We provide web development services to help businesses and individuals establish an online presence.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎨</div>
                <h3>Graphic Design</h3>
                <p>Our graphic design services help you create stunning visuals for your personal or business needs.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎥</div>
                <h3>Video Production</h3>
                <p>We offer professional video production services to help you tell your story effectively.</p>
            </div>
        </div>
    </div>

    <!-- About Us Section -->
    <div class="about-section" id="about">
        <h2>About EduJobs Scholars</h2>
        <p>EduJobs Scholars is a platform designed to provide students and job seekers with up-to-date news and opportunities. We aim to bridge the gap between secondary school graduates and universities in Rwanda and internationally. Our services also cater to job seekers by offering real-time updates on job vacancies, including those listed by MIFOTRA and other platforms.</p>
        
        <p>Our platform also provides valuable services like application consulting, CV writing, account setup for MIFOTRA, and more. Additionally, we offer web development, graphic design, and video production services to help you build your career or business presence online.</p>
        
        <div class="contact-info">
            <h3>Contact Information</h3>
            <p><strong>Email:</strong> info@edujobsscholars.com</p>
            <p><strong>Phone:</strong> +250 788 123 456</p>
            <p><strong>Address:</strong> Kigali, Rwanda</p>
        </div>
    </div>

    <!-- Footer Section -->
    <div class="footer-section">
        <p>&copy; 2025 EduJobs Scholars | All Rights Reserved</p>
    </div>
</div>

<!-- Job Details Modal -->
<div id="jobDetailsModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <div id="jobDetailsContent">
            <!-- Content will be dynamically loaded here -->
        </div>
    </div>
</div>

<!-- Job Application Modal -->
<div id="jobApplicationModal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Job Application Form</h2>
        <?php if (isset($_SESSION['application_success'])): ?>
            <div class="alert alert-success">
                Thank you for your application! We will review it and get back to you soon.
            </div>
            <?php unset($_SESSION['application_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['application_error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['application_error']; ?>
            </div>
            <?php unset($_SESSION['application_error']); ?>
        <?php endif; ?>
        
        <form id="jobApplicationForm" action="process_application.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="jobId" name="job_id">
            <div class="form-group">
                <label for="fullName">Full Name</label>
                <input type="text" id="fullName" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="education">Education</label>
                <textarea id="education" name="education" required></textarea>
            </div>
            <div class="form-group">
                <label for="experience">Work Experience</label>
                <textarea id="experience" name="experience" required></textarea>
            </div>
            <div class="form-group">
                <label for="cv">Upload CV (PDF)</label>
                <input type="file" id="cv" name="cv" accept=".pdf" required>
                <small class="form-text text-muted">Maximum file size: 5MB</small>
            </div>
            <div class="form-group">
                <label for="coverLetter">Cover Letter</label>
                <textarea id="coverLetter" name="cover_letter" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Application</button>
        </form>
    </div>
</div>

<!-- Add this before the closing body tag -->
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        overflow-y: auto;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 90%;
        max-width: 800px;
        border-radius: 8px;
        position: relative;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .close-modal {
        position: absolute;
        right: 20px;
        top: 10px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
        color: #666;
        transition: color 0.3s;
    }

    .close-modal:hover {
        color: #000;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
    }

    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 16px;
        transition: border-color 0.3s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
        border-color: #007bff;
        outline: none;
    }

    .form-group textarea {
        height: 120px;
        resize: vertical;
    }

    .job-info {
        margin: 20px 0;
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 4px;
    }

    .job-info p {
        margin: 8px 0;
    }

    .job-requirements,
    .job-responsibilities,
    .job-benefits {
        margin: 20px 0;
    }

    .job-requirements ul,
    .job-responsibilities ul,
    .job-benefits ul {
        padding-left: 20px;
    }

    .job-requirements li,
    .job-responsibilities li,
    .job-benefits li {
        margin: 8px 0;
        line-height: 1.5;
    }

    .apply-now-btn {
        margin-top: 20px;
        width: 100%;
        padding: 12px;
        font-size: 16px;
    }

    @media (max-width: 768px) {
        .modal-content {
            width: 95%;
            margin: 10% auto;
        }
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .form-text {
        font-size: 0.875em;
        color: #6c757d;
        margin-top: 0.25rem;
    }
</style>

<script>
    // Job details data
    const jobDetails = {
        1: {
            title: "Secondary School Teacher - Mathematics",
            company: "Ministry of Education",
            location: "Kigali, Rwanda",
            type: "Full-time",
            deadline: "June 30, 2023",
            description: "The Ministry of Education is looking for qualified Mathematics teachers for secondary schools in Kigali. The successful candidate will be responsible for teaching mathematics to secondary school students and contributing to the development of the school's mathematics curriculum.",
            requirements: [
                "Bachelor's degree in Mathematics or related field",
                "Teaching certification",
                "Minimum 3 years of teaching experience",
                "Strong communication skills",
                "Ability to work with diverse student populations",
                "Proficiency in English and Kinyarwanda",
                "Experience with modern teaching methods"
            ],
            responsibilities: [
                "Teach Mathematics to secondary school students",
                "Develop lesson plans and assessments",
                "Participate in faculty meetings",
                "Maintain student records",
                "Communicate with parents",
                "Organize and supervise extracurricular activities",
                "Contribute to curriculum development"
            ],
            benefits: [
                "Competitive salary",
                "Health insurance",
                "Professional development opportunities",
                "Pension plan",
                "Paid leave",
                "Housing allowance",
                "Transportation allowance"
            ]
        },
        2: {
            title: "Software Developer",
            company: "Rwanda Information Society Authority",
            location: "Kigali, Rwanda",
            type: "Full-time",
            deadline: "July 15, 2023",
            description: "RISA is seeking skilled Software Developers to join their team and contribute to Rwanda's digital transformation. The successful candidate will work on developing and maintaining various digital solutions for government services.",
            requirements: [
                "Bachelor's degree in Computer Science or related field",
                "Proficiency in modern programming languages (JavaScript, Python, Java)",
                "Experience with web development frameworks (React, Node.js, Django)",
                "Strong problem-solving skills",
                "Team collaboration experience",
                "Knowledge of database systems",
                "Experience with version control (Git)"
            ],
            responsibilities: [
                "Develop and maintain software applications",
                "Write clean, efficient code",
                "Participate in code reviews",
                "Collaborate with team members",
                "Document technical specifications",
                "Debug and fix technical issues",
                "Implement security best practices"
            ],
            benefits: [
                "Competitive salary",
                "Health insurance",
                "Training opportunities",
                "Flexible working hours",
                "Remote work options",
                "Annual bonus",
                "Professional certification support"
            ]
        },
        3: {
            title: "Project Manager",
            company: "UNDP Rwanda",
            location: "Kigali, Rwanda",
            type: "Contract",
            deadline: "July 5, 2023",
            description: "UNDP is looking for an experienced Project Manager to oversee development projects in Rwanda. The successful candidate will manage various development initiatives and ensure their successful implementation.",
            requirements: [
                "Master's degree in Project Management or related field",
                "PMP certification preferred",
                "5+ years of project management experience",
                "Strong leadership skills",
                "Experience with international organizations",
                "Excellent communication skills",
                "Proficiency in French and English"
            ],
            responsibilities: [
                "Plan and execute development projects",
                "Manage project resources",
                "Monitor project progress",
                "Coordinate with stakeholders",
                "Prepare project reports",
                "Risk management",
                "Team leadership and mentoring"
            ],
            benefits: [
                "Competitive salary",
                "International exposure",
                "Professional development",
                "Travel opportunities",
                "Contract renewal possibility",
                "Health and life insurance",
                "Annual leave and sick leave"
            ]
        }
    };

    // Modal functionality
    const jobDetailsModal = document.getElementById('jobDetailsModal');
    const jobApplicationModal = document.getElementById('jobApplicationModal');
    const closeButtons = document.querySelectorAll('.close-modal');
    const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
    const quickApplyButtons = document.querySelectorAll('.quick-apply-btn');

    // View Details functionality
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', () => {
            const jobCard = button.closest('.job-card');
            const jobId = jobCard.dataset.jobId;
            const job = jobDetails[jobId];
            
            const content = `
                <h2>${job.title}</h2>
                <div class="job-info">
                    <p><strong>Company:</strong> ${job.company}</p>
                    <p><strong>Location:</strong> ${job.location}</p>
                    <p><strong>Type:</strong> ${job.type}</p>
                    <p><strong>Deadline:</strong> ${job.deadline}</p>
                </div>
                <div class="job-description">
                    <h3>Description</h3>
                    <p>${job.description}</p>
                </div>
                <div class="job-requirements">
                    <h3>Requirements</h3>
                    <ul>
                        ${job.requirements.map(req => `<li>${req}</li>`).join('')}
                    </ul>
                </div>
                <div class="job-responsibilities">
                    <h3>Responsibilities</h3>
                    <ul>
                        ${job.responsibilities.map(resp => `<li>${resp}</li>`).join('')}
                    </ul>
                </div>
                <div class="job-benefits">
                    <h3>Benefits</h3>
                    <ul>
                        ${job.benefits.map(benefit => `<li>${benefit}</li>`).join('')}
                    </ul>
                </div>
                <button class="btn btn-primary apply-now-btn">Apply Now</button>
            `;
            
            document.getElementById('jobDetailsContent').innerHTML = content;
            jobDetailsModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
        });
    });

    // Quick Apply functionality
    quickApplyButtons.forEach(button => {
        button.addEventListener('click', () => {
            const jobCard = button.closest('.job-card');
            const jobId = jobCard.dataset.jobId;
            const job = jobDetails[jobId];
            document.getElementById('jobId').value = jobId;
            
            // Pre-fill some form fields with job information
            document.getElementById('fullName').value = '';
            document.getElementById('email').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('education').value = '';
            document.getElementById('experience').value = '';
            document.getElementById('coverLetter').value = `I am writing to apply for the ${job.title} position at ${job.company}.`;
            
            jobApplicationModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
        });
    });

    // Close modal functionality
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            jobDetailsModal.style.display = 'none';
            jobApplicationModal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Restore scrolling when modal is closed
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (event.target === jobDetailsModal) {
            jobDetailsModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        if (event.target === jobApplicationModal) {
            jobApplicationModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // Apply Now button in job details modal
    document.addEventListener('click', (event) => {
        if (event.target.classList.contains('apply-now-btn')) {
            jobDetailsModal.style.display = 'none';
            jobApplicationModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    });

    // Form submission handling
    document.getElementById('jobApplicationForm').addEventListener('submit', (event) => {
        // Get the CV file input
        const cvInput = document.getElementById('cv');
        const cvFile = cvInput.files[0];
        
        // Check file size (5MB limit)
        if (cvFile && cvFile.size > 5 * 1024 * 1024) {
            alert('CV file size must be less than 5MB');
            event.preventDefault();
            return;
        }
        
        // Check file type
        if (cvFile && !cvFile.type.includes('pdf')) {
            alert('Only PDF files are allowed');
            event.preventDefault();
            return;
        }
        
        // If validation passes, the form will submit normally
    });
</script>

<!-- Add JavaScript for tab functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Show corresponding content
            const tabId = button.getAttribute('data-tab');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
});
</script>

<?php
// Include footer file
include 'footer.php';
?>
