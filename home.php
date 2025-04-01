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
                <div class="job-card">
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
                        <a href="#" class="btn btn-sm btn-primary">View Details</a>
                        <a href="#" class="btn btn-sm btn-secondary">Quick Apply</a>
                    </div>
                </div>
                
                <div class="job-card">
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
                        <a href="#" class="btn btn-sm btn-primary">View Details</a>
                        <a href="#" class="btn btn-sm btn-secondary">Quick Apply</a>
                    </div>
                </div>
                
                <div class="job-card">
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
                        <a href="#" class="btn btn-sm btn-primary">View Details</a>
                        <a href="#" class="btn btn-sm btn-secondary">Quick Apply</a>
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

<?php
// Include footer file
include 'footer.php';
?> 