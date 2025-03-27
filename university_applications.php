<?php
// Page title
$pageTitle = "University Applications";

// Include header
include 'includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>University Applications</h1>
        <p>Explore and apply to universities in Rwanda and around the world.</p>
    </div>
    
    <div class="university-section">
        <h2>All Universities</h2>
        
        <div class="filter-container">
            <form class="filter-form" action="#" method="get">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="location">Location</label>
                        <select id="location" name="location" class="form-control">
                            <option value="">All Locations</option>
                            <option value="rwanda">Rwanda</option>
                            <option value="africa">Africa</option>
                            <option value="europe">Europe</option>
                            <option value="america">America</option>
                            <option value="asia">Asia</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="level">Program Level</label>
                        <select id="level" name="level" class="form-control">
                            <option value="">All Levels</option>
                            <option value="undergraduate">Undergraduate</option>
                            <option value="graduate">Graduate</option>
                            <option value="phd">PhD</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="field">Field of Study</label>
                        <select id="field" name="field" class="form-control">
                            <option value="">All Fields</option>
                            <option value="business">Business</option>
                            <option value="engineering">Engineering</option>
                            <option value="medicine">Medicine</option>
                            <option value="arts">Arts & Humanities</option>
                            <option value="science">Science</option>
                        </select>
                    </div>
                    <div class="filter-button">
                        <button type="submit" class="btn btn-primary">Filter Results</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="university-grid">
            <!-- Rwanda Universities -->
            <div class="university-card">
                <div class="university-logo">🏛️</div>
                <h3>University of Rwanda</h3>
                <p class="deadline">Application Deadline: July 30, 2023</p>
                <p>The University of Rwanda is now accepting applications for the 2023/2024 academic year.</p>
                <div class="university-details">
                    <p><strong>Location:</strong> Kigali, Rwanda</p>
                    <p><strong>Programs:</strong> Undergraduate, Graduate</p>
                    <p><strong>Tuition:</strong> 1,500,000 - 2,500,000 RWF/year</p>
                </div>
                <a href="#" class="btn btn-primary">Apply Now</a>
            </div>
            
            <div class="university-card">
                <div class="university-logo">🏛️</div>
                <h3>Rwanda Polytechnic</h3>
                <p class="deadline">Application Deadline: August 15, 2023</p>
                <p>Rwanda Polytechnic has opened applications for various technical programs.</p>
                <div class="university-details">
                    <p><strong>Location:</strong> Multiple Campuses, Rwanda</p>
                    <p><strong>Programs:</strong> Diploma, Undergraduate</p>
                    <p><strong>Tuition:</strong> 800,000 - 1,500,000 RWF/year</p>
                </div>
                <a href="#" class="btn btn-primary">Apply Now</a>
            </div>
            
            <div class="university-card">
                <div class="university-logo">🏛️</div>
                <h3>Mount Kenya University Rwanda</h3>
                <p class="deadline">Application Deadline: Ongoing</p>
                <p>MKU Rwanda offers a variety of undergraduate and postgraduate programs.</p>
                <div class="university-details">
                    <p><strong>Location:</strong> Kigali, Rwanda</p>
                    <p><strong>Programs:</strong> Undergraduate, Graduate</p>
                    <p><strong>Tuition:</strong> 1,200,000 - 2,000,000 RWF/year</p>
                </div>
                <a href="#" class="btn btn-primary">Apply Now</a>
            </div>
            
            <!-- International Universities -->
            <div class="university-card">
                <div class="university-logo">🌍</div>
                <h3>Harvard University</h3>
                <p class="deadline">Application Deadline: January 1, 2024</p>
                <p>Harvard University is accepting international student applications for various programs.</p>
                <div class="university-details">
                    <p><strong>Location:</strong> Cambridge, MA, USA</p>
                    <p><strong>Programs:</strong> Undergraduate, Graduate, PhD</p>
                    <p><strong>Tuition:</strong> $50,000 - $65,000/year</p>
                </div>
                <a href="#" class="btn btn-primary">Apply Now</a>
            </div>
            
            <div class="university-card">
                <div class="university-logo">🌍</div>
                <h3>University of Cape Town</h3>
                <p class="deadline">Application Deadline: September 30, 2023</p>
                <p>UCT offers a wide range of undergraduate and postgraduate programs for international students.</p>
                <div class="university-details">
                    <p><strong>Location:</strong> Cape Town, South Africa</p>
                    <p><strong>Programs:</strong> Undergraduate, Graduate, PhD</p>
                    <p><strong>Tuition:</strong> $4,000 - $9,000/year</p>
                </div>
                <a href="#" class="btn btn-primary">Apply Now</a>
            </div>
            
            <div class="university-card">
                <div class="university-logo">🌍</div>
                <h3>University of Toronto</h3>
                <p class="deadline">Application Deadline: December 15, 2023</p>
                <p>UofT welcomes international applications for their diverse academic programs.</p>
                <div class="university-details">
                    <p><strong>Location:</strong> Toronto, Canada</p>
                    <p><strong>Programs:</strong> Undergraduate, Graduate, PhD</p>
                    <p><strong>Tuition:</strong> $30,000 - $45,000/year</p>
                </div>
                <a href="#" class="btn btn-primary">Apply Now</a>
            </div>
            
            <!-- Add more university cards as needed -->
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?> 