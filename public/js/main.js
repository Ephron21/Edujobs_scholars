/**
 * EduJobs Scholars - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Tab functionality
    initTabs();
    
    // Smooth scrolling for anchor links
    initSmoothScrolling();
    
    // Job search form
    initJobSearchForm();
    
    // Consultation form
    initConsultationForm();
});

/**
 * Initialize tab functionality
 */
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (!tabButtons.length) return;
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and contents
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked button
            button.classList.add('active');
            
            // Show corresponding content
            const tabId = button.getAttribute('data-tab');
            const content = document.getElementById(tabId);
            if (content) {
                content.classList.add('active');
            }
        });
    });
}

/**
 * Initialize smooth scrolling for anchor links
 */
function initSmoothScrolling() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    
    anchorLinks.forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    const headerOffset = 80;
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                    
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

/**
 * Initialize job search form
 */
function initJobSearchForm() {
    const jobSearchForm = document.querySelector('.job-search-form');
    
    if (jobSearchForm) {
        jobSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const keyword = this.querySelector('input[type="text"]').value;
            const location = this.querySelector('select:nth-of-type(1)').value;
            const category = this.querySelector('select:nth-of-type(2)').value;
            
            // Display message for demo purposes
            alert(`Searching for ${keyword} jobs in ${location || 'all locations'} in category ${category || 'all categories'}`);
            
            // In a real application, you would make an AJAX request to the server
            // and update the results on the page
        });
    }
}

/**
 * Initialize consultation form
 */
function initConsultationForm() {
    const consultationForm = document.querySelector('.consultation-form');
    
    if (consultationForm) {
        consultationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic form validation
            const name = this.querySelector('#name').value.trim();
            const email = this.querySelector('#email').value.trim();
            const phone = this.querySelector('#phone').value.trim();
            const service = this.querySelector('#service').value;
            const message = this.querySelector('#message').value.trim();
            
            if (!name || !email || !phone || !service || !message) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address');
                return;
            }
            
            // Display success message for demo purposes
            alert('Thank you for your consultation request! We will contact you shortly.');
            
            // Reset form
            this.reset();
            
            // In a real application, you would submit the form to the server using AJAX
        });
    }
}

/**
 * Show a loading spinner
 * @param {HTMLElement} element - Element to show spinner in
 */
function showSpinner(element) {
    const spinner = document.createElement('div');
    spinner.className = 'spinner';
    element.appendChild(spinner);
}

/**
 * Hide the loading spinner
 * @param {HTMLElement} element - Element containing spinner
 */
function hideSpinner(element) {
    const spinner = element.querySelector('.spinner');
    if (spinner) {
        spinner.remove();
    }
} 