document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    if (form) {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            const errors = [];
            
            // Personal Information Validation
            const firstname = document.getElementById('firstname');
            const lastname = document.getElementById('lastname');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');
            
            // Check required fields
            if (!firstname.value.trim()) {
                errors.push('First name is required');
                isValid = false;
                highlightField(firstname);
            }
            
            if (!lastname.value.trim()) {
                errors.push('Last name is required');
                isValid = false;
                highlightField(lastname);
            }
            
            // Email validation
            if (!email.value.trim()) {
                errors.push('Email is required');
                isValid = false;
                highlightField(email);
            } else if (!isValidEmail(email.value.trim())) {
                errors.push('Please enter a valid email address');
                isValid = false;
                highlightField(email);
            }
            
            // Phone validation
            if (!phone.value.trim()) {
                errors.push('Phone number is required');
                isValid = false;
                highlightField(phone);
            }
            
            // Gender validation
            const genderSelected = document.querySelector('input[name="gender"]:checked');
            if (!genderSelected) {
                errors.push('Please select a gender');
                isValid = false;
                highlightField(document.querySelector('.radio-group'));
            }
            
            // Parent information validation
            const fatherName = document.getElementById('father_name');
            const fatherPhone = document.getElementById('father_phone');
            const motherName = document.getElementById('mother_name');
            const motherPhone = document.getElementById('mother_phone');
            
            if (!fatherName.value.trim()) {
                errors.push('Father\'s name is required');
                isValid = false;
                highlightField(fatherName);
            }
            
            if (!fatherPhone.value.trim()) {
                errors.push('Father\'s phone is required');
                isValid = false;
                highlightField(fatherPhone);
            }
            
            if (!motherName.value.trim()) {
                errors.push('Mother\'s name is required');
                isValid = false;
                highlightField(motherName);
            }
            
            if (!motherPhone.value.trim()) {
                errors.push('Mother\'s phone is required');
                isValid = false;
                highlightField(motherPhone);
            }
            
            // Place of issue validation
            const locationFields = ['province', 'district', 'sector', 'cell', 'village'];
            locationFields.forEach(function(field) {
                const element = document.getElementById(field);
                if (!element.value.trim()) {
                    errors.push(field.charAt(0).toUpperCase() + field.slice(1) + ' is required');
                    isValid = false;
                    highlightField(element);
                }
            });
            
            // File validation
            const idDocument = document.getElementById('id_document');
            const diploma = document.getElementById('diploma');
            const profileImage = document.getElementById('profile_image');
            
            if (!idDocument.files || idDocument.files.length === 0) {
                errors.push('ID document is required');
                isValid = false;
                highlightField(idDocument);
            } else if (!isValidFileType(idDocument, ['pdf', 'jpg', 'jpeg', 'png'])) {
                errors.push('Invalid ID document file type. Allowed: PDF, JPG, PNG');
                isValid = false;
                highlightField(idDocument);
            }
            
            if (!diploma.files || diploma.files.length === 0) {
                errors.push('Diploma is required');
                isValid = false;
                highlightField(diploma);
            } else if (!isValidFileType(diploma, ['pdf', 'jpg', 'jpeg', 'png'])) {
                errors.push('Invalid diploma file type. Allowed: PDF, JPG, PNG');
                isValid = false;
                highlightField(diploma);
            }
            
            if (!profileImage.files || profileImage.files.length === 0) {
                errors.push('Profile image is required');
                isValid = false;
                highlightField(profileImage);
            } else if (!isValidFileType(profileImage, ['jpg', 'jpeg', 'png'])) {
                errors.push('Invalid profile image file type. Allowed: JPG, PNG');
                isValid = false;
                highlightField(profileImage);
            }
            
            // Display errors if any
            if (!isValid) {
                event.preventDefault();
                displayErrors(errors);
            }
        });
    }
    
    // Helper functions
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function isValidFileType(input, allowedTypes) {
        if (!input.files || input.files.length === 0) return false;
        
        const fileName = input.files[0].name;
        const fileExt = fileName.split('.').pop().toLowerCase();
        
        return allowedTypes.includes(fileExt);
    }
    
    function highlightField(field) {
        field.classList.add('is-invalid');
        
        field.addEventListener('input', function() {
            field.classList.remove('is-invalid');
        });
    }
    
    function displayErrors(errors) {
        // Clear previous errors
        const existingErrorBox = document.getElementById('error-box');
        if (existingErrorBox) {
            existingErrorBox.remove();
        }
        
        // Create error box
        const errorBox = document.createElement('div');
        errorBox.id = 'error-box';
        errorBox.className = 'alert alert-danger';
        
        const heading = document.createElement('h4');
        heading.textContent = 'Please correct the following errors:';
        errorBox.appendChild(heading);
        
        const errorList = document.createElement('ul');
        errors.forEach(function(error) {
            const listItem = document.createElement('li');
            listItem.textContent = error;
            errorList.appendChild(listItem);
        });
        
        errorBox.appendChild(errorList);
        
        // Insert at the top of the form
        const form = document.querySelector('form');
        form.parentNode.insertBefore(errorBox, form);
        
        // Scroll to errors
        errorBox.scrollIntoView({ behavior: 'smooth' });
    }
});