<?php
// Page title
$pageTitle = "Job Vacancies";

// Include header
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Job Vacancies</h1>
        <p>Find your next career opportunity from our extensive job listings.</p>
    </div>
    
    <div class="job-search-section">
        <div class="advanced-search-container">
            <h2>Search Jobs</h2>
            <form class="advanced-search-form" action="#" method="get">
                <div class="search-row">
                    <div class="search-group">
                        <label for="keywords">Keywords</label>
                        <input type="text" id="keywords" name="keywords" class="form-control" placeholder="Job title, skills, or company">
                    </div>
                    <div class="search-group">
                        <label for="location">Location</label>
                        <select id="location" name="location" class="form-control">
                            <option value="">All Locations</option>
                            <option value="kigali">Kigali</option>
                            <option value="eastern">Eastern Province</option>
                            <option value="western">Western Province</option>
                            <option value="northern">Northern Province</option>
                            <option value="southern">Southern Province</option>
                        </select>
                    </div>
                </div>
                
                <div class="search-row">
                    <div class="search-group">
                        <label for="category">Job Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="">All Categories</option>
                            <option value="education">Education</option>
                            <option value="healthcare">Healthcare</option>
                            <option value="it">IT & Technology</option>
                            <option value="admin">Administrative</option>
                            <option value="engineering">Engineering</option>
                            <option value="finance">Finance & Accounting</option>
                            <option value="sales">Sales & Marketing</option>
                            <option value="hospitality">Hospitality & Tourism</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <label for="type">Job Type</label>
                        <select id="type" name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>
                </div>
                
                <div class="search-row">
                    <div class="search-group">
                        <label for="experience">Experience Level</label>
                        <select id="experience" name="experience" class="form-control">
                            <option value="">Any Experience</option>
                            <option value="entry">Entry Level</option>
                            <option value="mid">Mid Level</option>
                            <option value="senior">Senior Level</option>
                            <option value="executive">Executive</option>
                        </select>
                    </div>
                    <div class="search-group">
                        <label for="salary">Salary Range</label>
                        <select id="salary" name="salary" class="form-control">
                            <option value="">Any Salary</option>
                            <option value="0-500000">Below 500,000 RWF</option>
                            <option value="500000-1000000">500,000 - 1,000,000 RWF</option>
                            <option value="1000000-2000000">1,000,000 - 2,000,000 RWF</option>
                            <option value="2000000+">Above 2,000,000 RWF</option>
                        </select>
                    </div>
                </div>
                
                <div class="search-buttons">
                    <button type="submit" class="btn btn-primary">Search Jobs</button>
                    <button type="reset" class="btn btn-secondary">Reset Filters</button>
                </div>
            </form>
        </div>
        
        <div class="jobs-list-section">
            <div class="jobs-header">
                <h2>Available Positions</h2>
                <div class="jobs-filter">
                    <label for="sort-by">Sort by:</label>
                    <select id="sort-by" class="form-control">
                        <option value="recent">Most Recent</option>
                        <option value="relevant">Most Relevant</option>
                        <option value="salary-high">Salary: High to Low</option>
                        <option value="salary-low">Salary: Low to High</option>
                    </select>
                </div>
            </div>
            
            <div class="jobs-list">
                <!-- Job Listing 1 -->
                <div class="job-listing">
                    <div class="job-main">
                        <div class="job-title-section">
                            <h3>Secondary School Teacher - Mathematics</h3>
                            <span class="job-type full-time">Full-time</span>
                        </div>
                        <div class="job-info">
                            <div class="job-company"><i class="fas fa-building"></i> Ministry of Education</div>
                            <div class="job-location"><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</div>
                            <div class="job-salary"><i class="fas fa-money-bill-wave"></i> 800,000 - 1,200,000 RWF/month</div>
                            <div class="job-posted"><i class="fas fa-calendar-alt"></i> Posted: June 5, 2023</div>
                            <div class="job-deadline"><i class="fas fa-hourglass-end"></i> Apply by: June 30, 2023</div>
                        </div>
                        <div class="job-description">
                            <p>The Ministry of Education is looking for qualified Mathematics teachers for secondary schools in Kigali. Candidates must have a Bachelor's degree in Mathematics or related field and at least 2 years of teaching experience.</p>
                        </div>
                        <div class="job-actions">
                            <a href="#" class="btn btn-primary">View Details</a>
                            <a href="#" class="btn btn-secondary">Quick Apply</a>
                            <button class="btn btn-outline save-job"><i class="far fa-bookmark"></i> Save</button>
                        </div>
                    </div>
                </div>
                
                <!-- Job Listing 2 -->
                <div class="job-listing">
                    <div class="job-main">
                        <div class="job-title-section">
                            <h3>Software Developer</h3>
                            <span class="job-type full-time">Full-time</span>
                        </div>
                        <div class="job-info">
                            <div class="job-company"><i class="fas fa-building"></i> Rwanda Information Society Authority</div>
                            <div class="job-location"><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</div>
                            <div class="job-salary"><i class="fas fa-money-bill-wave"></i> 1,500,000 - 2,500,000 RWF/month</div>
                            <div class="job-posted"><i class="fas fa-calendar-alt"></i> Posted: June 10, 2023</div>
                            <div class="job-deadline"><i class="fas fa-hourglass-end"></i> Apply by: July 15, 2023</div>
                        </div>
                        <div class="job-description">
                            <p>RISA is seeking skilled Software Developers to join their team and contribute to Rwanda's digital transformation. Responsibilities include developing and maintaining applications, implementing new features, and troubleshooting issues.</p>
                        </div>
                        <div class="job-actions">
                            <a href="#" class="btn btn-primary">View Details</a>
                            <a href="#" class="btn btn-secondary">Quick Apply</a>
                            <button class="btn btn-outline save-job"><i class="far fa-bookmark"></i> Save</button>
                        </div>
                    </div>
                </div>
                
                <!-- Job Listing 3 -->
                <div class="job-listing">
                    <div class="job-main">
                        <div class="job-title-section">
                            <h3>Project Manager</h3>
                            <span class="job-type contract">Contract</span>
                        </div>
                        <div class="job-info">
                            <div class="job-company"><i class="fas fa-building"></i> UNDP Rwanda</div>
                            <div class="job-location"><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</div>
                            <div class="job-salary"><i class="fas fa-money-bill-wave"></i> 2,500,000 - 3,500,000 RWF/month</div>
                            <div class="job-posted"><i class="fas fa-calendar-alt"></i> Posted: June 1, 2023</div>
                            <div class="job-deadline"><i class="fas fa-hourglass-end"></i> Apply by: July 5, 2023</div>
                        </div>
                        <div class="job-description">
                            <p>UNDP is looking for an experienced Project Manager to oversee development projects in Rwanda. The candidate will be responsible for planning, executing, and closing projects, ensuring all objectives are delivered on time and within budget.</p>
                        </div>
                        <div class="job-actions">
                            <a href="#" class="btn btn-primary">View Details</a>
                            <a href="#" class="btn btn-secondary">Quick Apply</a>
                            <button class="btn btn-outline save-job"><i class="far fa-bookmark"></i> Save</button>
                        </div>
                    </div>
                </div>
                
                <!-- Job Listing 4 -->
                <div class="job-listing">
                    <div class="job-main">
                        <div class="job-title-section">
                            <h3>Clinical Nurse</h3>
                            <span class="job-type full-time">Full-time</span>
                        </div>
                        <div class="job-info">
                            <div class="job-company"><i class="fas fa-building"></i> Rwanda Biomedical Center</div>
                            <div class="job-location"><i class="fas fa-map-marker-alt"></i> Huye, Rwanda</div>
                            <div class="job-salary"><i class="fas fa-money-bill-wave"></i> 700,000 - 1,000,000 RWF/month</div>
                            <div class="job-posted"><i class="fas fa-calendar-alt"></i> Posted: June 8, 2023</div>
                            <div class="job-deadline"><i class="fas fa-hourglass-end"></i> Apply by: July 10, 2023</div>
                        </div>
                        <div class="job-description">
                            <p>RBC is seeking dedicated Clinical Nurses to join healthcare facilities in Huye district. Responsibilities include patient care, administering medications, and assisting doctors with medical procedures.</p>
                        </div>
                        <div class="job-actions">
                            <a href="#" class="btn btn-primary">View Details</a>
                            <a href="#" class="btn btn-secondary">Quick Apply</a>
                            <button class="btn btn-outline save-job"><i class="far fa-bookmark"></i> Save</button>
                        </div>
                    </div>
                </div>
                
                <!-- Job Listing 5 -->
                <div class="job-listing">
                    <div class="job-main">
                        <div class="job-title-section">
                            <h3>Marketing Coordinator</h3>
                            <span class="job-type full-time">Full-time</span>
                        </div>
                        <div class="job-info">
                            <div class="job-company"><i class="fas fa-building"></i> Bank of Kigali</div>
                            <div class="job-location"><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</div>
                            <div class="job-salary"><i class="fas fa-money-bill-wave"></i> 1,200,000 - 1,800,000 RWF/month</div>
                            <div class="job-posted"><i class="fas fa-calendar-alt"></i> Posted: June 12, 2023</div>
                            <div class="job-deadline"><i class="fas fa-hourglass-end"></i> Apply by: July 12, 2023</div>
                        </div>
                        <div class="job-description">
                            <p>Bank of Kigali is looking for a creative and energetic Marketing Coordinator to support the marketing team in planning and implementing marketing strategies, campaigns, and events.</p>
                        </div>
                        <div class="job-actions">
                            <a href="#" class="btn btn-primary">View Details</a>
                            <a href="#" class="btn btn-secondary">Quick Apply</a>
                            <button class="btn btn-outline save-job"><i class="far fa-bookmark"></i> Save</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="pagination">
                <a href="#" class="page-link active">1</a>
                <a href="#" class="page-link">2</a>
                <a href="#" class="page-link">3</a>
                <a href="#" class="page-link">4</a>
                <a href="#" class="page-link">5</a>
                <a href="#" class="page-link next">Next &raquo;</a>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?> 