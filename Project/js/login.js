document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    // Clear error messages when user starts typing
    emailInput.addEventListener('input', function() {
        clearFieldError(this);
    });
    
    passwordInput.addEventListener('input', function() {
        clearFieldError(this);
    });
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        
        // Clear previous errors
        clearAllErrors();
        
        // Validate email
        const email = emailInput.value.trim();
        if (!email) {
            showError(emailInput, "Email is required!");
            isValid = false;
        } else if (!validateEmail(email)) {
            showError(emailInput, "Please enter a valid email address!");
            isValid = false;
        }
        
        // Validate password
        const password = passwordInput.value;
        if (!password) {
            showError(passwordInput, "Password is required!");
            isValid = false;
        } else if (password.length < 6) {
            showError(passwordInput, "Password must be at least 6 characters!");
            isValid = false;
        }
        
        // If all validations pass, submit the form
        if (isValid) {
            this.submit();
        }
    });
    
    // Email validation function
    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Show error for specific field
    function showError(inputElement, message) {
        // Remove existing error if any
        clearFieldError(inputElement);
        
        // Add error class to input
        inputElement.classList.add('error');
        
        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        errorElement.style.color = '#721c24';
        errorElement.style.fontSize = '14px';
        errorElement.style.marginTop = '5px';
        
        // Insert error after input
        inputElement.parentNode.appendChild(errorElement);
        
        // Focus on the error field
        inputElement.focus();
    }
    
    // Clear error for specific field
    function clearFieldError(inputElement) {
        inputElement.classList.remove('error');
        const errorElement = inputElement.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }
    
    // Clear all errors
    function clearAllErrors() {
        document.querySelectorAll('.field-error').forEach(function(error) {
            error.remove();
        });
        document.querySelectorAll('.error').forEach(function(input) {
            input.classList.remove('error');
        });
    }
    
    // Add CSS for error state
    const style = document.createElement('style');
    style.textContent = `
        input.error {
            border-color: #f5576c !important;
            background-color: #f8f9fa;
        }
        
        input.error:focus {
            box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.1) !important;
        }
    `;
    document.head.appendChild(style);
});